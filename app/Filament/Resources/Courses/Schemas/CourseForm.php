<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('المدرس')
                    ->relationship('instructor', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                FileUpload::make('cover_image')
                    ->label('صورة الغلاف')
                    ->image()
                    ->directory('courses/covers')
                    ->visibility('public'),
                TextInput::make('title')
                    ->label('العنوان')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('الرابط')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull()
                    ->rows(4),
                Textarea::make('objectives')
                    ->label('الأهداف')
                    ->columnSpanFull()
                    ->rows(3)
                    ->helperText('أدخل الأهداف مفصولة بفواصل أو أسطر'),
                Select::make('category_id')
                    ->label('التصنيف الرئيسي')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('sub_category_id', null)),
                Select::make('sub_category_id')
                    ->label('التصنيف الفرعي')
                    ->relationship('subCategory', 'name', fn ($query, $get) => $query->where('parent_id', $get('category_id')))
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => filled($get('category_id'))),
                Select::make('level')
                    ->label('المستوى')
                    ->options([
                        'beginner' => 'مبتدئ',
                        'intermediate' => 'متوسط',
                        'advanced' => 'متقدم',
                    ])
                    ->required()
                    ->default('beginner'),
                TextInput::make('hours')
                    ->label('عدد الساعات')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                TextInput::make('lectures_count')
                    ->label('عدد المحاضرات')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                TextInput::make('price')
                    ->label('السعر الافتراضي')
                    ->numeric()
                    ->default(0)
                    ->prefix('ج.م')
                    ->minValue(0)
                    ->helperText('سعر افتراضي يُستخدم إذا لم يُحدد سعر لنوع الاشتراك'),
                TextInput::make('offer_price')
                    ->label('سعر العرض')
                    ->numeric()
                    ->prefix('ج.م')
                    ->minValue(0)
                    ->helperText('اتركه فارغاً إذا لم يكن هناك عرض'),
                
                // Subscription Prices Section
                Toggle::make('has_subscription')
                    ->label('هل تدعم هذه الدورة اشتراكات؟')
                    ->default(true)
                    ->reactive(),
                
                TextInput::make('price_once')
                    ->label('سعر اشتراك واحد (120 يوم)')
                    ->numeric()
                    ->prefix('ج.م')
                    ->minValue(0)
                    ->visible(fn ($get) => $get('has_subscription'))
                    ->helperText('اتركه فارغاً لاستخدام السعر الافتراضي'),
                
                TextInput::make('price_monthly')
                    ->label('سعر الاشتراك الشهري')
                    ->numeric()
                    ->prefix('ج.م')
                    ->minValue(0)
                    ->visible(fn ($get) => $get('has_subscription'))
                    ->helperText('اتركه فارغاً لاستخدام السعر الافتراضي'),
                
                TextInput::make('price_daily')
                    ->label('سعر الاشتراك اليومي (درس واحد)')
                    ->numeric()
                    ->prefix('ج.م')
                    ->minValue(0)
                    ->visible(fn ($get) => $get('has_subscription'))
                    ->helperText('اتركه فارغاً لاستخدام السعر الافتراضي'),
                
                Toggle::make('is_published')
                    ->label('منشور')
                    ->default(false),
            ]);
    }
}
