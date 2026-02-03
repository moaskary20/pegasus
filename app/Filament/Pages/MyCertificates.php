<?php

namespace App\Filament\Pages;

use App\Models\Certificate;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MyCertificates extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationLabel = 'شهاداتي';
    
    protected static ?string $title = 'شهاداتي';
    
    protected static ?int $navigationSort = 4;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;
    
    protected string $view = 'filament.pages.my-certificates';
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('student') ?? false;
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Certificate::query()
                    ->where('user_id', auth()->id())
                    ->with(['course', 'course.user'])
            )
            ->columns([
                TextColumn::make('course.title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('course.instructor.name')
                    ->label('المدرس')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('course.hours')
                    ->label('عدد الساعات')
                    ->formatStateUsing(fn ($state) => $state . ' ساعة')
                    ->sortable(),
                TextColumn::make('uuid')
                    ->label('رقم الشهادة')
                    ->searchable()
                    ->copyable()
                    ->limit(20),
                TextColumn::make('pdf_path')
                    ->label('ملف PDF')
                    ->formatStateUsing(fn ($state) => $state ? '✓ متوفر' : '✗ قيد الإنشاء')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
                TextColumn::make('issued_at')
                    ->label('تاريخ الإصدار')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Certificate $record) => 
                        \App\Filament\Resources\Certificates\CertificateResource::getUrl('view', ['record' => $record])
                    )
                    ->color('info')
                    ->visible(fn (Certificate $record) => \App\Filament\Resources\Certificates\CertificateResource::canView($record)),
                Action::make('download')
                    ->label('تحميل PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (Certificate $record) => $record->pdf_path ? asset('storage/' . $record->pdf_path) : '#')
                    ->openUrlInNewTab()
                    ->visible(fn (Certificate $record) => !empty($record->pdf_path)),
                Action::make('generate_pdf')
                    ->label('إنشاء PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('إنشاء ملف PDF للشهادة')
                    ->modalDescription('سيتم إنشاء ملف PDF جديد للشهادة. هل تريد المتابعة؟')
                    ->action(function (Certificate $record) {
                        try {
                            $service = app(\App\Services\CertificateService::class);
                            $pdfPath = $service->saveCertificatePdf($record);
                            
                            $record->update(['pdf_path' => $pdfPath]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('تم إنشاء ملف PDF بنجاح')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('فشل إنشاء ملف PDF')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Certificate $record) => empty($record->pdf_path)),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('course_id')
                    ->label('الدورة')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->emptyStateHeading('لا توجد شهادات')
            ->emptyStateDescription('ستظهر شهاداتك عند إكمال الدورات المسجلة');
    }
}
