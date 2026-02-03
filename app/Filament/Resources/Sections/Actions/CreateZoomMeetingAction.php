<?php

namespace App\Filament\Resources\Sections\Actions;

use App\Models\Lesson;
use App\Services\ZoomAPIService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Exception;

class CreateZoomMeetingAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'create_zoom_meeting';
    }

    public function setUp(): void
    {
        parent::setUp();

        $this
            ->label('🎥 إنشاء اجتماع Zoom')
            ->icon('heroicon-o-video-camera')
            ->color('info')
            ->form([
                DateTimePicker::make('scheduled_start_time')
                    ->label('موعد الاجتماع')
                    ->required()
                    ->helperText('حدد التاريخ والوقت لبدء الاجتماع'),
                
                TextInput::make('duration')
                    ->label('مدة الاجتماع (بالدقائق)')
                    ->numeric()
                    ->default(60)
                    ->minValue(15)
                    ->maxValue(480)
                    ->step(15)
                    ->required()
                    ->helperText('مدة الاجتماع بالدقائق'),
                
                TextInput::make('password')
                    ->label('كلمة المرور (اختياري)')
                    ->placeholder('سيتم توليد كلمة مرور تلقائياً')
                    ->helperText('ترك هذا الحقل فارغاً سيولد كلمة مرور عشوائية'),
            ])
            ->action(function (Lesson $record, array $data): void {
                try {
                    $zoomService = new ZoomAPIService();
                    
                    if (!$zoomService->isConfigured()) {
                        Notification::make()
                            ->title('خطأ')
                            ->body('إعدادات Zoom غير مكتملة. يرجى تكوين بيانات API في إعدادات المنصة.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $zoomMeeting = $zoomService->createMeeting(
                        $record,
                        $data['scheduled_start_time'],
                        $data['duration']
                    );

                    if ($zoomMeeting) {
                        Notification::make()
                            ->title('نجح')
                            ->body('تم إنشاء اجتماع Zoom بنجاح! 🎉')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('خطأ')
                            ->body('فشل إنشاء الاجتماع. يرجى التحقق من بيانات الاعتماد.')
                            ->danger()
                            ->send();
                    }
                } catch (Exception $e) {
                    Notification::make()
                        ->title('خطأ')
                        ->body('حدث خطأ: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
