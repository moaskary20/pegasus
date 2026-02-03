<?php

namespace App\Filament\Pages;

use App\Models\PaymentVoucher;
use App\Models\PayoutRequest;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PayoutManagement extends Page
{
    protected static ?string $navigationLabel = 'إدارة المدفوعات';
    
    protected static ?string $title = 'إدارة طلبات السحب';
    
    protected static ?int $navigationSort = 36;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected string $view = 'filament.pages.payout-management';
    
    public static function getNavigationGroup(): ?string
    {
        return 'الإدارة المالية';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
    
    public string $activeFilter = 'pending';
    
    public ?int $selectedRequestId = null;
    
    public string $rejectionReason = '';
    public string $transactionReference = '';
    public string $voucherNotes = '';
    
    public function getStatsProperty(): array
    {
        return [
            'pending' => PayoutRequest::where('status', 'pending')->count(),
            'approved' => PayoutRequest::where('status', 'approved')->count(),
            'processing' => PayoutRequest::where('status', 'processing')->count(),
            'completed' => PayoutRequest::where('status', 'completed')->count(),
            'total_pending_amount' => PayoutRequest::whereIn('status', ['pending', 'approved', 'processing'])->sum('net_amount'),
            'total_paid' => PayoutRequest::where('status', 'completed')->sum('net_amount'),
        ];
    }
    
    public function getRequestsProperty()
    {
        $query = PayoutRequest::with(['user', 'voucher']);
        
        if ($this->activeFilter !== 'all') {
            $query->where('status', $this->activeFilter);
        }
        
        return $query->orderByDesc('requested_at')->get();
    }
    
    public function getSelectedRequestProperty()
    {
        if (!$this->selectedRequestId) {
            return null;
        }
        
        return PayoutRequest::with(['user', 'voucher', 'transactions.course'])->find($this->selectedRequestId);
    }
    
    public function setFilter(string $filter): void
    {
        $this->activeFilter = $filter;
        $this->selectedRequestId = null;
    }
    
    public function selectRequest(int $id): void
    {
        $this->selectedRequestId = $id;
        $this->rejectionReason = '';
        $this->transactionReference = '';
        $this->voucherNotes = '';
    }
    
    public function approveRequest(): void
    {
        $request = PayoutRequest::find($this->selectedRequestId);
        
        if ($request && $request->status === 'pending') {
            $request->approve(auth()->id());
            $this->dispatch('notify', ['type' => 'success', 'message' => 'تمت الموافقة على الطلب']);
        }
    }
    
    public function rejectRequest(): void
    {
        if (empty($this->rejectionReason)) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'يرجى إدخال سبب الرفض']);
            return;
        }
        
        $request = PayoutRequest::find($this->selectedRequestId);
        
        if ($request && $request->status === 'pending') {
            $request->reject(auth()->id(), $this->rejectionReason);
            $this->rejectionReason = '';
            $this->selectedRequestId = null;
            $this->dispatch('notify', ['type' => 'info', 'message' => 'تم رفض الطلب']);
        }
    }
    
    public function startProcessing(): void
    {
        $request = PayoutRequest::find($this->selectedRequestId);
        
        if ($request && $request->status === 'approved') {
            $request->markAsProcessing(auth()->id());
            $this->dispatch('notify', ['type' => 'info', 'message' => 'بدأت معالجة الطلب']);
        }
    }
    
    public function completeAndIssueVoucher(): void
    {
        $request = PayoutRequest::find($this->selectedRequestId);
        
        if (!$request || !in_array($request->status, ['approved', 'processing'])) {
            return;
        }
        
        // Create payment voucher
        PaymentVoucher::create([
            'voucher_number' => PaymentVoucher::generateVoucherNumber(),
            'payout_request_id' => $request->id,
            'user_id' => $request->user_id,
            'amount' => $request->net_amount,
            'payment_method' => $request->payment_method,
            'transaction_reference' => $this->transactionReference,
            'notes' => $this->voucherNotes,
            'issued_at' => now(),
            'issued_by' => auth()->id(),
        ]);
        
        // Complete the request
        $request->complete();
        
        $this->transactionReference = '';
        $this->voucherNotes = '';
        
        $this->dispatch('notify', ['type' => 'success', 'message' => 'تم إصدار سند الدفع بنجاح']);
    }
    
    public static function getNavigationBadge(): ?string
    {
        $pending = PayoutRequest::where('status', 'pending')->count();
        return $pending > 0 ? (string) $pending : null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
