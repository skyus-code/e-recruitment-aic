<x-layouts.app title="Dashboard">
    <div class="space-y-6">
        <x-trace :items="['Pelamar', 'Dashboard']" flow="STATUS · SKOR NLP · JADWAL" />

        <x-section-header
            eyebrow="Portal Pelamar"
            title="Dashboard Pelamar"
            subtitle="Ringkasan status lamaran, skor kesesuaian hasil screening NLP, dan jadwal wawancara terdekat Anda."
        >
            <x-slot name="actions">
                <x-button href="{{ route('pelamar.jobs.index') }}" variant="secondary" size="sm">
                    Cari Lowongan
                </x-button>
                <x-button href="{{ route('pelamar.applications.index') }}" variant="primary" size="sm">
                    Lamaran Saya
                </x-button>
            </x-slot>
        </x-section-header>

        {{-- Sapaan & kelengkapan profil --}}
        <x-card class="border-t-4 border-t-blue-900">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <p class="font-mono text-[10px] font-semibold tracking-[0.15em] text-blue-900 uppercase">
                        Selamat datang kembali
                    </p>
                    <h2 class="mt-2 font-heading text-2xl leading-tight font-extrabold text-slate-900 sm:text-3xl">
                        {{ auth()->user()->name }}
                    </h2>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-slate-500">
                        @if ($applications->isEmpty())
                            Anda belum mengirim lamaran apa pun. Jelajahi lowongan yang tersedia dan kirim berkas Anda untuk memulai proses screening NLP.
                        @else
                            {{ $applications->count() }} lamaran tercatat di sistem. Pantau progres tiap formasi dan lengkapi berkas agar memperoleh skor kesesuaian yang akurat.
                        @endif
                    </p>
                </div>

                <div class="w-full shrink-0 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 lg:w-72">
                    <x-progress
                        :value="$completeness['percent']"
                        label="Kelengkapan Profil"
                    />

                    @if ($completeness['missing'] !== [])
                        <p class="mt-3 text-xs leading-relaxed text-slate-500">
                            Lengkapi {{ implode(', ', $completeness['missing']) }} agar data Anda terbaca pipeline screening.
                        </p>
                        <x-button href="{{ route('pelamar.profile.edit') }}" variant="ghost" size="sm" class="mt-3">
                            Lengkapi Sekarang
                        </x-button>
                    @else
                        <p class="mt-3 text-xs leading-relaxed text-emerald-700">
                            Profil Anda sudah lengkap. Semua data siap diproses pipeline screening.
                        </p>
                    @endif
                </div>
            </div>
        </x-card>

        {{-- Rekap singkat --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card
                label="Total Lamaran"
                :value="$summary['total']"
                accent="blue"
                meta="terkirim ke sistem"
            />
            <x-stat-card
                label="Sedang Berjalan"
                :value="$summary['aktif']"
                accent="amber"
                meta="applied · screening · lolos"
            />
            <x-stat-card
                label="Wawancara"
                :value="$summary['wawancara']"
                accent="slate"
                meta="menunggu sesi"
            />
            <x-stat-card
                label="Diterima"
                :value="$summary['diterima']"
                accent="emerald"
                meta="final decision: hire"
            />
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Kiri: lamaran aktif + riwayat --}}
            <div class="space-y-6 lg:col-span-2">
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <p class="eyebrow">Lamaran Aktif</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Progres Formasi Terakhir</h2>
                    </div>
                </div>

                @if ($activeApplication === null)
                    <x-empty-state
                        title="Belum ada lamaran aktif"
                        description="Pilih lowongan yang sesuai keahlian Anda, lalu kirim CV dan ijazah untuk diparse pipeline NLP."
                    >
                        <x-slot name="actions">
                            <x-button href="{{ route('pelamar.jobs.index') }}" variant="primary">
                                Lihat Lowongan Tersedia
                            </x-button>
                        </x-slot>
                    </x-empty-state>
                @else
                    <x-card class="border-t-4 border-t-emerald-600">
                        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="font-mono text-[10px] font-semibold tracking-widest text-blue-900 uppercase">
                                    {{ $activeApplication->job->department?->name ?? 'Departemen' }}
                                </p>
                                <h3 class="mt-1.5 font-heading text-xl leading-snug font-extrabold text-slate-900">
                                    {{ $activeApplication->job->title }}
                                </h3>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <x-status-badge :status="$activeApplication->status" />

                                    @if ($activeApplication->score !== null)
                                        <x-badge variant="solid-blue">
                                            Skor Kesesuaian {{ $activeApplication->score }}
                                        </x-badge>
                                    @else
                                        <x-badge variant="slate">Belum discoring</x-badge>
                                    @endif

                                    @if ($activeApplication->applied_at)
                                        <span class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">
                                            dikirim {{ $activeApplication->applied_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-wrap gap-2.5">
                                <x-button href="{{ route('pelamar.applications.show', $activeApplication) }}" variant="primary" size="sm">
                                    Detail Lamaran
                                </x-button>

                                @if ($activeApplication->score !== null)
                                    <x-button href="{{ route('pelamar.applications.analysis', $activeApplication) }}" variant="secondary" size="sm">
                                        Bedah CV
                                    </x-button>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 border-t border-dashed border-slate-200 pt-5">
                            <x-progress
                                :value="$activeApplication->status->step()"
                                :max="3"
                                label="Tahap Proses Rekrutmen"
                            />

                            <div class="mt-3 grid grid-cols-4 gap-2">
                                @foreach (['Dilamar', 'Screening', 'Interview', 'Keputusan'] as $index => $stageLabel)
                                    <div class="text-center">
                                        <span
                                            class="block font-mono text-[10px] font-semibold uppercase tracking-wide {{ $activeApplication->status->step() >= $index ? 'text-blue-900' : 'text-slate-400' }}"
                                        >
                                            {{ $stageLabel }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </x-card>
                @endif

                <div class="flex items-end justify-between gap-3">
                    <div>
                        <p class="eyebrow">Riwayat Lamaran</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Semua Formasi Yang Pernah Dilamar</h2>
                    </div>

                    @if ($applications->isNotEmpty())
                        <a
                            href="{{ route('pelamar.applications.index') }}"
                            class="shrink-0 font-mono text-[10px] font-semibold tracking-wider text-blue-900 uppercase transition-all duration-200 ease-in-out hover:text-blue-700 hover:underline"
                        >
                            Lihat semua ({{ $applications->count() }})
                        </a>
                    @endif
                </div>

                @if ($applications->isEmpty())
                    <x-empty-state
                        title="Riwayat masih kosong"
                        description="Setiap lamaran yang Anda kirim akan tercatat di sini beserta status dan skornya."
                    />
                @else
                    <x-card padding="p-0">
                        <ul class="divide-y divide-slate-100">
                            @foreach ($applications as $application)
                                <li>
                                    <a
                                        href="{{ route('pelamar.applications.show', $application) }}"
                                        class="flex flex-col gap-3 px-5 py-4 transition-all duration-200 ease-in-out hover:bg-slate-50/70 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                                    >
                                        <div class="min-w-0">
                                            <p class="truncate font-heading text-base font-bold text-slate-900">
                                                {{ $application->job->title }}
                                            </p>
                                            <p class="mt-0.5 font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                                                {{ $application->job->department?->name ?? 'Departemen' }}
                                                @if ($application->applied_at)
                                                    · {{ $application->applied_at->format('d M Y') }}
                                                @endif
                                            </p>
                                        </div>

                                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                                            @if ($application->score !== null)
                                                <x-badge variant="blue">{{ $application->score }}</x-badge>
                                            @endif
                                            <x-status-badge :status="$application->status" />
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </x-card>
                @endif
            </div>

            {{-- Kanan: jadwal & insight --}}
            <div class="space-y-6">
                <div>
                    <p class="eyebrow">Jadwal Terdekat</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Wawancara &amp; Microteaching</h2>
                </div>

                @if ($upcomingInterview === null)
                    <x-empty-state
                        title="Belum ada jadwal"
                        description="Jadwal akan muncul di sini setelah tim HRD menetapkan tahap wawancara untuk Anda."
                        class="py-10"
                    />
                @else
                    <x-card class="border-t-4 border-t-amber-600">
                        <div class="flex items-start gap-4">
                            <div class="flex shrink-0 flex-col items-center rounded-xl border border-amber-100 bg-amber-50 px-3.5 py-3 text-center">
                                <span class="font-mono text-[10px] font-semibold tracking-widest text-amber-700 uppercase">
                                    {{ $upcomingInterview->interview_date->translatedFormat('M') }}
                                </span>
                                <span class="mt-1 font-heading text-3xl leading-none font-extrabold text-amber-700">
                                    {{ $upcomingInterview->interview_date->format('d') }}
                                </span>
                                <span class="mt-1 font-mono text-[10px] font-semibold tracking-widest text-amber-700 uppercase">
                                    {{ $upcomingInterview->interview_date->translatedFormat('D') }}
                                </span>
                            </div>

                            <div class="min-w-0">
                                <p class="font-mono text-[10px] font-semibold tracking-widest text-blue-900 uppercase">
                                    {{ $upcomingInterview->application?->job?->title ?? 'Wawancara' }}
                                </p>
                                <p class="mt-1.5 text-sm font-semibold text-slate-900">
                                    Pukul {{ \Illuminate\Support\Str::of((string) $upcomingInterview->interview_time)->substr(0, 5) }} WIB
                                </p>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500">
                                    Narasumber: {{ $upcomingInterview->interviewer_name ?? 'Belum ditentukan' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2.5">
                            @if ($upcomingInterview->interview_type === 'online' && $upcomingInterview->interview_location)
                                <x-button href="{{ $upcomingInterview->interview_location }}" variant="primary" size="sm">
                                    Buka Tautan Sesi
                                </x-button>
                            @elseif ($upcomingInterview->interview_location)
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700">
                                    Lokasi: {{ $upcomingInterview->interview_location }}
                                </span>
                            @endif

                            <x-button href="{{ route('pelamar.interviews.index') }}" variant="secondary" size="sm">
                                Semua Jadwal
                            </x-button>
                        </div>
                    </x-card>
                @endif

                <div>
                    <p class="eyebrow">Panduan Singkat</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Langkah-langkah Melamar Pekerjaan</h2>
                </div>

                <x-card class="bg-blue-900 text-white">
                    <ol class="space-y-4">
                        @foreach ([
                            ['label' => '1. Isi data diri di Profil & Master Data', 'hint' => 'Lengkapi identitas, pendidikan, pengalaman, dan keahlian agar profilmu semakin lengkap.'],
                            ['label' => '2. Kirim Berkas', 'hint' => 'Unggah berkas lamaran (CV dan ijazah) sesuai kebutuhan untuk formasi yang kamu pilih.'],
                            ['label' => '3. Tunggu hasil seleksi', 'hint' => 'Kami akan memproses lamaranmu dan menilai kesesuaian dengan formasi yang dilamar.'],
                            ['label' => '4. Hasil seleksi akan dikirim lewat Email', 'hint' => 'Pantau email terdaftar untuk informasi hasil seleksi dan tahapan selanjutnya.'],
                        ] as $step)
                            <li class="flex gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-white">{{ $step['label'] }}</p>
                                    <p class="mt-0.5 text-xs leading-relaxed text-blue-200">{{ $step['hint'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    <div class="mt-5 border-t border-blue-800 pt-4">
                        <x-button href="{{ route('pelamar.jobs.index') }}" variant="secondary" size="sm">
                            Mulai Lamar Formasi
                        </x-button>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
