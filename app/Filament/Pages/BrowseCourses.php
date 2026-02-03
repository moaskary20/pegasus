<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\Enrollment;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Session;

class BrowseCourses extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationLabel = 'تصفح الدورات';
    
    protected static ?string $title = 'تصفح الدورات';
    
    protected static ?int $navigationSort = 2;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;
    
    protected string $view = 'filament.pages.browse-courses';
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('student') ?? false;
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Course::query()
                    ->where('is_published', true)
                    ->with(['user', 'category', 'enrollments'])
                    ->whereDoesntHave('enrollments', function ($query) {
                        $query->where('user_id', auth()->id());
                    })
            )
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('الصورة')
                    ->circular()
                    ->size(80),
                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),
                TextColumn::make('user.name')
                    ->label('المدرس')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('التصنيف')
                    ->badge()
                    ->sortable(),
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
                    }),
                TextColumn::make('hours')
                    ->label('الساعات')
                    ->formatStateUsing(fn ($state) => $state . ' ساعة')
                    ->sortable(),
                TextColumn::make('students_count')
                    ->label('الطلاب')
                    ->counts('enrollments')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rating')
                    ->label('التقييم')
                    ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 1) . ' ⭐' : '-')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(function ($record) {
                        if ($record->offer_price && $record->offer_price < $record->price) {
                            return '<span class="line-through text-gray-400">' . number_format($record->price, 2) . ' ج.م</span> ' .
                                   '<span class="text-success-600 font-bold">' . number_format($record->offer_price, 2) . ' ج.م</span>';
                        }
                        return $record->price > 0 ? number_format($record->price, 2) . ' ج.م' : 'مجاني';
                    })
                    ->html()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\SelectFilter::make('level')
                    ->label('المستوى')
                    ->options([
                        'beginner' => 'مبتدئ',
                        'intermediate' => 'متوسط',
                        'advanced' => 'متقدم',
                    ]),
                \Filament\Tables\Filters\TernaryFilter::make('price')
                    ->label('السعر')
                    ->placeholder('الكل')
                    ->trueLabel('مجاني فقط')
                    ->falseLabel('مدفوع فقط')
                    ->queries(
                        true: fn ($query) => $query->where('price', 0),
                        false: fn ($query) => $query->where('price', '>', 0),
                    ),
            ])
            ->actions([
                Action::make('add_to_cart')
                    ->label('إضافة للسلة')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('success')
                    ->action(function (Course $record) {
                        $cart = Session::get('cart', []);
                        if (!in_array($record->id, $cart)) {
                            $cart[] = $record->id;
                            Session::put('cart', $cart);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('تمت الإضافة')
                                ->body('تمت إضافة الدورة إلى السلة')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('موجود بالفعل')
                                ->body('الدورة موجودة في السلة بالفعل')
                                ->warning()
                                ->send();
                        }
                    })
                    ->visible(fn (Course $record) => $record->price > 0),
                Action::make('enroll_free')
                    ->label('تسجيل مجاني')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Course $record) {
                        Enrollment::create([
                            'user_id' => auth()->id(),
                            'course_id' => $record->id,
                            'price_paid' => 0,
                            'enrolled_at' => now(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('تم التسجيل')
                            ->body('تم تسجيلك في الدورة بنجاح')
                            ->success()
                            ->send();
                        
                        $this->dispatch('$refresh');
                    })
                    ->visible(fn (Course $record) => $record->price == 0),
                Action::make('view')
                    ->label('عرض التفاصيل')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Course $record) => 
                        \App\Filament\Pages\ViewCourse::getUrl(['course' => $record->id])
                    )
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('لا توجد دورات متاحة')
            ->emptyStateDescription('لا توجد دورات جديدة للتصفح حالياً');
    }
}
