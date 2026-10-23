@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'disabled' => false,
])

@php
    $classes = trim('btn btn-'.$variant.($size !== 'md' ? ' btn-'.$size : ''));
    $attributes = $attributes->merge(['class' => $classes]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes }} @if ($disabled) aria-disabled="true" tabindex="-1" @endif>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes }} @if ($disabled) disabled @endif>
        {{ $slot }}
    </button>
@endif
