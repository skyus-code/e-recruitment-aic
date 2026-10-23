@props([
    'title' => null,
    'shell' => false,
])

@php
    $documentTitle = trim(($title ? $title.' · ' : '').'E-Recruitment Ar-Rahman IC');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('Logo.png') }}">

    <title>{{ $documentTitle }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body @class([($shell ? 'bg-slate-100' : 'bg-white')])>
    <div id="app">
        @if ($shell)
            {{ $slot }}
        @else
            <div class="grid min-h-screen lg:grid-cols-2">
                {{-- Brand panel --}}
                <div class="relative hidden overflow-hidden bg-blue-900 lg:flex lg:flex-col lg:justify-between lg:p-12">
                    <div class="pointer-events-none absolute -top-32 -right-32 h-96 w-96 rounded-full bg-blue-800/60 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-40 -left-24 h-[28rem] w-[28rem] rounded-full bg-emerald-600/20 blur-3xl"></div>

                    <div class="relative">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-xl bg-white p-0.5">
                                <img src="{{ asset('logo-aic.png') }}" alt="Ar-Rahman Islamic Center" class="h-full w-full object-contain">
                            </span>
                            <span class="leading-tight">
                                <span class="block font-heading text-base font-bold text-white">Ar-Rahman Islamic Center</span>
                                <span class="block font-mono text-[10px] tracking-[0.2em] text-blue-200 uppercase">E-Recruitment System</span>
                            </span>
                        </a>
                    </div>

                    <div class="relative max-w-lg">
                        <p class="font-mono text-[11px] font-semibold tracking-[0.25em] text-blue-200 uppercase">Yayasan Generasi Cerdas Akhlak Mulia</p>
                        <h1 class="mt-4 font-heading text-4xl leading-tight font-extrabold text-white xl:text-5xl">
                            Rekrutmen staff &amp; tenaga kependidikan yang transparan.
                        </h1>
                        <p class="mt-5 text-base leading-relaxed text-blue-100/90">
                            Portal rekrutmen Ar-Rahman Islamic Center: lengkapi profilmu sekali,
                            kirimkan berkas lamaran, dan pantau hasil seleksinya — transparan
                            untuk pelamar maupun tim HR.
                        </p>

                        <dl class="mt-9 grid grid-cols-3 gap-4 border-t border-blue-800 pt-6">
                            <div>
                                <dt class="font-mono text-[10px] tracking-wider text-blue-300 uppercase">Langkah</dt>
                                <dd class="mt-1 font-heading text-2xl font-extrabold text-white">4 Tahap</dd>
                            </div>
                            <div>
                                <dt class="font-mono text-[10px] tracking-wider text-blue-300 uppercase">Pendaftaran</dt>
                                <dd class="mt-1 font-heading text-2xl font-extrabold text-white">Online</dd>
                            </div>
                            <div>
                                <dt class="font-mono text-[10px] tracking-wider text-blue-300 uppercase">Hasil</dt>
                                <dd class="mt-1 font-heading text-2xl font-extrabold text-white">Email</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="relative font-mono text-[10px] leading-relaxed tracking-wider text-blue-300/70 uppercase">
                        Sistem Informasi Rekrutmen · v1.0.0
                    </div>
                </div>

                {{-- Form panel --}}
                <div class="flex flex-col justify-center bg-white px-5 py-10 sm:px-10 lg:px-16">
                    <div class="mx-auto w-full max-w-md">
                        <a href="{{ route('home') }}" class="mb-8 inline-flex items-center gap-2.5 lg:hidden">
                            <span class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-lg bg-white p-0.5">
                                <img src="{{ asset('logo-aic.png') }}" alt="Ar-Rahman Islamic Center" class="h-full w-full object-contain">
                            </span>
                            <span class="font-heading text-sm font-bold text-slate-900">Ar-Rahman Islamic Center</span>
                        </a>

                        {{ $slot }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</body>
</html>
