@props([
    'variant' => 'info',
    'title' => null,
])

@php
    $palette = [
        'info' => ['box' => 'border-blue-200 bg-blue-50', 'dot' => 'bg-blue-900', 'text' => 'text-blue-900'],
        'success' => ['box' => 'border-emerald-200 bg-emerald-50', 'dot' => 'bg-emerald-600', 'text' => 'text-emerald-800'],
        'warning' => ['box' => 'border-amber-200 bg-amber-50', 'dot' => 'bg-amber-600', 'text' => 'text-amber-800'],
        'danger' => ['box' => 'border-red-200 bg-red-50', 'dot' => 'bg-red-600', 'text' => 'text-red-700'],
        'neutral' => ['box' => 'border-slate-200 bg-slate-50', 'dot' => 'bg-slate-500', 'text' => 'text-slate-700'],
    ];
    $style = $palette[$variant] ?? $palette['info'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl border '.$style['box'].' p-4']) }}>
    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $style['dot'] }}"></span>

    <div class="min-w-0">
        @if ($title)
            <p class="text-sm font-bold {{ $style['text'] }}">{{ $title }}</p>
        @endif
        <div class="@if ($title) mt-1 @endif text-sm leading-relaxed {{ $style['text'] }}">
            {{ $slot }}
        </div>

        @isset($actions)
            <div class="mt-3 flex flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
