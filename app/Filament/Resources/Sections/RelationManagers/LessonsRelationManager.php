<?php

namespace App\Filament\Resources\Sections\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LessonsRelationManager extends RelationManager
{
    use \App\Filament\Resources\Courses\Traits\GeneratesQuestionsFromBank;
    
    protected static string $relationship = 'lessons';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('العنوان')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull()
                    ->rows(3),
                Select::make('content_type')
                    ->label('نوع المحتوى')
                    ->options([
                        'text' => 'نص فقط',
                        'video' => 'فيديو',
                        'image' => 'صورة',
                        'mixed' => 'مختلط (نص + فيديو/صورة)',
                        'zoom' => '📹 اضافه درس على زوم',
                    ])
                    ->default('text')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // مسح الحقول غير المستخدمة
                        if ($state === 'text') {
                            $set('video_path', null);
                            $set('youtube_url', null);
                            $set('image_path', null);
                        }
                        // تفعيل Zoom عند اختيار نوع zoom
                        if ($state === 'zoom') {
                            $set('has_zoom_meeting', true);
                        } else {
                            // تعطيل Zoom عند اختيار نوع آخر
                            $set('has_zoom_meeting', false);
                        }
                    }),
                
                // Zoom Meeting Fields - موضوعة هنا مباشرة بعد content_type
                Toggle::make('has_zoom_meeting')
                    ->label('إضافة اجتماع Zoom')
                    ->default(false)
                    ->reactive()
                    ->visible(fn ($get) => $get('content_type') === 'zoom')
                    ->helperText('فعّل هذا الخيار لإنشاء اجتماع Zoom مرتبط بهذا الدرس'),
                
                DateTimePicker::make('zoom_scheduled_time')
                    ->label('موعد الاجتماع')
                    ->visible(fn ($get) => $get('content_type') === 'zoom' && $get('has_zoom_meeting'))
                    ->required(fn ($get) => $get('content_type') === 'zoom')
                    ->helperText('حدد التاريخ والوقت لاجتماع Zoom'),
                
                TextInput::make('zoom_duration')
                    ->label('مدة الاجتماع (بالدقائق)')
                    ->numeric()
                    ->default(60)
                    ->minValue(15)
                    ->maxValue(480)
                    ->step(15)
                    ->visible(fn ($get) => $get('content_type') === 'zoom' && $get('has_zoom_meeting'))
                    ->helperText('مدة الاجتماع بالدقائق (الحد الأدنى 15، الحد الأقصى 480)'),
                
                TextInput::make('zoom_password')
                    ->label('كلمة مرور الاجتماع (اختياري)')
                    ->placeholder('سيتم توليد كلمة مرور تلقائياً إذا لم تحدد واحدة')
                    ->visible(fn ($get) => $get('content_type') === 'zoom' && $get('has_zoom_meeting'))
                    ->helperText('إذا تركت هذا الحقل فارغاً، سيتم توليد كلمة مرور عشوائية'),
                
                TextInput::make('zoom_link')
                    ->label('رابط اجتماع Zoom (يملأ تلقائياً)')
                    ->placeholder('https://zoom.us/j/...')
                    ->url()
                    ->disabled()
                    ->visible(fn ($get) => $get('content_type') === 'zoom' && $get('has_zoom_meeting'))
                    ->helperText('سيملأ تلقائياً بعد الحفظ عند إنشاء الاجتماع في Zoom'),
                
                TextInput::make('youtube_url')
                    ->label('رابط فيديو يوتيوب')
                    ->placeholder('https://www.youtube.com/watch?v=... أو https://youtu.be/...')
                    ->url()
                    ->maxLength(500)
                    ->visible(fn ($get) => in_array($get('content_type'), ['video', 'mixed']))
                    ->helperText('أو ارفع ملف فيديو أدناه. إذا أدخلت رابط يوتيوب فلن يُستخدم الملف المرفوع.'),
                FileUpload::make('video_path')
                    ->label('فيديو الدرس (ملف)')
                    ->disk('public')
                    ->directory('lessons/videos')
                    ->acceptedFileTypes(['video/mp4', 'video/quicktime', 'video/webm'])
                    ->maxSize(102400) // 100MB
                    ->visible(fn ($get) => in_array($get('content_type'), ['video', 'mixed']))
                    ->helperText('يمكن رفع ملفات فيديو بصيغة MP4, MOV, WebM'),
                FileUpload::make('image_path')
                    ->label('صورة الدرس')
                    ->disk('public')
                    ->directory('lessons/images')
                    ->image()
                    ->maxSize(10240) // 10MB
                    ->visible(fn ($get) => in_array($get('content_type'), ['image', 'mixed']))
                    ->helperText('يمكن رفع صور بصيغة JPG, PNG, GIF'),
                RichEditor::make('content')
                    ->label('محتوى الدرس النصي')
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'link',
                        'bulletList',
                        'orderedList',
                        'blockquote',
                        'codeBlock',
                    ])
                    ->visible(fn ($get) => in_array($get('content_type'), ['text', 'mixed']))
                    ->helperText('اكتب محتوى الدرس هنا. يمكنك استخدام التنسيق الغني'),
                TextInput::make('duration_minutes')
                    ->label('المدة (بالدقائق)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Toggle::make('is_free_preview')
                    ->label('معاينة مجانية')
                    ->default(false),
                Toggle::make('is_free')
                    ->label('درس مجاني')
                    ->default(false)
                    ->helperText('إذا كان مفعلاً، يمكن للطلاب الوصول لهذا الدرس مجاناً حتى بدون شراء الدورة'),
                Toggle::make('can_unlock_without_completion')
                    ->label('السماح بفتح الدرس بدون إكمال السابق')
                    ->default(false)
                    ->helperText('إذا كان مفعلاً، يمكن للطلاب فتح هذا الدرس حتى بدون إكمال الدرس السابق'),
                Toggle::make('use_as_course_preview')
                    ->label('استخدم كمعاينة للدورة')
                    ->default(fn ($record) => $record && $record->section?->course?->preview_lesson_id === $record->id)
                    ->helperText('فعّل لاستخدام فيديو هذا الدرس كمعاينة للدورة على الموقع')
                    ->dehydrated(false),
                
                // Zoom Meeting Fields
                Toggle::make('has_zoom_meeting')
                    ->label('إضافة اجتماع Zoom')
                    ->default(false)
                    ->reactive()
                    ->helperText('فعّل هذا الخيار لإنشاء اجتماع Zoom مرتبط بهذا الدرس'),
                
                DateTimePicker::make('zoom_scheduled_time')
                    ->label('موعد الاجتماع')
                    ->visible(fn ($get) => $get('has_zoom_meeting'))
                    ->required(fn ($get) => $get('has_zoom_meeting'))
                    ->helperText('حدد التاريخ والوقت لاجتماع Zoom'),
                
                TextInput::make('zoom_duration')
                    ->label('مدة الاجتماع (بالدقائق)')
                    ->numeric()
                    ->default(60)
                    ->minValue(15)
                    ->maxValue(480)
                    ->step(15)
                    ->visible(fn ($get) => $get('has_zoom_meeting'))
                    ->helperText('مدة الاجتماع بالدقائق (الحد الأدنى 15، الحد الأقصى 480)'),
                
                TextInput::make('zoom_password')
                    ->label('كلمة مرور الاجتماع (اختياري)')
                    ->placeholder('سيتم توليد كلمة مرور تلقائياً إذا لم تحدد واحدة')
                    ->visible(fn ($get) => $get('has_zoom_meeting'))
                    ->helperText('إذا تركت هذا الحقل فارغاً، سيتم توليد كلمة مرور عشوائية'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('duration_minutes')
                    ->label('المدة')
                    ->formatStateUsing(fn ($state) => $state . ' دقيقة')
                    ->sortable(),
                TextColumn::make('has_quiz')
                    ->label('اختبار')
                    ->getStateUsing(fn ($record) => $record->quiz ? '✓' : '✗')
                    ->badge()
                    ->color(fn ($record) => $record->quiz ? 'success' : 'gray'),
                TextColumn::make('has_video')
                    ->label('فيديو')
                    ->getStateUsing(fn ($record) => ($record->video || $record->youtube_url || $record->video_path) ? '✓' : '✗')
                    ->badge()
                    ->color(fn ($record) => ($record->video || $record->youtube_url || $record->video_path) ? 'success' : 'gray'),
                TextColumn::make('has_files')
                    ->label('ملفات')
                    ->getStateUsing(fn ($record) => $record->files->count() > 0 ? $record->files->count() : '✗')
                    ->badge()
                    ->color(fn ($record) => $record->files->count() > 0 ? 'warning' : 'gray'),
                IconColumn::make('is_free_preview')
                    ->label('معاينة مجانية')
                    ->boolean(),
                IconColumn::make('is_free')
                    ->label('درس مجاني')
                    ->boolean()
                    ->color(fn ($record) => $record->is_free ? 'success' : 'gray'),
                IconColumn::make('can_unlock_without_completion')
                    ->label('بدون شرط السابق')
                    ->boolean()
                    ->color(fn ($record) => $record->can_unlock_without_completion ? 'warning' : 'gray'),
                TextColumn::make('zoom_meeting')
                    ->label('اجتماع Zoom')
                    ->getStateUsing(fn ($record) => $record->zoomMeeting ? '📹 ' . $record->zoomMeeting->status : 'لا يوجد')
                    ->badge()
                    ->color(fn ($record) => $record->zoomMeeting ? match($record->zoomMeeting->status) {
                        'scheduled' => 'info',
                        'started' => 'success',
                        'ended' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray'
                    } : 'gray'),
                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->after(function ($record) {
                        $data = $this->form->getState();
                        if (!empty($data['use_as_course_preview']) && $record && $record->section?->course_id) {
                            \App\Models\Course::where('id', $record->section->course_id)->update(['preview_lesson_id' => $record->id]);
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function ($record) {
                        $data = $this->form->getState();
                        if (!empty($data['use_as_course_preview']) && $record && $record->section?->course_id) {
                            \App\Models\Course::where('id', $record->section->course_id)->update(['preview_lesson_id' => $record->id]);
                        } elseif (empty($data['use_as_course_preview']) && $record && $record->section?->course?->preview_lesson_id == $record->id) {
                            \App\Models\Course::where('id', $record->section->course_id)->update(['preview_lesson_id' => null]);
                        }
                    }),
                Action::make('manage_quiz')
                    ->label('الاختبار')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('info')
                    ->form(function ($record) {
                        $quiz = $record->quiz;
                        $courseId = $record->section->course_id;
                        
                        return [
                            \Filament\Forms\Components\TextInput::make('title')
                                ->label('عنوان الاختبار')
                                ->required()
                                ->default($quiz?->title),
                            \Filament\Forms\Components\Textarea::make('description')
                                ->label('الوصف')
                                ->rows(3)
                                ->default($quiz?->description),
                            \Filament\Forms\Components\TextInput::make('duration_minutes')
                                ->label('المدة (بالدقائق)')
                                ->numeric()
                                ->default($quiz?->duration_minutes),
                            \Filament\Forms\Components\TextInput::make('pass_percentage')
                                ->label('نسبة النجاح')
                                ->numeric()
                                ->default($quiz?->pass_percentage ?? 60)
                                ->suffix('%')
                                ->required(),
                            \Filament\Forms\Components\Toggle::make('allow_retake')
                                ->label('السماح بإعادة المحاولة')
                                ->default($quiz?->allow_retake ?? true),
                            \Filament\Forms\Components\TextInput::make('max_attempts')
                                ->label('الحد الأقصى للمحاولات')
                                ->numeric()
                                ->default($quiz?->max_attempts),
                            \Filament\Forms\Components\Toggle::make('use_question_bank')
                                ->label('استخدام بنك أسئلة')
                                ->default($quiz?->question_bank_id ? true : false)
                                ->reactive()
                                ->helperText('تفعيل استخدام بنك أسئلة لاختيار أسئلة عشوائية'),
                            \Filament\Forms\Components\Select::make('question_bank_id')
                                ->label('بنك الأسئلة')
                                ->options(function () use ($courseId) {
                                    $banks = \App\Models\QuestionBank::where(function($q) use ($courseId) {
                                        $q->whereNull('course_id')
                                          ->orWhere('course_id', $courseId);
                                    })
                                    ->where('is_active', true)
                                    ->with('questions')
                                    ->get();
                                    
                                    return $banks->mapWithKeys(fn ($bank) => [
                                        $bank->id => $bank->title . ' (' . $bank->questions->count() . ' سؤال' . ($bank->course_id ? ' - خاص بالدورة' : ' - عام') . ')'
                                    ])->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->visible(fn ($get) => $get('use_question_bank') === true)
                                ->required(fn ($get) => $get('use_question_bank') === true)
                                ->helperText('اختر بنك الأسئلة المراد استخدامه'),
                            \Filament\Forms\Components\TextInput::make('questions_count')
                                ->label('عدد الأسئلة المطلوبة')
                                ->numeric()
                                ->minValue(1)
                                ->default($quiz?->questions_count ?? 10)
                                ->required(fn ($get) => $get('use_question_bank') === true)
                                ->visible(fn ($get) => $get('use_question_bank') === true)
                                ->helperText('عدد الأسئلة التي سيتم اختيارها عشوائياً من البنك'),
                            \Filament\Forms\Components\Toggle::make('randomize_questions')
                                ->label('اختيار عشوائي للأسئلة')
                                ->default($quiz?->randomize_questions ?? true)
                                ->visible(fn ($get) => $get('use_question_bank') === true)
                                ->helperText('إذا كان مفعلاً، سيتم اختيار الأسئلة بشكل عشوائي من البنك'),
                        ];
                    })
                    ->fillForm(function ($record) {
                        $quiz = $record->quiz;
                        $data = $quiz ? $quiz->toArray() : [];
                        $data['use_question_bank'] = !empty($quiz?->question_bank_id);
                        return $data;
                    })
                    ->action(function ($record, array $data) {
                        $useQuestionBank = $data['use_question_bank'] ?? false;
                        unset($data['use_question_bank']);
                        
                        if ($record->quiz) {
                            $record->quiz->update($data);
                            
                            // If question bank changed, regenerate questions
                            if ($useQuestionBank && isset($data['question_bank_id']) && 
                                ($data['question_bank_id'] !== $record->quiz->question_bank_id ||
                                 $data['questions_count'] !== $record->quiz->questions_count ||
                                 $data['randomize_questions'] !== $record->quiz->randomize_questions)) {
                                // Delete existing questions
                                $record->quiz->questions()->delete();
                                
                                // Generate new questions from bank
                                $this->generateQuestionsFromBank($record->quiz, $data);
                            } elseif (!$useQuestionBank && $record->quiz->question_bank_id) {
                                // If disabled question bank, clear it
                                $record->quiz->update([
                                    'question_bank_id' => null,
                                    'questions_count' => null,
                                    'randomize_questions' => true,
                                ]);
                            }
                        } else {
                            $quiz = $record->quiz()->create($data);
                            
                            // If using question bank, generate questions
                            if ($useQuestionBank && isset($data['question_bank_id'])) {
                                $this->generateQuestionsFromBank($quiz, $data);
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('تم إنشاء الاختبار')
                                    ->body('يمكنك الآن إضافة الأسئلة')
                                    ->success()
                                    ->send();
                            }
                        }
                    })
                    ->modalHeading('إدارة الاختبار')
                    ->modalSubmitActionLabel('حفظ')
                    ->modalWidth('2xl'),
                Action::make('manage_questions')
                    ->label('أسئلة الاختبار')
                    ->icon('heroicon-o-question-mark-circle')
                    ->color('info')
                    ->url(fn ($record) => 
                        $record->quiz 
                            ? \App\Filament\Resources\Quizzes\QuizResource::getUrl('edit', ['record' => $record->quiz->id])
                            : null
                    )
                    ->visible(fn ($record) => $record->quiz !== null),
                Action::make('manage_video')
                    ->label('الفيديو')
                    ->icon('heroicon-o-video-camera')
                    ->color('success')
                    ->form(function ($record) {
                        $video = $record->video;
                        return [
                            \Filament\Forms\Components\FileUpload::make('path')
                                ->label('ملف الفيديو')
                                ->disk('public')
                                ->directory('videos')
                                ->acceptedFileTypes(['video/mp4', 'video/quicktime'])
                                ->default($video?->path),
                            \Filament\Forms\Components\Select::make('disk')
                                ->label('التخزين')
                                ->options([
                                    'local' => 'محلي',
                                    's3' => 'S3',
                                ])
                                ->default($video?->disk ?? 'local'),
                            \Filament\Forms\Components\Select::make('status')
                                ->label('الحالة')
                                ->options([
                                    'pending' => 'قيد الانتظار',
                                    'processing' => 'قيد المعالجة',
                                    'ready' => 'جاهز',
                                    'failed' => 'فشل',
                                ])
                                ->default($video?->status ?? 'pending'),
                        ];
                    })
                    ->fillForm(function ($record) {
                        return $record->video ? $record->video->toArray() : [];
                    })
                    ->action(function ($record, array $data) {
                        if ($record->video) {
                            $record->video->update($data);
                        } else {
                            $record->video()->create($data);
                        }
                    })
                    ->modalHeading('إدارة الفيديو')
                    ->modalSubmitActionLabel('حفظ'),
                Action::make('manage_files')
                    ->label('الملفات')
                    ->icon('heroicon-o-document')
                    ->color('warning')
                    ->url(fn ($record) => 
                        \App\Filament\Resources\Courses\CourseResource::getUrl('edit', [
                            'record' => $record->section->course_id,
                        ]) . '?activeRelationManager=0&activeRelationManagerTab=1&activeRelationManagerRecord=' . $record->id . '&activeRelationManagerTab=files'
                    )
                    ->openUrlInNewTab(false),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
