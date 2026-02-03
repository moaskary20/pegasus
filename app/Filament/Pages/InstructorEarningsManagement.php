<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\InstructorEarning;
use App\Models\PayoutGlobalSetting;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\WithPagination;

class InstructorEarningsManagement extends Page
{
    use WithPagination;

    protected static ?string $navigationLabel = 'إدارة الأرباح';
    
    protected static ?string $title = 'إدارة أرباح المدرسين';
    
    protected static ?int $navigationSort = 11;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected string $view = 'filament.pages.instructor-earnings-management';
    
    protected static ?string $slug = 'instructor-earnings-management';
    
    // Settings form fields
    public string $defaultCommissionRate = '';
    public string $adminFeeRate = '';
    public string $minimumPayout = '';
    public string $processingDays = '';
    
    public string $activeTab = 'earnings'; // earnings, settings
    public ?int $selectedInstructorId = null;
    public string $searchQuery = '';
    
    // Add/Edit earning form
    public bool $showEarningForm = false;
    public ?int $editingEarningId = null;
    public ?int $formCourseId = null;
    public string $formEarningsType = 'percentage';
    public string $formEarningsValue = '';
    public bool $formIsActive = true;
    
    public static function getNavigationGroup(): ?string
    {
        return 'الإدارة المالية';
    }
    
    public function mount(): void
    {
        $this->loadSettings();
        $this->formEarningsValue = $this->defaultCommissionRate;
    }
    
    public function loadSettings(): void
    {
        $this->defaultCommissionRate = PayoutGlobalSetting::getValue('default_commission_rate', '70');
        $this->adminFeeRate = PayoutGlobalSetting::getValue('admin_fee_rate', '5');
        $this->minimumPayout = PayoutGlobalSetting::getValue('minimum_payout', '100');
        $this->processingDays = PayoutGlobalSetting::getValue('payout_processing_days', '7');
    }
    
    public function saveSettings(): void
    {
        PayoutGlobalSetting::setValue('default_commission_rate', $this->defaultCommissionRate);
        PayoutGlobalSetting::setValue('admin_fee_rate', $this->adminFeeRate);
        PayoutGlobalSetting::setValue('minimum_payout', $this->minimumPayout);
        PayoutGlobalSetting::setValue('payout_processing_days', $this->processingDays);
        
        PayoutGlobalSetting::clearCache();
        
        session()->flash('success', 'تم حفظ الإعدادات بنجاح');
    }
    
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
    
    public function selectInstructor(?int $id): void
    {
        $this->selectedInstructorId = $id;
        $this->resetEarningForm();
    }
    
    public function resetEarningForm(): void
    {
        $this->showEarningForm = false;
        $this->editingEarningId = null;
        $this->formCourseId = null;
        $this->formEarningsType = 'percentage';
        $this->formEarningsValue = $this->defaultCommissionRate;
        $this->formIsActive = true;
    }
    
    public function openAddEarningForm(): void
    {
        $this->resetEarningForm();
        $this->showEarningForm = true;
    }
    
    public function editEarning(int $earningId): void
    {
        $earning = InstructorEarning::find($earningId);
        if ($earning) {
            $this->editingEarningId = $earning->id;
            $this->formCourseId = $earning->course_id;
            $this->formEarningsType = $earning->earnings_type;
            $this->formEarningsValue = (string) $earning->earnings_value;
            $this->formIsActive = $earning->is_active;
            $this->showEarningForm = true;
        }
    }
    
    public function saveEarning(): void
    {
        $this->validate([
            'formCourseId' => 'required|exists:courses,id',
            'formEarningsType' => 'required|in:percentage,fixed',
            'formEarningsValue' => 'required|numeric|min:0',
        ], [
            'formCourseId.required' => 'يجب اختيار الدورة',
            'formEarningsValue.required' => 'يجب إدخال القيمة',
            'formEarningsValue.numeric' => 'يجب أن تكون القيمة رقماً',
        ]);
        
        $data = [
            'user_id' => $this->selectedInstructorId,
            'course_id' => $this->formCourseId,
            'earnings_type' => $this->formEarningsType,
            'earnings_value' => (float) $this->formEarningsValue,
            'is_active' => $this->formIsActive,
        ];
        
        if ($this->editingEarningId) {
            $earning = InstructorEarning::find($this->editingEarningId);
            if ($earning) {
                $earning->update($data);
                session()->flash('earning_success', 'تم تحديث نسبة العمولة بنجاح');
            }
        } else {
            // Check if earning already exists for this course
            $exists = InstructorEarning::where('user_id', $this->selectedInstructorId)
                ->where('course_id', $this->formCourseId)
                ->exists();
            
            if ($exists) {
                session()->flash('earning_error', 'توجد نسبة عمولة لهذه الدورة بالفعل');
                return;
            }
            
            InstructorEarning::create($data);
            session()->flash('earning_success', 'تم إضافة نسبة العمولة بنجاح');
        }
        
        $this->resetEarningForm();
    }
    
    public function deleteEarning(int $earningId): void
    {
        $earning = InstructorEarning::find($earningId);
        if ($earning && $earning->user_id === $this->selectedInstructorId) {
            $earning->delete();
            session()->flash('earning_success', 'تم حذف نسبة العمولة بنجاح');
        }
    }
    
    public function toggleEarningStatus(int $earningId): void
    {
        $earning = InstructorEarning::find($earningId);
        if ($earning) {
            $earning->update(['is_active' => !$earning->is_active]);
        }
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
    
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
    
    public function getInstructorsProperty()
    {
        $query = User::whereHas('roles', fn ($q) => $q->where('name', 'instructor'))
            ->withCount(['instructorEarnings as courses_count' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name');
        
        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->searchQuery}%")
                  ->orWhere('email', 'like', "%{$this->searchQuery}%");
            });
        }
        
        return $query->get();
    }
    
    public function getSelectedInstructorProperty()
    {
        if (!$this->selectedInstructorId) {
            return null;
        }
        
        return User::with(['instructorEarnings.course.enrollments'])
            ->find($this->selectedInstructorId);
    }
    
    public function getInstructorEarningsProperty()
    {
        if (!$this->selectedInstructorId) {
            return collect();
        }
        
        return InstructorEarning::with(['course.enrollments'])
            ->where('user_id', $this->selectedInstructorId)
            ->get();
    }
    
    public function getAvailableCoursesProperty()
    {
        if (!$this->selectedInstructorId) {
            return collect();
        }
        
        $existingCourseIds = InstructorEarning::where('user_id', $this->selectedInstructorId)
            ->pluck('course_id')
            ->toArray();
        
        // Get courses owned by instructor that don't have earnings yet
        return Course::where('user_id', $this->selectedInstructorId)
            ->when(!$this->editingEarningId, fn ($q) => $q->whereNotIn('id', $existingCourseIds))
            ->orderBy('title')
            ->get();
    }
    
    public function getStatsProperty(): array
    {
        $allEarnings = InstructorEarning::with(['user', 'course.enrollments'])
            ->where('is_active', true)
            ->get();
        
        return [
            'total_instructors' => User::whereHas('roles', fn ($q) => $q->where('name', 'instructor'))->count(),
            'active_earnings' => $allEarnings->count(),
            'total_earnings' => $allEarnings->sum(fn ($e) => $e->calculateTotalEarnings()),
            'total_payments' => $allEarnings->sum(fn ($e) => $e->getTotalPayments()),
        ];
    }
    
    public function getSettingsProperty(): array
    {
        return [
            'commission_rate' => PayoutGlobalSetting::getDefaultCommissionRate(),
            'admin_fee' => PayoutGlobalSetting::getAdminFeeRate(),
            'minimum_payout' => PayoutGlobalSetting::getMinimumPayout(),
            'processing_days' => PayoutGlobalSetting::getProcessingDays(),
        ];
    }
}
