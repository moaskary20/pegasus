@php
    /** @var string|null $logoPath */
    $logoPath = \App\Models\PlatformSetting::get('admin_logo_path', null);
    $logoAlt = \App\Models\PlatformSetting::get('admin_logo_alt', 'Logo');
    $logoUrl = $logoPath ? asset('storage/' . ltrim($logoPath, '/')) : null;
@endphp

<a
    href="{{ url('/admin') }}"
    class="flex items-center gap-2 px-2 py-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
    style="min-height: 32px;"
>
    @if($logoUrl)
        <img
            src="{{ $logoUrl }}"
            alt="{{ $logoAlt }}"
            class="h-8 w-auto"
            style="max-width: 180px; object-fit: contain;"
        />
    @else
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-primary-600/10 flex items-center justify-center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-primary-600">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422A12.083 12.083 0 0121 12.5c0 .888-.113 1.75-.327 2.568M12 14L5.84 10.578A12.083 12.083 0 003 12.5c0 .888.113 1.75.327 2.568M12 14v7" />
                </svg>
            </div>
            <span class="text-sm font-semibold text-gray-900 dark:text-white">Pegasus Academy</span>
        </div>
    @endif
</a>

