<x-layouts.guest title="Beranda" :shell="true">
    <div class="min-h-screen bg-white">
        <x-navbar />

        {{-- Hero --}}
        <section class="relative overflow-hidden bg-blue-900">
            <div class="pointer-events-none absolute -top-32 -right-24 h-96 w-96 rounded-full bg-blue-800/70 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-40 -left-20 h-[26rem] w-[26rem] rounded-full bg-emerald-600/20 blur-3xl"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-24 lg:px-8">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full bg-blue-800 px-3 py-1.5 font-mono text-[11px] font-semibold tracking-widest text-blue-100 ring-1 ring-blue-700 uppercase">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Sistem Informasi Rekrutmen · Tahun {{ now()->year }}/{{ now()->year + 1 }}
                    </span>

                    <h1 class="mt-6 font-heading text-4xl leading-[1.1] font-extrabold text-white sm:text-5xl xl:text-6xl">
                        Rekrutmen staff &amp; tenaga kependidikan yang
                        <span class="text-blue-200">terukur, transparan, dan adil.</span>
                    </h1>

                    <p class="mt-6 max-w-2xl text-base leading-relaxed text-blue-100/90 sm:text-lg">
                        Lengkapi profilmu, kirimkan berkas lamaran, dan pantau hasil seleksinya
                        dalam satu portal. Setiap lamaran dinilai berdasarkan kesesuaian
                        dengan formasi yang kamu pilih — transparan dan bisa kamu lihat sendiri.
                    </p>

                    <div class="mt-9 flex flex-wrap items-center gap-3">
                        <a href="{{ route('jobs.index') }}" class="btn btn-lg bg-white text-blue-900 hover:bg-blue-50 hover:shadow-lg">
                            Lihat Lowongan
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.6L10.72 5.53a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.63-3.64H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                            </svg>
                        </a>

                        <a href="{{ route('register') }}" class="btn btn-lg border border-blue-700 text-white hover:bg-blue-800">
                            Daftar Sebagai Pelamar
                        </a>
                    </div>
                </div>

                <dl class="mt-14 grid grid-cols-2 gap-4 border-t border-blue-800 pt-8 sm:grid-cols-4">
                    @foreach ([
                        ['label' => 'Formasi Dibuka', 'value' => $stats['formasi']],
                        ['label' => 'Pelamar Terdaftar', 'value' => $stats['pelamar']],
                        ['label' => 'Total Lamaran', 'value' => $stats['lamaran']],
                        ['label' => 'Diterima Kerja', 'value' => $stats['diterima']],
                    ] as $stat)
                        <div>
                            <dt class="font-mono text-[10px] tracking-[0.18em] text-blue-300 uppercase">{{ $stat['label'] }}</dt>
                            <dd class="mt-1.5 font-heading text-3xl font-extrabold text-white sm:text-4xl">{{ $stat['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>

        {{-- Lowongan --}}
        <section class="py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col justify-between gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end">
                    <div>
                        <h2 class="mt-2 text-3xl font-extrabold text-slate-900 sm:text-4xl">Formasi yang sedang dibuka</h2>
                        <p class="mt-2 max-w-2xl text-sm text-slate-500">
                            Pilih posisi, lengkapi master data, unggah CV &amp; ijazah, lalu pantau statusnya.
                        </p>
                    </div>

                    <a href="{{ route('jobs.index') }}" class="btn btn-secondary shrink-0">
                        Semua Lowongan
                    </a>
                </div>

                @if ($jobs->isEmpty())
                    <x-empty-state
                        title="Belum ada formasi terbuka"
                        description="Silakan kembali beberapa waktu lagi atau daftar akun terlebih dahulu agar Anda menerima kabar saat lowongan dibuka."
                    >
                        <x-slot name="actions">
                            <a href="{{ route('register') }}" class="btn btn-primary">Daftar Pelamar</a>
                        </x-slot>
                    </x-empty-state>
                @else
                    <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($jobs as $job)
                            <x-card padding="p-0" class="card-hover flex h-full flex-col overflow-hidden">
                                <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="font-mono text-[10px] font-semibold tracking-widest text-blue-900 uppercase">
                                            {{ $job->department?->name ?? 'Departemen' }}
                                        </span>
                                        <x-badge variant="blue">{{ $job->job_type->label() }}</x-badge>
                                    </div>
                                    <h3 class="mt-2.5 font-heading text-lg leading-snug font-bold text-slate-900">
                                        {{ $job->title }}
                                    </h3>
                                </div>

                                <div class="flex flex-1 flex-col p-6">
                                    <p class="line-clamp-3 text-sm leading-relaxed text-slate-500">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($job->description), 150) }}
                                    </p>

                                    <dl class="mt-5 space-y-2 text-xs text-slate-500">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                            <span>{{ $job->location }}</span>
                                        </div>
                                        @if ($job->salaryRange())
                                            <div class="flex items-center gap-2">
                                                <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33"/></svg>
                                                <span>{{ $job->salaryRange() }}</span>
                                            </div>
                                        @endif
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                            <span>Dibuka {{ $job->created_at->diffForHumans() }}</span>
                                        </div>
                                    </dl>

                                    <div class="mt-auto flex items-center justify-between gap-3 border-t border-dashed border-slate-200 pt-5">
                                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                                            {{ $job->applications_count }} pelamar · {{ $job->keywords_count }} keyword
                                        </span>
                                        <a href="{{ route('jobs.show', $job) }}" class="btn btn-sm btn-primary">Lihat Detail</a>
                                    </div>
                                </div>
                            </x-card>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        {{-- CTA --}}
        <section class="bg-slate-100 py-20">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="card overflow-hidden border-t-4 border-t-blue-900">
                    <div class="grid gap-8 p-8 sm:p-10 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <p class="eyebrow">Mulai Sekarang</p>
                            <h2 class="mt-2 text-2xl font-extrabold text-slate-900 sm:text-3xl">
                                Siap bergabung dengan Ar-Rahman Islamic Center?
                            </h2>
                            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-500">
                                Buat akun pelamar, lengkapi master data sekali, dan gunakan berulang untuk
                                setiap formasi. Status lamaran, hasil seleksi, dan jadwal wawancara
                                dapat dipantau langsung dari dashboard Anda.
                            </p>
                        </div>

                        <div class="flex shrink-0 flex-col gap-3">
                            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Buat Akun Pelamar</a>
                            <a href="{{ route('login') }}" class="btn btn-secondary">Sudah punya akun</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <x-footer />
    </div>
</x-layouts.guest>
