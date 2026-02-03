<?php

namespace App\Filament\Pages;

use App\Models\StoreOrder;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\WithPagination;

class StoreOrders extends Page
{
    use WithPagination;
    
    protected static ?string $navigationLabel = 'الطلبات';
    
    protected static ?string $title = 'إدارة الطلبات';
    
    protected static ?int $navigationSort = 3;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected string $view = 'filament.pages.store-orders';
    
    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المتجر';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
    
    public static function getNavigationBadge(): ?string
    {
        $count = StoreOrder::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    
    public string $filterStatus = 'all';
    public string $filterPayment = 'all';
    public string $search = '';
    public ?int $selectedOrderId = null;
    
    public string $trackingNumber = '';
    public string $adminNotes = '';
    
    public function mount(): void
    {
        //
    }
    
    public function getOrdersProperty()
    {
        return StoreOrder::with(['user', 'items'])
            ->when($this->search, fn ($q) => $q->where('order_number', 'like', "%{$this->search}%")
                ->orWhere('customer_name', 'like', "%{$this->search}%")
                ->orWhere('customer_email', 'like', "%{$this->search}%"))
            ->when($this->filterStatus !== 'all', fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPayment !== 'all', fn ($q) => $q->where('payment_status', $this->filterPayment))
            ->orderByDesc('created_at')
            ->paginate(15);
    }
    
    public function getSelectedOrderProperty()
    {
        if (!$this->selectedOrderId) {
            return null;
        }
        return StoreOrder::with(['user', 'items.product'])->find($this->selectedOrderId);
    }
    
    public function getStatsProperty(): array
    {
        return [
            'total' => StoreOrder::count(),
            'pending' => StoreOrder::where('status', 'pending')->count(),
            'processing' => StoreOrder::where('status', 'processing')->count(),
            'shipped' => StoreOrder::where('status', 'shipped')->count(),
            'delivered' => StoreOrder::where('status', 'delivered')->count(),
            'cancelled' => StoreOrder::where('status', 'cancelled')->count(),
            'total_sales' => StoreOrder::where('payment_status', 'paid')->sum('total'),
        ];
    }
    
    public function selectOrder(?int $id): void
    {
        $this->selectedOrderId = $id;
        if ($id) {
            $order = StoreOrder::find($id);
            $this->trackingNumber = $order->tracking_number ?? '';
            $this->adminNotes = $order->admin_notes ?? '';
        }
    }
    
    public function updateStatus(string $status): void
    {
        $order = StoreOrder::find($this->selectedOrderId);
        if ($order) {
            switch ($status) {
                case 'processing':
                    $order->update(['status' => 'processing']);
                    break;
                case 'shipped':
                    $order->markAsShipped($this->trackingNumber);
                    break;
                case 'delivered':
                    $order->markAsDelivered();
                    break;
                case 'cancelled':
                    $order->cancel();
                    break;
            }
            session()->flash('success', 'تم تحديث حالة الطلب بنجاح');
        }
    }
    
    public function markAsPaid(): void
    {
        $order = StoreOrder::find($this->selectedOrderId);
        if ($order) {
            $order->markAsPaid();
            session()->flash('success', 'تم تأكيد الدفع بنجاح');
        }
    }
    
    public function saveNotes(): void
    {
        $order = StoreOrder::find($this->selectedOrderId);
        if ($order) {
            $order->update([
                'admin_notes' => $this->adminNotes,
                'tracking_number' => $this->trackingNumber,
            ]);
            session()->flash('success', 'تم حفظ الملاحظات بنجاح');
        }
    }
}
