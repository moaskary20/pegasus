<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\OrderNotification;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ShoppingCart extends Page implements HasForms
{
    use InteractsWithForms;
    
    public array $cartItems = [];
    public float $subtotal = 0;
    public float $discount = 0;
    public ?string $couponCode = null;
    public ?Coupon $appliedCoupon = null;
    public float $total = 0;
    
    protected static ?string $navigationLabel = 'السلة';
    
    protected static ?string $title = 'سلة التسوق';
    
    protected static ?int $navigationSort = 3;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;
    
    protected string $view = 'filament.pages.shopping-cart';
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('student') ?? false;
    }
    
    public function mount(): void
    {
        $this->loadCart();
    }
    
    protected function loadCart(): void
    {
        $cart = Session::get('cart', []);
        $this->cartItems = Course::whereIn('id', $cart)
            ->with('user')
            ->get()
            ->toArray();
        
        $this->calculateTotals();
    }
    
    protected function calculateTotals(): void
    {
        $this->subtotal = 0;
        
        foreach ($this->cartItems as $item) {
            $price = $item['offer_price'] ?? $item['price'] ?? 0;
            $this->subtotal += $price;
        }
        
        $this->discount = 0;
        if ($this->appliedCoupon) {
            $this->discount = $this->appliedCoupon->calculateDiscount($this->subtotal);
        }
        
        $this->total = max(0, $this->subtotal - $this->discount);
    }
    
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('couponCode')
                    ->label('كود الكوبون')
                    ->placeholder('أدخل كود الكوبون')
                    ->maxLength(50)
                    ->suffixAction(
                        \Filament\Forms\Components\Actions\Action::make('apply')
                            ->label('تطبيق')
                            ->action('applyCoupon')
                    ),
            ])
            ->statePath('data');
    }
    
    public function applyCoupon(): void
    {
        $data = $this->form->getState();
        $code = $data['couponCode'] ?? $this->couponCode;
        
        if (empty($code)) {
            Notification::make()
                ->title('خطأ')
                ->body('يرجى إدخال كود الكوبون')
                ->danger()
                ->send();
            return;
        }
        
        $coupon = Coupon::where('code', strtoupper($code))->first();
        
        if (!$coupon) {
            Notification::make()
                ->title('كود غير صحيح')
                ->body('الكود المدخل غير صحيح')
                ->danger()
                ->send();
            $this->appliedCoupon = null;
            $this->couponCode = null;
            $this->calculateTotals();
            return;
        }
        
        if (!$coupon->isValid()) {
            Notification::make()
                ->title('كود منتهي')
                ->body('الكود المدخل منتهي الصلاحية أو غير نشط')
                ->warning()
                ->send();
            $this->appliedCoupon = null;
            $this->couponCode = null;
            $this->calculateTotals();
            return;
        }
        
        $this->appliedCoupon = $coupon;
        $this->couponCode = $code;
        $this->calculateTotals();
        
        Notification::make()
            ->title('تم التطبيق')
            ->body('تم تطبيق الكوبون بنجاح')
            ->success()
            ->send();
    }
    
    public function removeFromCart(int $courseId): void
    {
        $cart = Session::get('cart', []);
        $cart = array_values(array_filter($cart, fn($id) => $id != $courseId));
        Session::put('cart', $cart);
        
        $this->loadCart();
        
        Notification::make()
            ->title('تم الحذف')
            ->body('تم حذف الدورة من السلة')
            ->success()
            ->send();
    }
    
    public function checkout(): void
    {
        if (empty($this->cartItems)) {
            Notification::make()
                ->title('السلة فارغة')
                ->body('أضف دورات إلى السلة أولاً')
                ->warning()
                ->send();
            return;
        }
        
        try {
            DB::beginTransaction();
            
            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => 'ORD-' . time() . '-' . rand(1000, 9999),
                'subtotal' => $this->subtotal,
                'discount' => $this->discount,
                'total' => $this->total,
                'coupon_code' => $this->couponCode,
                'status' => 'pending',
            ]);
            
            // Create order items
            foreach ($this->cartItems as $item) {
                $price = $item['offer_price'] ?? $item['price'] ?? 0;
                OrderItem::create([
                    'order_id' => $order->id,
                    'course_id' => $item['id'],
                    'price' => $price,
                ]);
            }
            
            // Update coupon usage
            if ($this->appliedCoupon) {
                $this->appliedCoupon->increment('used_count');
            }
            
            // Create enrollments
            foreach ($this->cartItems as $item) {
                \App\Models\Enrollment::firstOrCreate(
                    [
                        'user_id' => auth()->id(),
                        'course_id' => $item['id'],
                    ],
                    [
                        'order_id' => $order->id,
                        'price_paid' => $item['offer_price'] ?? $item['price'] ?? 0,
                        'enrolled_at' => now(),
                    ]
                );
            }
            
            DB::commit();
            
            // Send order notifications
            $this->sendOrderNotifications($order);
            
            // Clear cart
            Session::forget('cart');
            $this->loadCart();
            
            Notification::make()
                ->title('تم الطلب بنجاح')
                ->body('تم إنشاء الطلب رقم: ' . $order->order_number)
                ->success()
                ->send();
            
            // Redirect to orders or courses
            redirect()->route('filament.admin.pages.my-courses');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->title('خطأ')
                ->body('حدث خطأ أثناء إنشاء الطلب: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Send order notifications to customer and instructors
     */
    protected function sendOrderNotifications(Order $order): void
    {
        try {
            $order->load(['user', 'items.course.instructor']);
            
            // Notify the customer
            if ($order->user) {
                $order->user->notify(new OrderNotification($order, 'customer'));
            }
            
            // Notify each instructor whose course was purchased
            $instructors = $order->items
                ->pluck('course.instructor')
                ->filter()
                ->unique('id');
            
            foreach ($instructors as $instructor) {
                $instructor->notify(new OrderNotification($order, 'instructor'));
            }
            
            Log::info('Order notifications sent', [
                'order_id' => $order->id,
                'instructors_count' => $instructors->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order notifications', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);
        }
    }
}
