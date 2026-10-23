@props([
    'value' => 0,
    'max' => 100,
    'label' => null,
    'size' => 'md',
])

@php
    $percentage = $max > 0 ? max(0, min(100, round(($value / $max) * 100))) : 0;

    $color = match (true) {
        $percentage >= 75 => 'bg-emerald-600',
        $percentage >= 50 => 'bg-amber-600',
        default => 'bg-red-500',
    };

    $height = $size === 'sm' ? 'h-1.5' : 'h-2.5';
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if ($label)
        <div class="mb-1.5 flex items-baseline justify-between gap-3">
            <span class="text-xs font-semibold text-slate-600">{{ $label }}</span>
            <span class="font-mono text-xs font-bold text-slate-900">{{ $percentage }}%</span>
        </div>
    @endif

    <div class="w-full overflow-hidden rounded-full bg-slate-100 {{ $height }}" role="progressbar" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
        <div
            class="{{ $height }} rounded-full {{ $color }} transition-all duration-700 ease-out"
            style="width: {{ $percentage }}%"
        ></div>
    </div>
</div>
