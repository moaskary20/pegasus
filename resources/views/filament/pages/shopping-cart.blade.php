<x-filament-panels::page>
    @if(count($cartItems) > 0)
        <div class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-4">
                    @foreach($cartItems as $item)
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <div class="flex gap-4">
                                @if(isset($item['cover_image']) && $item['cover_image'])
                                    <img src="{{ asset('storage/' . $item['cover_image']) }}" 
                                         alt="{{ $item['title'] }}" 
                                         class="w-24 h-24 object-cover rounded-lg">
                                @endif
                                <div class="flex-1">
                                    <h3 class="font-semibold text-lg mb-2">{{ $item['title'] }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        المدرس: {{ $item['user']['name'] ?? 'غير محدد' }}
                                    </p>
                                    <p class="text-lg font-bold text-primary-600">
                                        {{ number_format($item['offer_price'] ?? $item['price'] ?? 0, 2) }} ج.م
                                        @if(isset($item['offer_price']) && $item['offer_price'] < $item['price'])
                                            <span class="text-sm text-gray-400 line-through ml-2">
                                                {{ number_format($item['price'], 2) }} ج.م
                                            </span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <x-filament::button
                                        wire:click="removeFromCart({{ $item['id'] }})"
                                        color="danger"
                                        size="sm"
                                    >
                                        حذف
                                    </x-filament::button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="space-y-4">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="font-semibold text-lg mb-4">ملخص الطلب</h3>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between">
                                <span>المجموع الفرعي:</span>
                                <span>{{ number_format($subtotal, 2) }} ج.م</span>
                            </div>
                            
                            @if($appliedCoupon)
                                <div class="flex justify-between text-success-600">
                                    <span>الخصم ({{ $appliedCoupon->code }}):</span>
                                    <span>-{{ number_format($discount, 2) }} ج.م</span>
                                </div>
                            @endif
                            
                            <div class="border-t pt-2 mt-2">
                                <div class="flex justify-between font-bold text-lg">
                                    <span>الإجمالي:</span>
                                    <span>{{ number_format($total, 2) }} ج.م</span>
                                </div>
                            </div>
                        </div>
                        
                        <form wire:submit.prevent="applyCoupon">
                            {{ $this->form }}
                        </form>
                        
                        @if($appliedCoupon)
                            <div class="mt-2 p-2 bg-success-50 dark:bg-success-900/20 rounded">
                                <p class="text-sm text-success-700 dark:text-success-300">
                                    ✓ كوبون {{ $appliedCoupon->code }} مطبق
                                </p>
                            </div>
                        @endif
                        
                        <x-filament::button
                            wire:click="checkout"
                            type="button"
                            size="lg"
                            class="w-full mt-4"
                        >
                            إتمام الشراء
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <x-filament::icon 
                icon="heroicon-o-shopping-cart" 
                class="h-16 w-16 text-gray-400 mx-auto mb-4"
            />
            <h3 class="text-lg font-semibold mb-2">السلة فارغة</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">
                لم تقم بإضافة أي دورات إلى السلة بعد
            </p>
            <a href="{{ \App\Filament\Pages\BrowseCourses::getUrl() }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                تصفح الدورات
            </a>
        </div>
    @endif
</x-filament-panels::page>
