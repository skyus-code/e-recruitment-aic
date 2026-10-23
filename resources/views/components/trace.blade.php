@props([
    'items' => [],
    'flow' => null,
    'tone' => 'light',
])

@php
    $isDark = $tone === 'dark';
@endphp

<div class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between sm:gap-6 {{ $isDark ? 'border-blue-800' : '' }}">
    <nav
        class="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1 font-mono text-[11px] font-medium tracking-wider uppercase {{ $isDark ? 'text-blue-300' : 'text-slate-500' }}"
        aria-label="Breadcrumb"
    >
        @foreach ($items as $index => $item)
            @if ($index > 0)
                <span class="{{ $isDark ? 'text-blue-600' : 'text-slate-300' }}">/</span>
            @endif
            @if ($loop->last)
                <span class="font-semibold {{ $isDark ? 'text-white' : 'text-blue-900' }}">{{ $item }}</span>
            @else
                <span>{{ $item }}</span>
            @endif
        @endforeach
    </nav>

    @if ($flow)
        <p class="shrink-0 font-mono text-[11px] {{ $isDark ? 'text-blue-400' : 'text-slate-400' }}">{{ $flow }}</p>
    @endif
</div>
