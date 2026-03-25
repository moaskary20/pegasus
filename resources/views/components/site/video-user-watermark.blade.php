{{--
    طبقة فوق المشغّل: اسم المستخدم في مواضع متفرقة (علامة مائية خفيفة).
    pointer-events-none حتى لا تعيق التشغيل أو النقر على الـ iframe.
--}}
@props([
    'name',
])

@php
    $label = trim((string) $name);
    $spots = [
        ['t' => '10%', 'l' => '4%', 'r' => null, 'rot' => -10],
        ['t' => '18%', 'l' => null, 'r' => '5%', 'rot' => 8],
        ['t' => '36%', 'l' => '8%', 'r' => null, 'rot' => -6],
        ['t' => '44%', 'l' => null, 'r' => '12%', 'rot' => 12],
        ['t' => '58%', 'l' => '6%', 'r' => null, 'rot' => 5],
        ['t' => '52%', 'l' => '38%', 'r' => null, 'rot' => -4],
        ['t' => '68%', 'l' => null, 'r' => '7%', 'rot' => -9],
        ['t' => '78%', 'l' => '10%', 'r' => null, 'rot' => 7],
        ['t' => '88%', 'l' => '42%', 'r' => null, 'rot' => -3],
    ];
@endphp

@if($label !== '')
<div {{ $attributes->class('pointer-events-none absolute inset-0 z-[3] overflow-hidden select-none') }} aria-hidden="true">
    @foreach($spots as $s)
        @php
            $pos = 'top:'.$s['t'].';transform:rotate('.$s['rot'].'deg);';
            if ($s['l'] !== null) {
                $pos .= 'left:'.$s['l'].';';
            }
            if ($s['r'] !== null) {
                $pos .= 'right:'.$s['r'].';';
            }
        @endphp
        <span
            class="absolute max-w-[min(42vw,14rem)] truncate text-[11px] font-semibold text-white/45 sm:text-sm md:text-[15px] [text-shadow:0_0_8px_rgba(0,0,0,0.85),0_1px_2px_rgba(0,0,0,0.9)]"
            style="{{ $pos }}"
        >{{ $label }}</span>
    @endforeach
</div>
@endif
