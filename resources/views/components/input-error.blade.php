@props(['name'])

@error($name)
    <p {{ $attributes->merge(['class' => 'error-text']) }}>{{ $message }}</p>
@enderror
