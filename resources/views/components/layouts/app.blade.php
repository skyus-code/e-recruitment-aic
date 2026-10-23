@props([
    'title' => null,
    'fluid' => false,
])

@php
    use App\Enums\RoleEnum;

    $user = auth()->user();
    $role = $user?->role ?? RoleEnum::Pelamar;

    /** @var array<int, array{label: string, route: string, match: string, icon: string}> $navigation */
    $navigation = match ($role) {
        RoleEnum::Admin => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'],
            ['label' => 'Kelola Lowongan', 'route' => 'admin.jobs.index', 'match' => 'admin.jobs.*', 'icon' => 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z'],
            ['label' => 'Seleksi Berkas NLP', 'route' => 'admin.screening.index', 'match' => 'admin.screening.*', 'icon' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z'],
            ['label' => 'Jadwal Wawancara', 'route' => 'admin.schedules.index', 'match' => 'admin.schedules.*', 'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'],
            ['label' => 'Statistik Rekrutmen', 'route' => 'admin.statistics.index', 'match' => 'admin.statistics.*', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
            ['label' => 'Master & Settings', 'route' => 'admin.settings.index', 'match' => 'admin.settings.*', 'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z'],
            ['label' => 'Pengaturan Akun', 'route' => 'password.edit', 'match' => 'password.*', 'icon' => 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z'],
        ],
        RoleEnum::Manajer => [
            ['label' => 'Keputusan Final', 'route' => 'manajer.dashboard', 'match' => 'manajer.dashboard', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ['label' => 'Jadwal & Penilaian', 'route' => 'manajer.interviews.index', 'match' => 'manajer.interviews.*', 'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h2.25M9 15H5.25m7.5 0v2.25c0 .621.504 1.125 1.125 1.125h2.25c.621 0 1.125-.504 1.125-1.125V18.25m-6-3h6.75'],
            ['label' => 'Review Dossier Kandidat', 'route' => 'manajer.dossier.index', 'match' => 'manajer.dossier.*', 'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'],
            ['label' => 'Rekap & Offering Log', 'route' => 'manajer.decisions.index', 'match' => 'manajer.decisions.*', 'icon' => 'M16.5 8.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v8.25A2.25 2.25 0 0 0 6 16.5h2.25m8.25-8.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25H9.75A2.25 2.25 0 0 1 7.5 18v-1.5m0 0V11.25A2.25 2.25 0 0 1 9.75 9h6.75'],
            ['label' => 'Pengaturan Akun', 'route' => 'password.edit', 'match' => 'password.*', 'icon' => 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z'],
        ],
        default => [
            ['label' => 'Dashboard', 'route' => 'pelamar.dashboard', 'match' => 'pelamar.dashboard', 'icon' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75'],
            ['label' => 'Lowongan Kerja', 'route' => 'pelamar.jobs.index', 'match' => 'pelamar.jobs.*', 'icon' => 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z'],
            ['label' => 'Profil & Master Data', 'route' => 'pelamar.profile.edit', 'match' => 'pelamar.profile.*', 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
            ['label' => 'Kelola Berkas Lamaran', 'route' => 'pelamar.cv-files.index', 'match' => 'pelamar.cv-files.*', 'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'],
            ['label' => 'Lamaran Saya', 'route' => 'pelamar.applications.index', 'match' => 'pelamar.applications.*', 'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z'],
            ['label' => 'Jadwal Wawancara', 'route' => 'pelamar.interviews.index', 'match' => 'pelamar.interviews.*', 'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'],
            ['label' => 'Pengaturan Akun', 'route' => 'password.edit', 'match' => 'password.*', 'icon' => 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z'],
        ],
    };

    $roleLabel = $role->label();
    $pageTitle = isset($title) && $title ? $title.' · ' : '';
@endphp

<x-layouts.guest :title="$title" :shell="true">
    <div class="min-h-screen bg-slate-100" x-data="{ mobileNav: false }" x-on:keydown.escape.window="mobileNav = false">
        {{-- Top bar --}}
        <header class="sticky top-0 z-40 border-b border-blue-800 bg-blue-900 text-white shadow-md">
            <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        x-on:click="mobileNav = !mobileNav"
                        class="cursor-pointer rounded-lg p-2 text-blue-100 transition-colors duration-200 hover:bg-blue-800 hover:text-white lg:hidden"
                        aria-label="Buka menu navigasi"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-white p-0.5 transition-transform duration-200 group-hover:scale-105">
                            <img src="{{ asset('logo-aic.png') }}" alt="Ar-Rahman Islamic Center" class="h-full w-full object-contain">
                        </span>
                        <span class="min-w-0 leading-tight">
                            <span class="block truncate font-heading text-sm font-bold text-white">Ar-Rahman Islamic Center</span>
                            <span class="block truncate font-mono text-[10px] tracking-wider text-blue-200 uppercase">E-Recruitment System</span>
                        </span>
                    </a>

                    <span class="ml-1 hidden rounded-full bg-blue-800 px-2.5 py-1 font-mono text-[10px] font-semibold tracking-wider text-blue-100 ring-1 ring-blue-700 sm:inline-flex">
                        {{ $roleLabel }}
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right leading-tight sm:block">
                        <p class="truncate text-sm font-semibold text-white">{{ $user?->username ?: ($user?->name ?? 'Tamu') }}</p>
                        <p class="truncate font-mono text-[10px] text-blue-200">{{ $user?->email ?? '' }}</p>
                    </div>

                    <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                        <button
                            type="button"
                            x-on:click="open = !open"
                            class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-900 ring-2 ring-blue-700 transition-all duration-200 hover:ring-blue-400"
                            aria-label="Menu akun"
                        >
                            {{ $user?->initials() ?? 'GU' }}
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            class="absolute right-0 mt-3 w-64 overflow-hidden rounded-xl border border-slate-200 bg-white py-1.5 text-left shadow-xl"
                        >
                            <div class="border-b border-slate-100 px-4 py-3">
                                <p class="truncate text-sm font-bold text-slate-900">{{ $user?->username ?: $user?->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $user?->email }}</p>
                                <span class="mt-2 inline-flex rounded-full bg-blue-100 px-2 py-0.5 font-mono text-[10px] font-semibold text-blue-900">{{ $roleLabel }}</span>
                            </div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full cursor-pointer px-4 py-2.5 text-left text-sm font-medium text-red-600 transition-colors duration-200 hover:bg-red-50">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="mx-auto flex w-full max-w-[1700px]">
            {{-- Desktop sidebar --}}
            <aside class="sticky top-16 hidden h-[calc(100vh-4rem)] w-64 shrink-0 flex-col justify-between border-r border-slate-200 bg-white lg:flex">
                <div class="flex-1 overflow-y-auto px-4 py-6">
                    <p class="px-3 font-mono text-[10px] font-semibold tracking-[0.15em] text-slate-400 uppercase">
                        Navigasi {{ $roleLabel }}
                    </p>

                    <nav class="mt-3 space-y-1">
                        @foreach ($navigation as $index => $item)
                            <a
                                href="{{ route($item['route']) }}"
                                @class([
                                    'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-all duration-200 ease-in-out',
                                    'bg-blue-100 text-blue-900 shadow-sm' => request()->routeIs($item['match']),
                                    'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! request()->routeIs($item['match']),
                                ])
                                @if (request()->routeIs($item['match'])) aria-current="page" @endif
                            >
                                <svg
                                    class="h-[18px] w-[18px] shrink-0 {{ request()->routeIs($item['match']) ? 'text-blue-900' : 'text-slate-400 group-hover:text-slate-500' }}"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                                </svg>
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if ($role === RoleEnum::Admin && $item['route'] === 'admin.dashboard' && $newApplicationCount > 0)
                                    <span class="ml-auto inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800"><span>!</span>{{ $newApplicationCount > 99 ? '99+' : $newApplicationCount }}</span>
                                @endif
                            </a>
                        @endforeach
                    </nav>
                </div>


            </aside>

            {{-- Mobile sidebar --}}
            <div x-show="mobileNav" x-cloak class="fixed inset-0 z-50 lg:hidden">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-on:click="mobileNav = false"></div>
                <div
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="-translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="-translate-x-full"
                    class="absolute inset-y-0 left-0 flex w-72 max-w-[80%] flex-col bg-white shadow-xl"
                >
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <span class="font-heading text-sm font-bold text-slate-900">Menu {{ $roleLabel }}</span>
                        <button type="button" x-on:click="mobileNav = false" class="cursor-pointer rounded-lg p-1.5 text-slate-400 hover:bg-slate-100" aria-label="Tutup menu">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" /></svg>
                        </button>
                    </div>

                    <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-5">
                        @foreach ($navigation as $index => $item)
                            <a
                                href="{{ route($item['route']) }}"
                                x-on:click="mobileNav = false"
                                @class([
                                    'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-all duration-200',
                                    'bg-blue-100 text-blue-900' => request()->routeIs($item['match']),
                                    'text-slate-600 hover:bg-slate-50' => ! request()->routeIs($item['match']),
                                ])
                            >
                                <span class="w-5 shrink-0">
                                    <svg
                                        class="h-[18px] w-[18px] {{ request()->routeIs($item['match']) ? 'text-blue-900' : 'text-slate-400' }}"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        aria-hidden="true"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                                    </svg>
                                </span>
                                <span>{{ $item['label'] }}</span>
                                @if ($role === RoleEnum::Admin && $item['route'] === 'admin.dashboard' && $newApplicationCount > 0)
                                    <span class="ml-auto inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800"><span>!</span>{{ $newApplicationCount > 99 ? '99+' : $newApplicationCount }}</span>
                                @endif
                            </a>
                        @endforeach

                        <form method="POST" action="{{ route('logout') }}" class="pt-4">
                            @csrf
                            <button type="submit" class="w-full cursor-pointer rounded-lg px-3 py-2.5 text-left text-sm font-semibold text-red-600 transition-colors duration-200 hover:bg-red-50">
                                Keluar
                            </button>
                        </form>
                    </nav>
                </div>
            </div>

            {{-- Content --}}
            <main class="min-w-0 flex-1 px-4 py-7 sm:px-6 sm:py-8 lg:px-8">
                @if (session('success'))
                    <div class="mb-6">
                        <x-alert variant="success" :title="session('success')">{{ session('message', 'Perubahan berhasil disimpan.') }}</x-alert>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6">
                        <x-alert variant="danger" :title="session('error')">{{ session('message', 'Terjadi kesalahan pada proses.') }}</x-alert>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>

        <footer class="border-t border-slate-200 bg-white px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto flex max-w-[1700px] flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">
                    © {{ date('Y') }} Yayasan Generasi Cerdas Akhlak Mulia — Ar-Rahman Islamic Center.
                </p>
                <p class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Sistem Informasi Rekrutmen v1.0.0</p>
            </div>
        </footer>
    </div>
</x-layouts.guest>
