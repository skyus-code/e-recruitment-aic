<x-layouts.app title="Lowongan Kerja">
    <div class="space-y-6">
        <x-trace :items="['Pelamar', 'Lowongan Kerja']" flow="KATALOG → MASTER DATA → LAMAR" />

        <x-section-header
            eyebrow="Portal Pelamar"
            title="Lowongan Kerja Tersedia"
            subtitle="Pilih formasi yang sesuai dengan keahlian Anda, lalu kirimkan lamaran lengkap dengan CV dan ijazah. Satu akun dipakai untuk seluruh formasi."
        >
            <x-slot name="actions">
                <span class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">
                    {{ $jobs->total() }} formasi terbuka
                </span>
            </x-slot>
        </x-section-header>

        {{-- Banner verifikasi email untuk pelamar yang belum terverifikasi --}}
        @if (! $user->hasVerifiedEmail())
            <x-alert variant="warning" title="Verifikasi email untuk dapat melamar">
                Lamaran hanya dapat dikirim setelah email
                <strong>{{ $user->email }}</strong> diverifikasi. Mohon lengkapi langkah
                verifikasi terlebih dahulu, lamaran yang sudah Anda kirim tidak akan berubah.
                <x-slot name="actions">
                    <x-button href="{{ route('password.edit') }}" size="sm" variant="primary">
                        Verifikasi Email Sekarang
                    </x-button>
                </x-slot>
            </x-alert>
        @endif

        {{-- Pencarian --}}
        <x-card>
            <form method="GET" action="{{ route('pelamar.jobs.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <x-input
                        type="search"
                        name="q"
                        label="Cari Lowongan"
                        placeholder="cth: Guru Tahfidz, Yogyakarta"
                        :value="$keyword"
                    />
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <x-button type="submit" variant="primary">Cari</x-button>

                    @if ($keyword !== '')
                        <x-button href="{{ route('pelamar.jobs.index') }}" variant="ghost">Reset</x-button>
                    @endif
                </div>
            </form>
        </x-card>

        @if ($jobs->isEmpty())
            <x-empty-state
                title="Tidak ada lowongan yang cocok"
                description="Belum ada formasi terbuka yang sesuai dengan pencarian Anda. Coba ubah kata kunci atau lihat seluruh lowongan yang tersedia."
            >
                <x-slot name="actions">
                    <x-button href="{{ route('pelamar.jobs.index') }}" variant="secondary">Tampilkan Semua</x-button>
                    <x-button href="{{ route('pelamar.applications.index') }}" variant="primary">Lamaran Saya</x-button>
                </x-slot>
            </x-empty-state>
        @else
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
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
                                    <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                    <span>{{ $job->location ?? 'Seluruh Indonesia' }}</span>
                                </div>

                                @if ($job->salaryRange())
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33"/></svg>
                                        <span>{{ $job->salaryRange() }}</span>
                                    </div>
                                @endif

                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                    <span>Dibuka {{ $job->created_at->diffForHumans() }}</span>
                                </div>
                            </dl>

                            <div class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-dashed border-slate-200 pt-5">
                                <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                                    {{ $job->applications_count }} pelamar · {{ $job->keywords_count }} keyword
                                </span>

                                @if ($appliedJobIds->has($job->id))
                                    <x-badge variant="emerald">✔ Sudah Dilamar</x-badge>
                                @elseif (! $user->hasVerifiedEmail())
                                    <a href="{{ route('password.edit') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 ring-1 ring-inset ring-amber-200" title="Verifikasi email terlebih dahulu untuk melamar.">
                                        Verifikasi Email Sekarang
                                    </a>
                                @else
                                    <x-button href="{{ route('pelamar.apply.create', $job) }}" size="sm" variant="primary">
                                        Lamar Sekarang
                                    </x-button>
                                @endif
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>

            <div class="mt-2">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
