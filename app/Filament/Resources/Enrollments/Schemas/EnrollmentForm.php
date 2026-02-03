<?php

namespace App\Filament\Resources\Enrollments\Schemas;

use App\Models\Course;
use App\Models\Voucher;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('subscription_type')
                    ->label('نوع الاشتراك')
                    ->options([
                        'once_4_months' => 'اشتراك مرة واحدة (كامل 4 شهور)',
                        'monthly' => 'اشتراك شهري',
                        'per_session' => 'اشتراك بالحصة',
                    ])
                    ->default('once_4_months')
                    ->required()
                    ->reactive()
                    ->afterStateHydrated(function ($state, callable $set, callable $get) {
                        // Ensure defaults are applied on first load
                        if ($state === 'once_4_months' && !$get('access_expires_at')) {
                            $set('access_expires_at', now()->addMonths(4));
                        } elseif ($state === 'monthly' && !$get('access_expires_at')) {
                            $set('access_expires_at', now()->addMonth());
                        } elseif ($state === 'per_session') {
                            if (!$get('sessions_total')) {
                                $set('sessions_total', 1);
                            }
                            if (!$get('sessions_remaining')) {
                                $set('sessions_remaining', (int) $get('sessions_total'));
                            }
                            $set('access_expires_at', null);
                        }
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state === 'once_4_months') {
                            $set('access_expires_at', now()->addMonths(4));
                            $set('sessions_total', null);
                            $set('sessions_remaining', null);
                        } elseif ($state === 'monthly') {
                            $set('access_expires_at', now()->addMonth());
                            $set('sessions_total', null);
                            $set('sessions_remaining', null);
                        } else { // per_session
                            $set('access_expires_at', null);
                            $set('sessions_total', 1);
                            $set('sessions_remaining', 1);
                        }
                    }),

                Select::make('user_id')
                    ->label('الطالب')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('course_id')
                    ->label('الدورة')
                    ->relationship('course', 'title')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        // Recalculate price if voucher payment is selected
                        if ($get('payment_method') !== 'voucher') {
                            return;
                        }

                        $code = trim((string) $get('voucher_code'));
                        if ($code === '') {
                            return;
                        }

                        $voucher = Voucher::where('code', $code)->first();
                        if (!$voucher || !$voucher->isValid()) {
                            $set('voucher_id', null);
                            return;
                        }

                        $set('voucher_id', $voucher->id);

                        $course = Course::find($state);
                        if (!$course) {
                            return;
                        }

                        $basePrice = (float) ($course->offer_price ?: $course->price ?: 0);
                        $discount = 0;
                        if ($voucher->discount_type === 'percentage') {
                            $discount = ($basePrice * ((float) $voucher->discount_percentage)) / 100;
                        } else {
                            $discount = (float) ($voucher->discount_amount ?: 0);
                        }
                        $set('price_paid', max(0, $basePrice - $discount));
                    }),

                Select::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'manual' => 'يدوي',
                        'order' => 'من خلال طلب (Order)',
                        'voucher' => 'Voucher (كود)',
                    ])
                    ->default('manual')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state !== 'order') {
                            $set('order_id', null);
                        }
                        if ($state !== 'voucher') {
                            $set('voucher_code', null);
                            $set('voucher_id', null);
                        }
                    }),

                Select::make('order_id')
                    ->label('الطلب (اختياري)')
                    ->relationship('order', 'order_number', function ($query, $get) {
                        $query = $query->orderBy('created_at', 'desc');
                        if ($get('user_id')) {
                            $query->where('user_id', $get('user_id'));
                        }
                        return $query;
                    })
                    ->searchable()
                    ->preload()
                    ->helperText('اختر الطلب المرتبط بهذا الاشتراك (اختياري)')
                    ->reactive()
                    ->visible(fn (callable $get) => $get('payment_method') === 'order')
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        // إذا تم اختيار طلب، يمكن تحديث السعر المدفوع من الطلب
                        if ($state) {
                            $set('payment_method', 'order');
                            $order = \App\Models\Order::find($state);
                            if ($order && $get('course_id')) {
                                // البحث عن سعر الدورة في الطلب
                                $orderItem = \App\Models\OrderItem::where('order_id', $order->id)
                                    ->where('course_id', $get('course_id'))
                                    ->first();
                                if ($orderItem) {
                                    $set('price_paid', $orderItem->price);
                                }
                            }
                        }
                    }),

                TextInput::make('voucher_code')
                    ->label('كود الـ Voucher')
                    ->placeholder('مثال: VCH-20260130-ABCD أو CODE123')
                    ->reactive()
                    ->visible(fn (callable $get) => $get('payment_method') === 'voucher')
                    ->required(fn (callable $get) => $get('payment_method') === 'voucher')
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $code = trim((string) $state);
                        if ($code === '') {
                            $set('voucher_id', null);
                            return;
                        }

                        $voucher = Voucher::where('code', $code)->first();
                        if (!$voucher || !$voucher->isValid()) {
                            $set('voucher_id', null);
                            return;
                        }

                        $set('voucher_id', $voucher->id);

                        $courseId = $get('course_id');
                        if ($courseId) {
                            $course = Course::find($courseId);
                            if ($course) {
                                $basePrice = (float) ($course->offer_price ?: $course->price ?: 0);
                                $discount = 0;
                                if ($voucher->discount_type === 'percentage') {
                                    $discount = ($basePrice * ((float) $voucher->discount_percentage)) / 100;
                                } else {
                                    $discount = (float) ($voucher->discount_amount ?: 0);
                                }
                                $set('price_paid', max(0, $basePrice - $discount));
                            }
                        }
                    })
                    ->rules(fn (callable $get) => [
                        function (string $attribute, $value, \Closure $fail) use ($get) {
                            if ($get('payment_method') !== 'voucher') {
                                return;
                            }

                            $code = trim((string) $value);
                            $voucher = $code !== '' ? Voucher::where('code', $code)->first() : null;

                            if (!$voucher || !$voucher->isValid()) {
                                $fail('كود الـ Voucher غير صالح أو منتهي أو غير مُفعل.');
                            }
                        },
                    ])
                    ->helperText('سيتم التحقق من صلاحية الكود وتطبيق الخصم تلقائياً.'),

                TextInput::make('sessions_total')
                    ->label('عدد الحصص')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->reactive()
                    ->visible(fn (callable $get) => $get('subscription_type') === 'per_session')
                    ->required(fn (callable $get) => $get('subscription_type') === 'per_session')
                    ->afterStateUpdated(function ($state, callable $set) {
                        $total = (int) $state;
                        $set('sessions_remaining', max(0, $total));
                    }),

                TextInput::make('sessions_remaining')
                    ->label('الحصص المتبقية')
                    ->numeric()
                    ->minValue(0)
                    ->disabled()
                    ->dehydrated()
                    ->visible(fn (callable $get) => $get('subscription_type') === 'per_session'),

                DateTimePicker::make('access_expires_at')
                    ->label('تاريخ انتهاء الاشتراك')
                    ->helperText('يُضبط تلقائياً حسب نوع الاشتراك (يمكن تعديله).')
                    ->visible(fn (callable $get) => in_array($get('subscription_type'), ['once_4_months', 'monthly'], true)),

                TextInput::make('price_paid')
                    ->label('السعر المدفوع')
                    ->numeric()
                    ->default(0)
                    ->prefix('ج.م'),
                TextInput::make('progress_percentage')
                    ->label('نسبة التقدم')
                    ->numeric()
                    ->default(0)
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(100),
                DateTimePicker::make('enrolled_at')
                    ->label('تاريخ التسجيل')
                    ->default(now()),
                DateTimePicker::make('completed_at')
                    ->label('تاريخ الإكمال'),
            ]);
    }
}
