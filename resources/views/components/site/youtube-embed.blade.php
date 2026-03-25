{{--
    تضمين YouTube مع طبقة تغطية على الزاوية العلوية اليمنى لحجب Share و Watch later
    (لا يوجد معامل رسمي في واجهة اليوتيوب لإخفائهما).
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
    <div
        class="pointer-events-auto absolute top-0 right-0 z-[1] h-[5rem] w-[min(55%,260px)] bg-gradient-to-bl from-black from-35% via-black/90 to-transparent sm:h-[5.5rem] sm:w-[min(50%,280px)]"
        aria-hidden="true"
    ></div>
</div>
