@props([
    'type' => 'text',
    'name',
    'value' => null,
    'label' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'hint' => null,
])

@php
    $classes = $type === 'file'
        ? 'input cursor-pointer text-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-blue-100 hover:file:text-blue-900'
        : 'input';
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $name }}" class="label">
            {{ $label }}
            @if ($required) <span class="text-red-600">*</span> @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if ($required) required @endif
        @if ($disabled) disabled @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >

    @if ($hint)
        <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif

    <x-input-error :name="$name" />
</div>
