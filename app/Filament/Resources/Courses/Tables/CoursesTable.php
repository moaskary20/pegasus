<?php

namespace App\Filament\Resources\Courses\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('')
                    ->square()
                    ->size(60)
                    ->defaultImageUrl('https://placehold.co/60x60/10b981/white?text=C'),
                    
                TextColumn::make('title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->title)
                    ->description(fn ($record) => $record->category?->name),
                    
                TextColumn::make('instructor.name')
                    ->label('المدرس')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-user'),
                    
                TextColumn::make('level')
                    ->label('المستوى')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'beginner' => 'مبتدئ',
                        'intermediate' => 'متوسط',
                        'advanced' => 'متقدم',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'beginner' => 'success',
                        'intermediate' => 'warning',
                        'advanced' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'beginner' => 'heroicon-o-academic-cap',
                        'intermediate' => 'heroicon-o-fire',
                        'advanced' => 'heroicon-o-bolt',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                    
                TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 0) . ' ج.م' : 'مجاني')
                    ->color(fn ($state) => $state > 0 ? 'success' : 'info')
                    ->weight('bold')
                    ->sortable(),
                    
                TextColumn::make('students_count')
                    ->label('الطلاب')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-o-users'),
                    
                TextColumn::make('rating')
                    ->label('التقييم')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 1) . ' ⭐' : '—')
                    ->color(fn ($state) => match(true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray',
                    }),
                    
                IconColumn::make('is_published')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-pencil-square')
                    ->trueColor('success')
                    ->falseColor('warning'),
                    
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->date('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('level')
                    ->label('المستوى')
                    ->options([
                        'beginner' => 'مبتدئ',
                        'intermediate' => 'متوسط',
                        'advanced' => 'متقدم',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\TernaryFilter::make('is_published')
                    ->label('منشور')
                    ->placeholder('الكل')
                    ->trueLabel('منشور فقط')
                    ->falseLabel('غير منشور فقط'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('students')
                    ->label('المشتركين')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->modalHeading(fn ($record) => 'المشتركين في الدورة: ' . $record->title)
                    ->modalContent(function ($record) {
                        $enrollments = \App\Models\Enrollment::where('course_id', $record->id)
                            ->with(['user', 'course'])
                            ->orderBy('enrolled_at', 'desc')
                            ->get();
                        
                        if ($enrollments->isEmpty()) {
                            return new \Illuminate\Support\HtmlString('
                                <div class="flex flex-col items-center justify-center min-h-[500px] py-20 px-4 text-center">
                                    <svg class="w-64 h-64 text-white dark:text-white mb-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
                                    <h3 class="text-2xl font-semibold text-white dark:text-white mb-3">لا يوجد مشتركين</h3>
                                    <p class="text-lg text-white dark:text-white opacity-90">لم يسجل أي طالب في هذه الدورة بعد</p>
                                </div>
                            ');
                        }
                        
                        $totalLessons = $record->lessons()->count();
                        
                        return view('filament.tables.students-list', [
                            'enrollments' => $enrollments,
                            'totalLessons' => $totalLessons,
                            'courseId' => $record->id,
                        ]);
                    })
                    ->modalActions(function ($record) {
                        return [
                            Action::make('add_student')
                                ->label('إضافة مشترك')
                                ->icon('heroicon-o-plus')
                                ->color('success')
                                ->form(function () use ($record) {
                                    $courseId = $record->id;
                                return [
                                    Select::make('user_id')
                                        ->label('الطالب')
                                        ->options(function () {
                                            return \App\Models\User::whereHas('roles', fn ($q) => 
                                                $q->where('name', 'student')
                                            )
                                            ->get()
                                            ->mapWithKeys(fn ($user) => [$user->id => $user->name . ' (' . $user->email . ')'])
                                            ->toArray();
                                        })
                                        ->required()
                                        ->searchable()
                                        ->helperText('اختر الطالب المراد إضافته كـ مشترك'),
                                    Select::make('order_id')
                                        ->label('الطلب (اختياري)')
                                        ->options(function (callable $get) {
                                            $userId = $get('user_id');
                                            if (!$userId) {
                                                return [];
                                            }
                                            return \App\Models\Order::where('user_id', $userId)
                                                ->orderBy('created_at', 'desc')
                                                ->get()
                                                ->mapWithKeys(fn ($order) => [
                                                    $order->id => $order->order_number . ' - ' . number_format($order->total, 2) . ' ج.م'
                                                ])
                                                ->toArray();
                                        })
                                        ->searchable()
                                        ->helperText('اختر الطلب المرتبط بهذا الاشتراك (اختياري)')
                                        ->reactive()
                                        ->visible(fn (callable $get) => !empty($get('user_id')))
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) use ($courseId) {
                                            if ($state) {
                                                $order = \App\Models\Order::find($state);
                                                if ($order) {
                                                    $orderItem = \App\Models\OrderItem::where('order_id', $order->id)
                                                        ->where('course_id', $courseId)
                                                        ->first();
                                                    if ($orderItem) {
                                                        $set('price_paid', $orderItem->price);
                                                    }
                                                }
                                            }
                                        }),
                                    TextInput::make('price_paid')
                                        ->label('السعر المدفوع')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('ج.م'),
                                    DateTimePicker::make('enrolled_at')
                                        ->label('تاريخ التسجيل')
                                        ->default(now()),
                                ];
                            })
                            ->action(function (array $data) use ($record) {
                                // التحقق من عدم وجود اشتراك مسبق
                                $existingEnrollment = \App\Models\Enrollment::where('user_id', $data['user_id'])
                                    ->where('course_id', $record->id)
                                    ->first();
                                
                                if ($existingEnrollment) {
                                    Notification::make()
                                        ->title('خطأ')
                                        ->body('هذا الطالب مشترك بالفعل في هذه الدورة')
                                        ->danger()
                                        ->send();
                                    } else {
                                        \App\Models\Enrollment::create([
                                            'user_id' => $data['user_id'],
                                            'course_id' => $record->id,
                                            'order_id' => $data['order_id'] ?? null,
                                            'price_paid' => $data['price_paid'] ?? 0,
                                            'enrolled_at' => $data['enrolled_at'] ?? now(),
                                            'progress_percentage' => 0,
                                        ]);
                                        
                                        Notification::make()
                                            ->title('نجح')
                                            ->body('تم إضافة الطالب كـ مشترك في الدورة بنجاح')
                                            ->success()
                                            ->send();
                                    }
                                })
                                ->closeModalByClickingAway(false)
                                ->modalHeading('إضافة مشترك جديد')
                                ->modalWidth('md'),
                        ];
                    })
                    ->modalWidth('6xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
