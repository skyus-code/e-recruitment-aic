@props([
    'name',
    'label' => null,
    'rows' => 4,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
])

<div class="w-full">
    @if ($label)
        <label for="{{ $name }}" class="label">
            {{ $label }}
            @if ($required) <span class="text-red-600">*</span> @endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if ($required) required @endif
        @if ($disabled) disabled @endif
        {{ $attributes->merge(['class' => 'input resize-y']) }}
    >{{ old($name, $slot) }}</textarea>

    <x-input-error :name="$name" />
</div>
