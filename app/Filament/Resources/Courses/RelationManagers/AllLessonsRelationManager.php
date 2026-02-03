<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AllLessonsRelationManager extends RelationManager
{
    use \App\Filament\Resources\Courses\Traits\GeneratesQuestionsFromBank;
    
    protected static string $relationship = 'lessons';

    protected static ?string $title = 'جميع الدروس';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('section_id')
                    ->label('القسم')
                    ->relationship('section', 'title', fn ($query, $get) => 
                        $query->where('course_id', $this->getOwnerRecord()->id)
                    )
                    ->required()
                    ->searchable()
                    ->preload(),
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
                
                FileUpload::make('video_path')
                    ->label('فيديو الدرس')
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('section.title')
                    ->label('القسم')
                    ->searchable()
                    ->sortable()
                    ->badge(),
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
                    ->getStateUsing(fn ($record) => $record->video ? '✓' : '✗')
                    ->badge()
                    ->color(fn ($record) => $record->video ? 'success' : 'gray'),
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
                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('section_id')
                    ->label('القسم')
                    ->relationship('section', 'title', fn ($query) => 
                        $query->where('course_id', $this->getOwnerRecord()->id)
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->after(function ($record) {
                        // إرسال إشعار للطلاب المشتركين عند إضافة محاضرة جديدة
                        if ($record->section && $record->section->course) {
                            $course = $record->section->course;
                            $enrollments = $course->enrollments()->with('user')->get();
                            
                            foreach ($enrollments as $enrollment) {
                                $enrollment->user->notify(new \App\Notifications\NewLessonAddedNotification($record));
                            }
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make(),
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
                                    return \App\Models\QuestionBank::where(function($q) use ($courseId) {
                                        $q->whereNull('course_id')
                                          ->orWhere('course_id', $courseId);
                                    })
                                    ->where('is_active', true)
                                    ->with('questions')
                                    ->get()
                                    ->mapWithKeys(fn ($bank) => [
                                        $bank->id => $bank->title . ' (' . $bank->questions->count() . ' سؤال' . ($bank->course_id ? ' - خاص بالدورة' : ' - عام') . ')'
                                    ])
                                    ->toArray();
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
                    ->form(function ($record) {
                        return [
                            \Filament\Forms\Components\Repeater::make('files')
                                ->label('الملفات')
                                ->schema([
                                    \Filament\Forms\Components\TextInput::make('name')
                                        ->label('اسم الملف')
                                        ->required(),
                                    \Filament\Forms\Components\FileUpload::make('path')
                                        ->label('الملف')
                                        ->disk('public')
                                        ->directory('lesson-files')
                                        ->required(),
                                ])
                                ->defaultItems(0)
                                ->default($record->files->map(fn ($file) => [
                                    'name' => $file->name,
                                    'path' => $file->path,
                                ])->toArray()),
                        ];
                    })
                    ->fillForm(function ($record) {
                        return [
                            'files' => $record->files->map(fn ($file) => [
                                'name' => $file->name,
                                'path' => $file->path,
                            ])->toArray(),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        // Delete existing files
                        $record->files()->delete();
                        
                        // Create new files
                        foreach ($data['files'] ?? [] as $fileData) {
                            $record->files()->create([
                                'name' => $fileData['name'],
                                'path' => $fileData['path'],
                            ]);
                        }
                    })
                    ->modalHeading('إدارة الملفات')
                    ->modalSubmitActionLabel('حفظ')
                    ->modalWidth('2xl'),
                Action::make('manage_lesson_comments')
                    ->label('التعليقات')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('info')
                    ->form(function ($record) {
                        return [
                            \Filament\Forms\Components\Repeater::make('comments')
                                ->label('التعليقات')
                                ->schema([
                                    \Filament\Forms\Components\Select::make('user_id')
                                        ->label('المستخدم')
                                        ->options(\App\Models\User::pluck('name', 'id'))
                                        ->default(auth()->id())
                                        ->required()
                                        ->searchable(),
                                    \Filament\Forms\Components\Textarea::make('body')
                                        ->label('التعليق')
                                        ->required()
                                        ->rows(3),
                                ])
                                ->defaultItems(0)
                                ->default($record->comments->map(fn ($comment) => [
                                    'id' => $comment->id,
                                    'user_id' => $comment->user_id,
                                    'body' => $comment->body,
                                ])->toArray())
                                ->reorderable(false)
                                ->deletable(true)
                                ->addable(true),
                        ];
                    })
                    ->fillForm(function ($record) {
                        return [
                            'comments' => $record->comments->map(fn ($comment) => [
                                'id' => $comment->id,
                                'user_id' => $comment->user_id,
                                'body' => $comment->body,
                            ])->toArray(),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        // حفظ التعليقات الجديدة فقط
                        foreach ($data['comments'] ?? [] as $commentData) {
                            if (!isset($commentData['id'])) {
                                // تعليق جديد
                                $comment = $record->comments()->create([
                                    'user_id' => $commentData['user_id'],
                                    'body' => $commentData['body'],
                                ]);
                                
                                // إرسال إشعار للمدرس
                                if ($record->section && $record->section->course) {
                                    $instructor = $record->section->course->instructor;
                                    if ($instructor && $instructor->id !== $commentData['user_id']) {
                                        $instructor->notify(new \App\Notifications\LessonCommentNotification($comment));
                                    }
                                }
                            }
                        }
                        
                        Notification::make()
                            ->title('تم إضافة التعليق')
                            ->success()
                            ->send();
                    })
                    ->modalHeading(fn ($record) => 'التعليقات على الدرس: ' . $record->title)
                    ->modalWidth('4xl'),
                Action::make('manage_lesson_qa')
                    ->label('أسئلة الدرس (Q&A)')
                    ->icon('heroicon-o-question-mark-circle')
                    ->color('warning')
                    ->form(function ($record) {
                        return [
                            \Filament\Forms\Components\Repeater::make('questions')
                                ->label('الأسئلة')
                                ->schema([
                                    \Filament\Forms\Components\TextInput::make('student_name')
                                        ->label('الطالب')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->formatStateUsing(function ($state, $get) {
                                            if ($state) {
                                                return $state;
                                            }
                                            $userId = $get('user_id');
                                            if ($userId) {
                                                $user = \App\Models\User::find($userId);
                                                return $user ? $user->name : '';
                                            }
                                            return '';
                                        })
                                        ->visible(fn ($get) => null !== $get('id')),
                                    \Filament\Forms\Components\Select::make('user_id')
                                        ->label('الطالب')
                                        ->options(function () {
                                            // محاولة جلب الطلاب أولاً
                                            $studentUsers = \App\Models\User::whereHas('roles', function ($q) {
                                                $q->where('name', 'student');
                                            })->get();
                                            
                                            // إذا لم يكن هناك طلاب، عرض جميع المستخدمين
                                            if ($studentUsers->isEmpty()) {
                                                $studentUsers = \App\Models\User::all();
                                            }
                                            
                                            return $studentUsers->pluck('name', 'id')->toArray();
                                        })
                                        ->default(auth()->id())
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn ($get) => !$get('id')),
                                    \Filament\Forms\Components\Textarea::make('question')
                                        ->label('السؤال')
                                        ->required()
                                        ->rows(3)
                                        ->disabled(fn ($get) => null !== $get('id')),
                                    \Filament\Forms\Components\Toggle::make('is_answered')
                                        ->label('تم الرد')
                                        ->default(false)
                                        ->disabled(),
                                    \Filament\Forms\Components\Repeater::make('answers')
                                        ->label('الردود')
                                        ->schema([
                                            \Filament\Forms\Components\Textarea::make('answer')
                                                ->label('الرد')
                                                ->required()
                                                ->rows(3),
                                        ])
                                        ->defaultItems(0)
                                        ->default(function ($get) use ($record) {
                                            $questionId = $get('id');
                                            if ($questionId) {
                                                $question = \App\Models\CourseQuestion::find($questionId);
                                                return $question ? $question->answers->map(fn ($a) => [
                                                    'id' => $a->id,
                                                    'answer' => $a->answer,
                                                ])->toArray() : [];
                                            }
                                            return [];
                                        })
                                        ->addable(true)
                                        ->deletable(true)
                                        ->reorderable(false)
                                        ->visible(fn ($get) => null !== $get('id')),
                                ])
                                ->defaultItems(0)
                                ->default($record->questions()->with('user')->get()->map(fn ($q) => [
                                    'id' => $q->id,
                                    'user_id' => $q->user_id,
                                    'student_name' => $q->user->name ?? '',
                                    'question' => $q->question,
                                    'is_answered' => $q->is_answered,
                                ])->toArray())
                                ->reorderable(false)
                                ->deletable(true)
                                ->addable(true),
                        ];
                    })
                    ->fillForm(function ($record) {
                        return [
                            'questions' => $record->questions()->with(['answers', 'user'])->get()->map(fn ($q) => [
                                'id' => $q->id,
                                'user_id' => $q->user_id,
                                'student_name' => $q->user->name ?? '',
                                'question' => $q->question,
                                'is_answered' => $q->is_answered,
                                'answers' => $q->answers->map(fn ($a) => [
                                    'id' => $a->id,
                                    'answer' => $a->answer,
                                ])->toArray(),
                            ])->toArray(),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        foreach ($data['questions'] ?? [] as $questionData) {
                            if (!isset($questionData['id'])) {
                                // سؤال جديد
                                $question = \App\Models\CourseQuestion::create([
                                    'course_id' => $record->section->course_id,
                                    'lesson_id' => $record->id,
                                    'user_id' => $questionData['user_id'],
                                    'question' => $questionData['question'],
                                    'is_answered' => false,
                                ]);
                                
                                // إرسال إشعار للمدرس
                                if ($record->section && $record->section->course) {
                                    $instructor = $record->section->course->instructor;
                                    if ($instructor && $instructor->id !== $questionData['user_id']) {
                                        $instructor->notify(new \App\Notifications\CourseQuestionAnsweredNotification($question, 'new_question'));
                                    }
                                }
                            } else {
                                // سؤال موجود - حفظ الردود الجديدة
                                $question = \App\Models\CourseQuestion::find($questionData['id']);
                                if ($question && isset($questionData['answers'])) {
                                    $existingAnswerIds = collect($questionData['answers'])->pluck('id')->filter();
                                    
                                    // حذف الردود المحذوفة
                                    $question->answers()->whereNotIn('id', $existingAnswerIds)->delete();
                                    
                                    // إضافة/تحديث الردود
                                    foreach ($questionData['answers'] as $answerData) {
                                        if (isset($answerData['id'])) {
                                            // تحديث رد موجود
                                            $question->answers()->where('id', $answerData['id'])->update([
                                                'answer' => $answerData['answer'],
                                            ]);
                                        } else {
                                            // إضافة رد جديد
                                            $answer = $question->answers()->create([
                                                'user_id' => auth()->id(),
                                                'answer' => $answerData['answer'],
                                            ]);
                                            
                                            // تحديث حالة السؤال
                                            $question->update(['is_answered' => true]);
                                            
                                            // إرسال إشعار للطالب
                                            if ($question->user_id !== auth()->id()) {
                                                $question->user->notify(new \App\Notifications\CourseQuestionAnsweredNotification($question, 'answered'));
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        Notification::make()
                            ->title('تم الحفظ')
                            ->success()
                            ->send();
                    })
                    ->modalHeading(fn ($record) => 'أسئلة الدرس (Q&A): ' . $record->title)
                    ->modalWidth('4xl'),
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
