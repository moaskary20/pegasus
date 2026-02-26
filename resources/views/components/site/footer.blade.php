@php
    $siteLogoPath = (string) \App\Models\PlatformSetting::get('site_logo_path', '');
    $siteLogoAlt = (string) \App\Models\PlatformSetting::get('site_logo_alt', config('app.name', 'Pegasus Academy'));
    $siteLogoUrl = $siteLogoPath !== '' ? asset('storage/' . ltrim($siteLogoPath, '/')) : null;

    $footerLogoPath = (string) \App\Models\PlatformSetting::get('site_footer_logo_path', '');
    $footerLogoAlt = (string) \App\Models\PlatformSetting::get('site_footer_logo_alt', $siteLogoAlt);
    $footerLogoUrl = $footerLogoPath !== '' ? asset('storage/' . ltrim($footerLogoPath, '/')) : $siteLogoUrl;

    $googlePlayUrl = trim((string) \App\Models\PlatformSetting::get('site_app_google_play_url', ''));
    $appleStoreUrl = trim((string) \App\Models\PlatformSetting::get('site_app_apple_store_url', ''));

    // Footer idea: "أقسام الدورات" بدل "الدورات المميزة"
    $footerCategories = \App\Models\Category::query()
        ->whereNull('parent_id')
        ->where('is_active', true)
        ->with([
            'children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('name'),
        ])
        ->orderBy('sort_order')
        ->orderBy('name')
        ->limit(8)
        ->get();
@endphp

<footer class="relative overflow-hidden">
    {{-- Background (similar to hero section) --}}
    <div class="absolute inset-0 bg-gradient-to-l from-[#2c004d] to-[#2c004d]/85"></div>
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(rgba(255,255,255,.18) 1px, transparent 1px); background-size: 18px 18px;"></div>

    <div class="relative border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 py-12 text-white">
            <div class="grid gap-10 lg:grid-cols-12">
                {{-- Brand + App --}}
                <div class="lg:col-span-4">
                    <div class="flex items-center gap-3">
                        @if($footerLogoUrl)
                            <img src="{{ $footerLogoUrl }}" alt="{{ $footerLogoAlt ?: $siteLogoAlt }}" class="h-10 w-auto" style="max-width: 200px; object-fit: contain;">
                        @else
                            <div class="h-10 w-10 rounded-2xl bg-white/10 border border-white/15 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v7"/>
                                </svg>
                            </div>
                            <div class="font-extrabold text-lg">{{ config('app.name', 'Pegasus Academy') }}</div>
                        @endif
                    </div>

                    <p class="mt-4 text-sm text-white/85 leading-relaxed">
                        منصة تعليمية احترافية تساعدك على التعلم بذكاء عبر دورات حديثة، متابعة تقدمك، ودعم سريع — كل شيء في مكان واحد.
                    </p>

                    <div class="mt-6">
                        <div class="text-xs font-bold text-white/80 mb-3">حمّل التطبيق</div>
                        <div class="flex flex-col sm:flex-row gap-3">
                            @php $gpEnabled = $googlePlayUrl !== ''; @endphp
                            <a
                                href="{{ $gpEnabled ? $googlePlayUrl : '#' }}"
                                @if(!$gpEnabled) aria-disabled="true" @endif
                                class="{{ $gpEnabled ? 'hover:bg-white/15' : 'opacity-60 cursor-not-allowed' }} inline-flex items-center gap-3 px-4 py-3 rounded-2xl bg-white/10 border border-white/15 transition"
                            >
                                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/15 flex items-center justify-center">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M3.6 2.9 14.8 12 3.6 21.1c-.4-.4-.6-1-.6-1.7V4.6c0-.7.2-1.3.6-1.7Z" opacity=".85"/>
                                        <path d="M16.2 10.9 6.3 3.1c.5-.1 1.1 0 1.7.3l11.1 6.4-2.9 1.1Z"/>
                                        <path d="M19.1 14.2 8 20.6c-.6.3-1.2.4-1.7.3l9.9-7.8 2.9 1.1Z"/>
                                        <path d="M20.6 13.3c.6-.4 1-.9 1-1.3s-.4-.9-1-1.3l-2.2-1.3-3.2 1.3 3.2 1.3 2.2 1.3Z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[11px] text-white/70 font-semibold">GET IT ON</div>
                                    <div class="text-sm font-extrabold">Google Play</div>
                                    @if(!$gpEnabled)
                                        <div class="text-[11px] text-white/65">قريباً</div>
                                    @endif
                                </div>
                            </a>

                            @php $asEnabled = $appleStoreUrl !== ''; @endphp
                            <a
                                href="{{ $asEnabled ? $appleStoreUrl : '#' }}"
                                @if(!$asEnabled) aria-disabled="true" @endif
                                class="{{ $asEnabled ? 'hover:bg-white/15' : 'opacity-60 cursor-not-allowed' }} inline-flex items-center gap-3 px-4 py-3 rounded-2xl bg-white/10 border border-white/15 transition"
                            >
                                <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/15 flex items-center justify-center">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M16.4 13.5c0 2 1.8 2.7 1.8 2.7s-1.3 3.8-3.1 3.8c-.9 0-1.6-.6-2.6-.6s-1.8.6-2.6.6c-1.8 0-3.2-3.5-3.2-6.2 0-2.5 1.6-3.8 3.1-3.8.9 0 1.8.6 2.4.6.6 0 1.6-.7 2.7-.7.5 0 2 .1 3 1.6-.1.1-1.5.9-1.5 2Z"/>
                                        <path d="M14.3 6.2c.7-.9.6-2.1.6-2.3-1.1.1-2.3.7-3 1.6-.6.7-.7 2-.6 2.2 1.2.1 2.3-.6 3-1.5Z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[11px] text-white/70 font-semibold">Download on the</div>
                                    <div class="text-sm font-extrabold">App Store</div>
                                    @if(!$asEnabled)
                                        <div class="text-[11px] text-white/65">قريباً</div>
                                    @endif
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Quick links --}}
                <div class="lg:col-span-3">
                    <div class="text-sm font-extrabold">روابط سريعة</div>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                        <a href="{{ url('/') }}" class="text-white/85 hover:text-white transition">الرئيسية</a>
                        <a href="{{ route('site.about') }}" class="text-white/85 hover:text-white transition">من نحن</a>
                        <a href="{{ route('site.contact') }}" class="text-white/85 hover:text-white transition">الاتصال بنا</a>
                        <a href="{{ url('/courses') }}" class="text-white/85 hover:text-white transition">الدورات</a>
                        <a href="{{ route('site.store') }}" class="text-white/85 hover:text-white transition">المتجر</a>
                        <a href="{{ route('site.support') }}" class="text-white/85 hover:text-white transition">الدعم</a>
                        <a href="{{ route('site.account') }}" class="text-white/85 hover:text-white transition">الحساب</a>
                        <a href="{{ route('site.subscriptions') }}" class="text-white/85 hover:text-white transition">الاشتراكات</a>
                    </div>
                </div>

                {{-- Course Categories --}}
                <div class="lg:col-span-5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm font-extrabold">أقسام الدورات</div>
                        <a href="{{ url('/courses') }}" class="text-xs font-bold text-white/80 hover:text-white transition underline-offset-4 hover:underline">عرض الأقسام</a>
                    </div>

                    <div class="mt-4 grid sm:grid-cols-2 gap-3">
                        @forelse($footerCategories as $cat)
                            <div class="rounded-2xl bg-white/5 border border-white/10 p-4 hover:bg-white/10 hover:border-white/20 transition">
                                <a
                                    href="{{ url('/courses?category=' . (int) $cat->id) }}"
                                    class="group flex items-center justify-between gap-3 text-sm font-extrabold text-white"
                                >
                                    <span class="line-clamp-1 group-hover:underline underline-offset-4">{{ $cat->name }}</span>
                                    <span class="shrink-0 w-7 h-7 rounded-xl bg-white/10 border border-white/15 flex items-center justify-center group-hover:bg-white/15 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </span>
                                </a>
                                @if($cat->children->count())
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        @foreach($cat->children->take(8) as $child)
                                            <a
                                                href="{{ url('/courses?category=' . (int) $cat->id . '&sub=' . (int) $child->id) }}"
                                                class="group flex items-center gap-2 rounded-xl px-3 py-2 bg-white/5 border border-white/10 text-white/85 hover:text-white hover:bg-white/10 hover:border-white/20 transition text-xs font-bold"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full bg-white/55 group-hover:bg-white transition"></span>
                                                <span class="line-clamp-1">{{ $child->name }}</span>
                                            </a>
                                        @endforeach
                                    </div>

                                    <div class="mt-3">
                                        <a
                                            href="{{ url('/courses?category=' . (int) $cat->id) }}"
                                            class="inline-flex items-center gap-2 text-xs font-bold text-white/80 hover:text-white transition"
                                        >
                                            <span>عرض دورات هذا القسم</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </div>
                                @else
                                    <div class="mt-2 text-xs text-white/70">
                                        تصفّح الدورات داخل هذا القسم من صفحة الدورات.
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="sm:col-span-2 rounded-2xl bg-white/5 border border-white/10 p-6 text-center text-sm text-white/80">
                                لا توجد تصنيفات حالياً.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-white/10 flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                <div class="text-xs text-white/70">
                    {{ config('app.name', 'Pegasus Academy') }} © {{ now()->year }} — جميع الحقوق محفوظة
                </div>
                <div class="text-xs text-white/70 flex items-center gap-3">
                    <a href="{{ route('site.support') }}" class="hover:text-white transition">سياسة الخصوصية</a>
                    <span class="opacity-40">•</span>
                    <a href="{{ route('site.support') }}" class="hover:text-white transition">الشروط والأحكام</a>
                </div>
            </div>
        </div>
    </div>
</footer>

