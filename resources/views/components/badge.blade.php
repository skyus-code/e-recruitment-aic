@props([
    'variant' => 'slate',
])

@php
    $palette = [
        'slate' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'blue' => 'bg-blue-100 text-blue-900 ring-blue-200',
        'emerald' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'amber' => 'bg-amber-100 text-amber-700 ring-amber-200',
        'red' => 'bg-red-100 text-red-600 ring-red-200',
        'solid-emerald' => 'bg-emerald-600 text-white ring-emerald-600',
        'solid-red' => 'bg-red-600 text-white ring-red-600',
        'solid-blue' => 'bg-blue-900 text-white ring-blue-900',
    ];
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold tracking-wide ring-1 ring-inset '.($palette[$variant] ?? $palette['slate']),
]) }}>
    {{ $slot }}
</span>
