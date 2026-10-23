@props([
    'label',
    'value',
    'caption' => null,
    'meta' => null,
    'accent' => 'blue',
])

@php
    $accents = [
        'blue' => 'border-t-blue-900',
        'emerald' => 'border-t-emerald-600',
        'amber' => 'border-t-amber-600',
        'red' => 'border-t-red-600',
        'slate' => 'border-t-slate-400',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'card border-t-4 '.($accents[$accent] ?? $accents['blue']).' p-5']) }}>
    <p class="font-mono text-[10px] font-semibold tracking-[0.15em] text-slate-500 uppercase">
        {{ $label }}
    </p>

    <p class="mt-3 font-heading text-3xl leading-none font-extrabold text-slate-900">
        {!! $value !!}
    </p>

    @if ($caption)
        <p class="mt-2.5 text-xs leading-relaxed text-slate-500">{!! $caption !!}</p>
    @endif

    @if ($meta)
        <div class="mt-3 flex items-center gap-2 border-t border-dashed border-slate-200 pt-3">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
            <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-500 uppercase">{{ $meta }}</span>
        </div>
    @endif
</div>
