@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null,
])

<div class="flex flex-col justify-between gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:gap-6">
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 class="page-title mt-1">{{ $title }}</h1>
        @if ($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2.5">
            {{ $actions }}
        </div>
    @endisset
</div>
