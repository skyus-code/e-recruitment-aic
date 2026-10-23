@props([
    'padding' => 'p-6',
    'as' => 'div',
])

<{{ $as }} {{ $attributes->merge(['class' => 'card '.$padding]) }}>
    @isset($header)
        <div class="mb-5 border-b border-slate-100 pb-4">
            {{ $header }}
        </div>
    @endisset
    {{ $slot }}
</{{ $as }}>
