<?php

namespace App\Filament\Pages;

use App\Filament\Resources\InstructorEarnings\InstructorEarningResource;
use App\Filament\Widgets\EarningsStatsWidget;
use App\Models\InstructorEarning;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class InstructorEarnings extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'تقرير الأرباح';
    
    protected static ?string $title = 'تقرير الأرباح';
    
    protected static ?int $navigationSort = 12;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected string $view = 'filament.pages.instructor-earnings';
    
    protected static ?string $slug = 'earnings-report';
    
    public ?int $selectedInstructor = null;
    
    public function mount(): void
    {
        // إذا كان المستخدم مدرس وليس أدمن، عرض بياناته فقط
        if (auth()->user()?->hasRole('instructor') && !auth()->user()?->hasRole('admin')) {
            $this->selectedInstructor = auth()->id();
        }
    }
    
    public static function getNavigationGroup(): ?string
    {
        return 'الإدارة المالية';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        if (!auth()->check()) {
            return false;
        }
        
        $user = auth()->user();
        return $user?->hasAnyRole(['admin', 'instructor']) ?? false;
    }
    
    public static function canAccess(): bool
    {
        if (!auth()->check()) {
            return false;
        }
        
        $user = auth()->user();
        return $user?->hasAnyRole(['admin', 'instructor']) ?? false;
    }
    
    public function getHeaderWidgets(): array
    {
        return [
            EarningsStatsWidget::class,
        ];
    }
    
    public function getHeaderWidgetsColumns(): int
    {
        return 5;
    }
    
    public function getInstructors()
    {
        return \App\Models\User::whereHas('roles', fn ($q) => 
            $q->where('name', 'instructor')
        )->orderBy('name')->get();
    }
    
    public function getEarningsData()
    {
        $query = InstructorEarning::with(['user', 'course.enrollments'])
            ->where('is_active', true);
        
        // إذا كان أدمن واختار مدرس معين
        if (auth()->user()?->hasRole('admin') && $this->selectedInstructor) {
            $query->where('user_id', $this->selectedInstructor);
        }
        // إذا كان مدرس فقط، عرض بياناته فقط
        elseif (auth()->user()?->hasRole('instructor') && !auth()->user()?->hasRole('admin')) {
            $query->where('user_id', auth()->id());
        }
        
        return $query->get();
    }
    
    public function getTotalEarnings()
    {
        return $this->getEarningsData()->sum(fn ($earning) => $earning->calculateTotalEarnings());
    }
    
    public function getTotalPayments()
    {
        return $this->getEarningsData()->sum(fn ($earning) => $earning->getTotalPayments());
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                InstructorEarning::query()
                    ->with(['user', 'course.enrollments'])
                    ->where('is_active', true)
                    ->when(
                        auth()->user()?->hasRole('admin') && $this->selectedInstructor,
                        fn ($query) => $query->where('user_id', $this->selectedInstructor)
                    )
                    ->when(
                        auth()->user()?->hasRole('instructor') && !auth()->user()?->hasRole('admin'),
                        fn ($query) => $query->where('user_id', auth()->id())
                    )
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('المدرس')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->hasRole('admin')),
                
                TextColumn::make('course.title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->course->title),
                
                TextColumn::make('earnings_type')
                    ->label('نوع الأرباح')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'info',
                        'fixed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percentage' => 'نسبة مئوية',
                        'fixed' => 'مبلغ ثابت',
                        default => $state,
                    })
                    ->sortable(),
                
                TextColumn::make('earnings_value')
                    ->label('القيمة')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->earnings_type === 'percentage') {
                            return number_format($state, 2) . '%';
                        }
                        return number_format($state, 2) . ' ج.م';
                    })
                    ->sortable(),
                
                TextColumn::make('students_count')
                    ->label('عدد الطلاب')
                    ->state(fn ($record) => $record->getStudentsCount())
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                
                TextColumn::make('total_payments')
                    ->label('إجمالي المدفوعات')
                    ->state(fn ($record) => $record->getTotalPayments())
                    ->money('EGP', locale: 'ar')
                    ->sortable(),
                
                TextColumn::make('total_earnings')
                    ->label('إجمالي الأرباح')
                    ->state(fn ($record) => $record->calculateTotalEarnings())
                    ->money('EGP', locale: 'ar')
                    ->color('success')
                    ->weight('bold')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('المدرس')
                    ->relationship('user', 'name')
                    ->visible(fn () => auth()->user()?->hasRole('admin'))
                    ->preload()
                    ->searchable(),
                
                SelectFilter::make('earnings_type')
                    ->label('نوع الأرباح')
                    ->options([
                        'percentage' => 'نسبة مئوية',
                        'fixed' => 'مبلغ ثابت',
                    ]),
            ])
            ->actions([
                Action::make('edit')
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => InstructorEarningResource::getUrl('edit', ['record' => $record]))
                    ->visible(fn () => auth()->user()?->hasRole('admin')),
            ])
            ->defaultSort('total_earnings', 'desc')
            ->emptyStateHeading('لا توجد بيانات أرباح')
            ->emptyStateDescription('لم يتم العثور على أي بيانات أرباح')
            ->emptyStateIcon('heroicon-o-currency-dollar');
    }
}
