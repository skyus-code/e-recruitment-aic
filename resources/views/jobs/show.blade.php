<x-layouts.guest :title="$job->title" :shell="true">
    <div class="min-h-screen bg-slate-100">
        <x-navbar />

        {{-- Header lowongan --}}
        <section class="border-b border-slate-200 bg-blue-900">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <x-trace
                    :items="['Beranda', 'Lowongan', $job->title]"
                    flow="JOB_POSTING → {{ $job->status->label() }}"
                    tone="dark"
                />

                <div class="mt-6 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-blue-800 px-3 py-1 font-mono text-[10px] font-semibold tracking-widest text-blue-100 uppercase ring-1 ring-blue-700">
                                {{ $job->department?->name ?? 'Departemen' }}
                            </span>
                            <span class="inline-flex items-center rounded-full bg-white px-3 py-1 font-mono text-[10px] font-semibold tracking-widest text-blue-900 uppercase">
                                {{ $job->job_type->label() }}
                            </span>
                            <x-badge :variant="$job->status->value === 'open' ? 'emerald' : 'red'">
                                {{ $job->status->label() }}
                            </x-badge>
                        </div>

                        <h1 class="mt-4 font-heading text-3xl leading-tight font-extrabold text-white sm:text-4xl">
                            {{ $job->title }}
                        </h1>

                        <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-blue-100">
                            <span class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                {{ $job->location }}
                            </span>
                            @if ($job->salaryRange())
                                <span class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33"/></svg>
                                    {{ $job->salaryRange() }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                Dibuka {{ $job->created_at->translatedFormat('d F Y') }}
                            </span>
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-col gap-3">
                        @auth
                            @if ($alreadyApplied)
                                <span class="btn btn-lg cursor-not-allowed bg-emerald-600 text-white opacity-90">
                                    ✓ Sudah Anda Lamar
                                </span>
                                <a href="{{ route('pelamar.applications.index') }}" class="btn border border-blue-700 text-white hover:bg-blue-800">
                                    Lihat Status Lamaran
                                </a>
                            @elseif ($job->isOpen() && ! $emailVerified)
                                <a href="{{ route('password.edit') }}" class="btn btn-lg bg-white text-blue-900 hover:bg-blue-50 hover:shadow-lg">
                                    Verifikasi Email Sekarang
                                </a>
                            @elseif ($job->isOpen())
                                <a href="{{ route('pelamar.apply.create', $job) }}" class="btn btn-lg bg-white text-blue-900 hover:bg-blue-50 hover:shadow-lg">
                                    Lamar Sekarang
                                </a>
                            @else
                                <span class="btn btn-lg cursor-not-allowed bg-blue-800 text-blue-300">Lowongan Ditutup</span>
                            @endif
                        @else
                            <a href="{{ route('register') }}" class="btn btn-lg bg-white text-blue-900 hover:bg-blue-50 hover:shadow-lg">
                                Daftar &amp; Lamar
                            </a>
                            <a href="{{ route('login') }}" class="btn border border-blue-700 text-white hover:bg-blue-800">
                                Sudah punya akun
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>

        {{-- Konten --}}
        <section class="py-14">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1.5fr_1fr] lg:px-8">
                <div class="space-y-6">
                    <x-card>
                        <x-slot name="header">
                            <p class="eyebrow">Bagian 01 · Gambaran Posisi</p>
                        </x-slot>
                        <div class="prose prose-slate max-w-none text-sm leading-relaxed text-slate-600">
                            {!! nl2br(e($job->description)) !!}
                        </div>
                    </x-card>

                    <x-card>
                        <x-slot name="header">
                            <p class="eyebrow">Bagian 02 · Persyaratan</p>
                        </x-slot>
                        <ul class="space-y-3">
                            @foreach (preg_split('/\r?\n/', trim($job->requirements)) ?: [] as $requirement)
                                @if (trim($requirement) === '')
                                    @continue
                                @endif
                                <li class="flex items-start gap-3 text-sm leading-relaxed text-slate-600">
                                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-100 font-mono text-[10px] font-bold text-blue-900">
                                        {{ \Illuminate\Support\Str::of($requirement)->squish()->limit(1, '')->upper() }}
                                    </span>
                                    <span>{{ trim($requirement) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </x-card>

                    <x-card>
                        <x-slot name="header">
                            <p class="eyebrow">Bagian 03 · Alur Rekrutmen</p>
                        </x-slot>
                        <ol class="space-y-4">
                            @foreach ([
                                ['step' => '01', 'title' => 'Pengumpulan Berkas', 'body' => 'Unggah CV berformat PDF dan ijazah. Formasi ini telah menerima '.$job->applications_count.' lamaran sejauh ini.'],
                                ['step' => '02', 'title' => 'Skoring NLP', 'body' => 'CV diparse menjadi teks, dinormalisasi, di-stemming, lalu dicocokkan dengan '.$job->keywords_count.' kata kunci bobot formasi ini.'],
                                ['step' => '03', 'title' => 'Seleksi Berkas', 'body' => 'Admin HRD meninjau skor beserta rincian kata kunci yang cocok dan tidak cocok sebelum memutuskan lolos atau tolak.'],
                                ['step' => '04', 'title' => 'Wawancara & Microteaching', 'body' => 'Kandidat yang lolos seleksi berkas dijadwalkan wawancara dan microteaching oleh tim HRD.'],
                                ['step' => '05', 'title' => 'Keputusan Akhir', 'body' => 'Manajer menetapkan HIRE atau REJECT disertai catatan wawancara yang tersimpan pada berkas kandidat.'],
                            ] as $item)
                                <li class="flex gap-4">
                                    <span class="font-mono text-lg font-extrabold text-blue-900">{{ $item['step'] }}</span>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ $item['title'] }}</p>
                                        <p class="mt-0.5 text-sm leading-relaxed text-slate-500">{{ $item['body'] }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </x-card>
                </div>

                <aside class="space-y-6">
                    <x-card>
                        <x-slot name="header">
                            <p class="eyebrow">Metadata Formasi</p>
                        </x-slot>
                        <dl class="divide-y divide-slate-100 text-sm">
                            @foreach ([
                                'Kode Formasi' => 'JR-'.str_pad($job->id, 4, '0', STR_PAD_LEFT),
                                'Departemen' => $job->department?->name ?? '—',
                                'Tipe Pekerjaan' => $job->job_type->label(),
                                'Lokasi' => $job->location,
                                'Rentang Gaji' => $job->salaryRange() ?? '—',
                                'Status' => $job->status->label(),
                                'Pelamar Masuk' => $job->applications_count.' orang',
                            ] as $label => $value)
                                <div class="flex items-center justify-between gap-4 py-2.5">
                                    <dt class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">{{ $label }}</dt>
                                    <dd class="text-right font-semibold text-slate-900">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </x-card>

                    <x-card class="border-t-4 border-t-blue-900">
                        <x-slot name="header">
                            <p class="eyebrow">Bobot Kata Kunci NLP</p>
                        </x-slot>

                        @if ($job->keywords->isEmpty())
                            <x-empty-state
                                title="Belum dikonfigurasi"
                                description="Bobot kata kunci untuk formasi ini belum diatur oleh Admin HRD."
                            />
                        @else
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                                    {{ $job->keywords->count() }} keyword
                                </span>
                                <x-badge variant="solid-blue">Total bobot {{ number_format($totalWeight, 2) }}</x-badge>
                            </div>

                            <ul class="space-y-3">
                                @foreach ($job->keywords as $keyword)
                                    <li>
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="truncate text-sm font-semibold text-slate-900">{{ $keyword->keyword }}</span>
                                            <span class="shrink-0 font-mono text-[11px] font-bold text-blue-900">{{ $keyword->weight }}</span>
                                        </div>
                                        <div class="mt-1.5 flex items-center gap-2">
                                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                                <div
                                                    class="h-full rounded-full {{ $keyword->category->value === 'hard_skill' ? 'bg-blue-900' : 'bg-amber-600' }}"
                                                    style="width: {{ min(100, ($keyword->weight / max($totalWeight, 1)) * 100) }}%"
                                                ></div>
                                            </div>
                                            <span class="shrink-0 font-mono text-[9px] tracking-wider text-slate-400 uppercase">
                                                {{ $keyword->category->label() }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="mt-6 rounded-lg bg-slate-50 p-3.5 font-mono text-[11px] leading-relaxed text-slate-600">
                                Skor = (Σ bobot cocok ÷ {{ number_format($totalWeight, 2) }}) × 100
                            </div>
                        @endif
                    </x-card>

                    <x-card class="bg-blue-900" padding="p-6">
                        <p class="font-mono text-[10px] font-semibold tracking-[0.2em] text-blue-300 uppercase">Langkah Berikutnya</p>
                        <p class="mt-3 text-sm leading-relaxed text-blue-100">
                            Lengkapi master data sekali di profil, lalu kirimkan lamaran ke semua formasi
                            yang Anda minati tanpa perlu mengisi ulang.
                        </p>
                        <div class="mt-5 flex flex-col gap-2.5">
                            @auth
                                <a href="{{ route('pelamar.jobs.index') }}" class="btn bg-white text-blue-900 hover:bg-blue-50">
                                    Kembali ke Lowongan
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="btn bg-white text-blue-900 hover:bg-blue-50">
                                    Buat Akun Pelamar
                                </a>
                                <a href="{{ route('login') }}" class="btn border border-blue-700 text-white hover:bg-blue-800">
                                    Masuk Portal
                                </a>
                            @endauth
                        </div>
                    </x-card>
                </aside>
            </div>
        </section>

        <x-footer />
    </div>
</x-layouts.guest>
