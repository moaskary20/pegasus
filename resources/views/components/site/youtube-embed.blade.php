{{--
    تضمين YouTube بدون شريط التحكم الافتراضي (معامل controls=0 في رابط الـ embed).
    طبقات تغطية فوق الـ iframe: لا يمكن إزالة Share / علامة YouTube من واجهة المضمّن رسمياً،
    لذا نُظلّ الزوايا (يمين أعلى ويمين أسفل) ونمنع النقر على زر المشاركة حيث أمكن.
--}}
@props([
    'src',
    'title' => 'YouTube',
    'iframeId' => null,
    'fillContainer' => false,
    'alpineIframeRef' => null,
    'watermarkUser' => null,
])

@php
    $wrapperClass = $fillContainer
        ? 'absolute inset-0 h-full w-full overflow-hidden bg-slate-900'
        : 'relative w-full overflow-hidden bg-slate-900 aspect-video';
@endphp

<div {{ $attributes->class($wrapperClass) }}>
    <iframe
        @if(filled($iframeId)) id="{{ $iframeId }}" @endif
        @if(filled($alpineIframeRef)) x-ref="{{ $alpineIframeRef }}" @endif
        src="{{ $src }}"
        title="{{ $title }}"
        class="absolute inset-0 h-full w-full border-0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen
        referrerpolicy="strict-origin-when-cross-origin"
    ></iframe>
    <div
        class="pointer-events-auto absolute inset-x-0 top-0 z-[1] h-[5.75rem] bg-gradient-to-b from-black from-30% via-black/80 to-transparent sm:h-28"
        aria-hidden="true"
    ></div>
    {{-- يمين أعلى (LTR داخل iframe): عنوان/قناة وعلامة YouTube — نستخدم right وليس logical end --}}
    <div
        class="pointer-events-auto absolute right-0 top-0 z-[1] h-[4.5rem] w-[10rem] bg-gradient-to-bl from-black/90 via-black/45 to-transparent sm:h-[5.5rem] sm:w-[12rem]"
        aria-hidden="true"
    ></div>
    {{-- يمين أسفل: زر Share وكلمة YouTube عند الإيقاف --}}
    <div
        class="pointer-events-auto absolute right-0 bottom-0 z-[1] h-[5.5rem] w-[11rem] bg-gradient-to-tl from-black/90 via-black/50 to-transparent sm:h-[6.5rem] sm:w-[13rem]"
        aria-hidden="true"
    ></div>
    @if(filled($watermarkUser))
        <x-site.video-user-watermark :name="$watermarkUser" />
    @endif
</div>
