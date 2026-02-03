<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\EarningTransaction;
use App\Models\Enrollment;
use App\Models\InstructorPayoutSetting;
use App\Models\PayoutGlobalSetting;
use App\Models\PayoutRequest;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class MyPayouts extends Page
{
    protected static ?string $navigationLabel = 'طلبات السحب';
    
    protected static ?string $title = 'أرباحي وطلبات السحب';
    
    protected static ?int $navigationSort = 35;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected string $view = 'filament.pages.my-payouts';
    
    public static function getNavigationGroup(): ?string
    {
        return 'الإدارة المالية';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole(['instructor', 'admin']) ?? false;
    }
    
    public string $activeTab = 'overview';
    
    // Payment settings form
    public string $paymentMethod = 'bank_transfer';
    public string $bankName = '';
    public string $accountNumber = '';
    public string $accountHolder = '';
    public string $iban = '';
    public string $phoneNumber = '';
    public string $paypalEmail = '';
    
    // Payout request
    public float $requestAmount = 0;
    public string $requestNotes = '';
    
    public function mount(): void
    {
        $settings = $this->payoutSettings;
        if ($settings) {
            $this->paymentMethod = $settings->payment_method;
            $details = $settings->payment_details ?? [];
            $this->bankName = $details['bank_name'] ?? '';
            $this->accountNumber = $details['account_number'] ?? '';
            $this->accountHolder = $details['account_holder'] ?? '';
            $this->iban = $details['iban'] ?? '';
            $this->phoneNumber = $details['phone_number'] ?? '';
            $this->paypalEmail = $details['paypal_email'] ?? '';
        }
    }
    
    public function getPayoutSettingsProperty()
    {
        return InstructorPayoutSetting::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'commission_rate' => PayoutGlobalSetting::getDefaultCommissionRate(),
                'admin_fee_rate' => PayoutGlobalSetting::getAdminFeeRate(),
                'minimum_payout' => PayoutGlobalSetting::getMinimumPayout(),
                'payment_method' => 'bank_transfer',
            ]
        );
    }
    
    public function getGlobalSettingsProperty(): array
    {
        return [
            'commission_rate' => PayoutGlobalSetting::getDefaultCommissionRate(),
            'admin_fee' => PayoutGlobalSetting::getAdminFeeRate(),
            'minimum_payout' => PayoutGlobalSetting::getMinimumPayout(),
            'processing_days' => PayoutGlobalSetting::getProcessingDays(),
        ];
    }
    
    public function getEarningsStatsProperty(): array
    {
        $userId = auth()->id();
        
        $transactions = EarningTransaction::where('user_id', $userId);
        
        $totalEarnings = (clone $transactions)->sum('commission_amount');
        $availableBalance = (clone $transactions)->where('status', 'available')->sum('commission_amount');
        $pendingPayout = (clone $transactions)->where('status', 'pending_payout')->sum('commission_amount');
        $paidOut = (clone $transactions)->where('status', 'paid_out')->sum('commission_amount');
        
        // If no transactions, calculate from enrollments
        if ($totalEarnings == 0) {
            $courseIds = Course::where('user_id', $userId)->pluck('id');
            $enrollments = Enrollment::whereIn('course_id', $courseIds)->get();
            $settings = $this->payoutSettings;
            
            foreach ($enrollments as $enrollment) {
                $commission = ($enrollment->price_paid * $settings->commission_rate) / 100;
                $totalEarnings += $commission;
                $availableBalance += $commission;
            }
        }
        
        return [
            'total_earnings' => $totalEarnings,
            'available_balance' => $availableBalance,
            'pending_payout' => $pendingPayout,
            'paid_out' => $paidOut,
            'minimum_payout' => $this->payoutSettings->minimum_payout,
            'commission_rate' => $this->payoutSettings->commission_rate,
        ];
    }
    
    public function getCourseEarningsProperty()
    {
        $userId = auth()->id();
        $courses = Course::where('user_id', $userId)->withCount('enrollments')->get();
        $settings = $this->payoutSettings;
        
        return $courses->map(function ($course) use ($settings) {
            $enrollments = $course->enrollments;
            $totalSales = $enrollments->sum('price_paid');
            $commission = ($totalSales * $settings->commission_rate) / 100;
            
            return [
                'id' => $course->id,
                'title' => $course->title,
                'students' => $enrollments->count(),
                'total_sales' => $totalSales,
                'commission_rate' => $settings->commission_rate,
                'commission_amount' => $commission,
            ];
        });
    }
    
    public function getPayoutRequestsProperty()
    {
        return PayoutRequest::where('user_id', auth()->id())
            ->with('voucher')
            ->orderByDesc('created_at')
            ->get();
    }
    
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
    
    public function savePaymentSettings(): void
    {
        $details = match($this->paymentMethod) {
            'bank_transfer' => [
                'bank_name' => $this->bankName,
                'account_number' => $this->accountNumber,
                'account_holder' => $this->accountHolder,
                'iban' => $this->iban,
            ],
            'vodafone_cash', 'instapay' => [
                'phone_number' => $this->phoneNumber,
            ],
            'paypal' => [
                'paypal_email' => $this->paypalEmail,
            ],
            default => [],
        };
        
        $this->payoutSettings->update([
            'payment_method' => $this->paymentMethod,
            'payment_details' => $details,
        ]);
        
        $this->dispatch('notify', ['type' => 'success', 'message' => 'تم حفظ إعدادات الدفع بنجاح']);
    }
    
    public function requestPayout(): void
    {
        $stats = $this->earningsStats;
        
        if ($stats['available_balance'] < $stats['minimum_payout']) {
            $this->dispatch('notify', [
                'type' => 'error', 
                'message' => "الحد الأدنى للسحب هو {$stats['minimum_payout']} ج.م"
            ]);
            return;
        }
        
        $settings = $this->payoutSettings;
        $amount = $stats['available_balance'];
        $adminFee = ($amount * $settings->admin_fee_rate) / 100;
        $netAmount = $amount - $adminFee;
        
        $request = PayoutRequest::create([
            'request_number' => PayoutRequest::generateRequestNumber(),
            'user_id' => auth()->id(),
            'requested_amount' => $amount,
            'commission_amount' => $amount,
            'admin_fee' => $adminFee,
            'deductions' => 0,
            'net_amount' => $netAmount,
            'status' => 'pending',
            'notes' => $this->requestNotes,
            'payment_method' => $settings->payment_method,
            'payment_details' => $settings->payment_details,
            'requested_at' => now(),
        ]);
        
        // Mark transactions as pending payout
        EarningTransaction::where('user_id', auth()->id())
            ->where('status', 'available')
            ->update([
                'status' => 'pending_payout',
                'payout_request_id' => $request->id,
            ]);
        
        $this->requestNotes = '';
        $this->activeTab = 'requests';
        
        $this->dispatch('notify', ['type' => 'success', 'message' => 'تم إرسال طلب السحب بنجاح']);
    }
}
