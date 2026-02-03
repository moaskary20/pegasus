<?php

namespace App\Filament\Resources\Certificates\Pages;

use App\Filament\Resources\Certificates\CertificateResource;
use App\Services\CertificateService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditCertificate extends EditRecord
{
    protected static string $resource = CertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_pdf')
                ->label('إنشاء PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
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
                            ->body('تم تحديث ملف PDF بالنصوص الجديدة')
                            ->success()
                            ->send();
                        
                        // Redirect to view page
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('فشل إنشاء ملف PDF')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            DeleteAction::make(),
        ];
    }
}
