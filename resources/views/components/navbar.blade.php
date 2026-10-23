@php
    $links = [
        ['label' => 'Beranda', 'route' => 'home', 'active' => request()->routeIs('home')],
        ['label' => 'Lowongan', 'route' => 'jobs.index', 'active' => request()->routeIs('jobs.*')],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-blue-800 bg-blue-900 text-white shadow-md" x-data="{ open: false }">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-8">
            <a href="{{ route('home') }}" class="group flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-lg bg-white p-0.5 transition-transform duration-200 group-hover:scale-105">
                    <img src="{{ asset('logo-aic.png') }}" alt="Ar-Rahman Islamic Center" class="h-full w-full object-contain">
                </span>
                <span class="leading-tight">
                    <span class="block font-heading text-sm font-bold text-white">Ar-Rahman Islamic Center</span>
                    <span class="block font-mono text-[10px] tracking-[0.2em] text-blue-200 uppercase">E-Recruitment</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1 md:flex" aria-label="Navigasi utama">
                @foreach ($links as $link)
                    <a
                        href="{{ route($link['route']) }}"
                        @class([
                            'rounded-lg px-3.5 py-2 text-sm font-semibold transition-all duration-200 ease-in-out',
                            'bg-blue-800 text-white' => $link['active'],
                            'text-blue-100 hover:bg-blue-800/70 hover:text-white' => ! $link['active'],
                        ])
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="flex items-center gap-2.5">
            @auth
                <a href="{{ route(auth()->user()->role->dashboardRoute()) }}" class="btn btn-sm bg-white text-blue-900 hover:bg-blue-50">
                    Ke Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-sm hidden text-blue-100 hover:bg-blue-800 hover:text-white sm:inline-flex">
                    Masuk
                </a>
                <a href="{{ route('register') }}" class="btn btn-sm bg-white text-blue-900 hover:bg-blue-50">
                    Daftar
                </a>
            @endauth

            <button
                type="button"
                x-on:click="open = !open"
                class="cursor-pointer rounded-lg p-2 text-blue-100 transition-colors duration-200 hover:bg-blue-800 md:hidden"
                aria-label="Buka menu"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
        </div>
    </div>

    <div x-show="open" x-cloak class="border-t border-blue-800 bg-blue-900 px-4 pb-4 md:hidden">
        @foreach ($links as $link)
            <a href="{{ route($link['route']) }}" class="block rounded-lg px-3 py-2.5 text-sm font-semibold text-blue-100 hover:bg-blue-800">
                {{ $link['label'] }}
            </a>
        @endforeach

        @guest
            <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2.5 text-sm font-semibold text-blue-100 hover:bg-blue-800">
                Masuk
            </a>
        @endguest
    </div>
</header>
