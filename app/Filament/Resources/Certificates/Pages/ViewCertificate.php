<?php

namespace App\Filament\Resources\Certificates\Pages;

use App\Filament\Resources\Certificates\CertificateResource;
use App\Services\CertificateService;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewCertificate extends ViewRecord
{
    protected static string $resource = CertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_pdf')
                ->label('تحميل PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => $this->record->getPdfUrl())
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->pdf_path),
            Action::make('regenerate_pdf')
                ->label('إعادة إنشاء PDF')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('إعادة إنشاء ملف PDF')
                ->modalDescription('سيتم إعادة إنشاء ملف PDF مع النصوص المحدثة. هل تريد المتابعة؟')
                ->action(function () {
                    try {
                        $service = app(CertificateService::class);
                        $pdfPath = $service->saveCertificatePdf($this->record);
                        
                        $this->record->update(['pdf_path' => $pdfPath]);
                        
                        Notification::make()
                            ->title('تم إعادة إنشاء ملف PDF بنجاح')
                            ->body('تم تحديث ملف PDF بالنصوص الجديدة')
                            ->success()
                            ->send();
                        
                        // Refresh the page to show updated PDF
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('فشل إعادة إنشاء ملف PDF')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('generate_pdf')
                ->label('إنشاء PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('إنشاء ملف PDF للشهادة')
                ->modalDescription('سيتم إنشاء ملف PDF جديد للشهادة. هل تريد المتابعة؟')
                ->action(function () {
                    try {
                        $service = app(CertificateService::class);
                        $pdfPath = $service->saveCertificatePdf($this->record);
                        
                        $this->record->update(['pdf_path' => $pdfPath]);
                        
                        Notification::make()
                            ->title('تم إنشاء ملف PDF بنجاح')
                            ->success()
                            ->send();
                        
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('فشل إنشاء ملف PDF')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => !$this->record->pdf_path),
        ];
    }
}
