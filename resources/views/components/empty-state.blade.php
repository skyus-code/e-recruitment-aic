@props([
    'title' => 'Belum ada data',
    'description' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'card flex flex-col items-center justify-center px-6 py-14 text-center']) }}>
    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-900">
        @if ($icon)
            {{ $icon }}
        @else
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5 12 3 3.75 7.5m16.5 0v9L12 21l-8.25-4.5v-9m16.5 0L12 12m-8.25-4.5L12 12m0 0V21" />
            </svg>
        @endif
    </div>

    <h3 class="mt-4 text-base font-bold text-slate-900">{{ $title }}</h3>

    @if ($description)
        <p class="mt-1.5 max-w-md text-sm leading-relaxed text-slate-500">{{ $description }}</p>
    @endif

    @isset($actions)
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2.5">
            {{ $actions }}
        </div>
    @endisset
</div>
