<x-layouts.guest title="Lowongan Kerja" :shell="true">
    <div class="min-h-screen bg-slate-100">
        <x-navbar />

        <section class="border-b border-slate-200 bg-blue-900">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <p class="font-mono text-[11px] font-semibold tracking-[0.25em] text-blue-300 uppercase">Direktori Formasi</p>
                <h1 class="mt-3 font-heading text-3xl font-extrabold text-white sm:text-4xl">
                    Lowongan Kerja Tersedia
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-blue-100/90 sm:text-base">
                    Pilih formasi, pelajari bobot kata kunci NLP-nya, lalu kirimkan CV terbaik Anda.
                    Setiap lamaran otomatis dinilai dan hasilnya transparan.
                </p>

                <form method="GET" action="{{ route('jobs.index') }}" class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <label for="q" class="mb-1.5 block font-mono text-[10px] font-semibold tracking-widest text-blue-300 uppercase">
                            Cari Formasi
                        </label>
                        <input
                            type="search"
                            id="q"
                            name="q"
                            value="{{ $keyword }}"
                            placeholder="Guru, Administrasi, DKM, lokasi…"
                            class="w-full rounded-lg border border-blue-700 bg-blue-800 px-4 py-3 text-sm text-white placeholder-blue-300 outline-none transition-all duration-200 ease-in-out focus:border-white focus:ring-2 focus:ring-blue-400"
                        >
                    </div>
                    <button type="submit" class="btn btn-lg bg-white text-blue-900 hover:bg-blue-50">
                        Cari
                    </button>
                </form>
            </div>
        </section>

        <section class="py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-4">
                    <p class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">
                        {{ $jobs->total() }} formasi ditemukan
                    </p>
                    @if ($keyword !== '')
                        <a href="{{ route('jobs.index') }}" class="text-sm font-semibold text-blue-900 transition-colors duration-200 hover:text-blue-700">
                            Reset pencarian
                        </a>
                    @endif
                </div>

                @if ($jobs->isEmpty())
                    <x-empty-state
                        title="Tidak ada formasi yang cocok"
                        description="Coba kata kunci lain, atau kembali untuk melihat seluruh lowongan yang tersedia."
                    >
                        <x-slot name="actions">
                            <a href="{{ route('jobs.index') }}" class="btn btn-secondary">Tampilkan Semua</a>
                        </x-slot>
                    </x-empty-state>
                @else
                    <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($jobs as $job)
                            <x-card padding="p-0" class="card-hover flex h-full flex-col overflow-hidden">
                                <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="font-mono text-[10px] font-semibold tracking-widest text-blue-900 uppercase">
                                            {{ $job->department?->name ?? 'Departemen' }}
                                        </span>
                                        <x-badge variant="blue">{{ $job->job_type->label() }}</x-badge>
                                    </div>
                                    <h2 class="mt-2.5 font-heading text-lg leading-snug font-bold text-slate-900">
                                        {{ $job->title }}
                                    </h2>
                                </div>

                                <div class="flex flex-1 flex-col p-6">
                                    <p class="line-clamp-3 text-sm leading-relaxed text-slate-500">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($job->description), 160) }}
                                    </p>

                                    <dl class="mt-5 space-y-2 text-xs text-slate-500">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                            <span>{{ $job->location }}</span>
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

                    <div class="mt-10">
                        {{ $jobs->links() }}
                    </div>
                @endif
            </div>
        </section>

        <x-footer />
    </div>
</x-layouts.guest>
