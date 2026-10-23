@props([
    'status',
])

@php
    use App\Enums\ApplicationStatusEnum;

    $status = $status instanceof ApplicationStatusEnum ? $status : ApplicationStatusEnum::tryFrom((string) $status);
@endphp

@if ($status)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold tracking-wide '.$status->badgeClasses()]) }}>
        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
        {{ $status->label() }}
    </span>
@endif
