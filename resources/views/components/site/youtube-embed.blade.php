{{--
    تضمين YouTube بدون شريط التحكم الافتراضي (معامل controls=0 في رابط الـ embed).
    شريط علوي يحجب عنوان الفيديو وأيقونة القناة و Share / Watch later قدر الإمكان.
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
    @if(filled($watermarkUser))
        <x-site.video-user-watermark :name="$watermarkUser" />
    @endif
</div>
