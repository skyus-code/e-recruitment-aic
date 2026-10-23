@props([
    'provider',
    'href',
])

@php
    $presets = [
        'google' => [
            'label' => 'Google',
            'classes' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
        ],
        'linkedin' => [
            'label' => 'LinkedIn',
            'classes' => 'border border-transparent bg-[#0A66C2] text-white hover:bg-[#004182]',
        ],
        'facebook' => [
            'label' => 'Facebook',
            'classes' => 'border border-transparent bg-[#1877F2] text-white hover:bg-[#0f5fc4]',
        ],
    ];

    $preset = $presets[$provider] ?? [
        'label' => ucfirst($provider),
        'classes' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
    ];

    $classes = trim('inline-flex w-full items-center justify-center gap-2.5 rounded-lg px-4 py-2.5 text-sm font-semibold shadow-sm transition duration-200 focus-visible:ring-2 focus-visible:ring-blue-900 focus-visible:ring-offset-2 focus-visible:outline-none '.$preset['classes']);
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @switch($provider)
        @case('google')
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1Z" />
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23Z" />
                <path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84Z" />
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15A11 11 0 0 0 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38Z" />
            </svg>
            @break
        @case('linkedin')
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M20.45 20.45h-3.56v-5.57c0-1.33-.03-3.04-1.85-3.04-1.86 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29ZM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13Zm1.78 13.02H3.56V9h3.56v11.45ZM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.22.79 24 1.77 24h20.45c.98 0 1.78-.78 1.78-1.73V1.73C24 .77 23.2 0 22.22 0Z" />
            </svg>
            @break
        @case('facebook')
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07c0 6.02 4.39 11.02 10.13 11.93v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8v8.44C19.61 23.09 24 18.09 24 12.07Z" />
            </svg>
            @break
    @endswitch

    <span>Lanjutkan dengan {{ $preset['label'] }}</span>
</a>
