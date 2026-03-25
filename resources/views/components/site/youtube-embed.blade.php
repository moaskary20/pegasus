{{--
    تضمين YouTube مع شريط تحكم افتراضي (تشغيل / صوت / تقدّم).
    شريط علوي يحجب عنوان الفيديو وأيقونة القناة و Share / Watch later (لا يوجد معامل رسمي لإخفائها).
    شعار YouTube في الزاوية السفلية يُقلّل عبر modestbranding في رابط الـ embed.
--}}
@props([
    'src',
    'title' => 'YouTube',
    'iframeId' => null,
    'fillContainer' => false,
    'alpineIframeRef' => null,
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
    {{-- لا يغطي شريط التحكم السفلي داخل الـ iframe --}}
    <div
        class="pointer-events-auto absolute inset-x-0 top-0 z-[1] h-[5.75rem] bg-gradient-to-b from-black from-30% via-black/80 to-transparent sm:h-28"
        aria-hidden="true"
    ></div>
</div>
