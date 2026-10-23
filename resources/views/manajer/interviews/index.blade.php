<x-layouts.app title="Jadwal & Penilaian">
    <div class="space-y-6">
        <x-section-header
            eyebrow="MODULE 02 · SCHED"
            title="Jadwal Wawancara & Lembar Penilaian"
            subtitle="Daftar sesi wawancara per tanggal, lengkap dengan skor NLP, pewawancara, dan akses langsung ke lembar penilaian."
        >
            <x-slot name="actions">
                <x-button href="{{ route('manajer.dashboard') }}" variant="secondary">Portal Keputusan</x-button>
                <x-button href="{{ route('manajer.dossier.index') }}" variant="ghost">Review Dossier</x-button>
            </x-slot>
        </x-section-header>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-stat-card
                label="Terjadwal"
                :value="$stats['terjadwal']"
                accent="blue"
                meta="total sesi wawancara"
            />
            <x-stat-card
                label="Hari Ini"
                :value="$stats['hari_ini']"
                accent="amber"
                meta="sesi berjalan hari ini"
            />
            <x-stat-card
                label="Selesai"
                :value="$stats['selesai']"
                accent="emerald"
                meta="kandidat sudah diputuskan"
            />
        </div>

        @if ($schedules->isEmpty())
            <x-empty-state
                title="Belum ada jadwal wawancara"
                description="Admin akan menjadwalkan sesi wawancara setelah kandidat dinyatakan lolos seleksi. Silakan periksa kembali nanti."
            >
                <x-slot name="actions">
                    <x-button href="{{ route('manajer.dashboard') }}" variant="secondary">Kembali ke Dashboard</x-button>
                </x-slot>
            </x-empty-state>
        @else
            <div class="space-y-8">
                @foreach ($schedules as $date => $group)
                    <section class="space-y-4">
                        <div class="flex items-center gap-3">
                            <p class="eyebrow shrink-0">{{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('l, d F Y') }}</p>
                            <span class="h-px flex-1 bg-slate-200"></span>
                            <span class="shrink-0 font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                                {{ $group->count() }} sesi
                            </span>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            @foreach ($group as $schedule)
                                <x-card padding="p-0" class="card-hover flex h-full flex-col overflow-hidden">
                                    <div class="flex flex-1 gap-4 p-5">
                                        <div class="flex w-14 shrink-0 flex-col items-center justify-center self-start rounded-lg bg-blue-900 py-3 text-white">
                                            <span class="font-heading text-2xl leading-none font-extrabold">
                                                {{ $schedule->interview_date->format('d') }}
                                            </span>
                                            <span class="mt-1 font-mono text-[10px] font-semibold tracking-widest text-blue-200 uppercase">
                                                {{ $schedule->interview_date->format('M') }}
                                            </span>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="truncate font-heading text-base font-bold text-slate-900">
                                                    {{ $schedule->application->user->name }}
                                                </h3>
                                                <x-status-badge :status="$schedule->application->status" />
                                            </div>

                                            <p class="mt-1 text-sm text-slate-500">
                                                {{ $schedule->application->job->title }}
                                                · {{ $schedule->application->job->department?->name ?? '—' }}
                                            </p>

                                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                                <x-badge variant="solid-blue">Skor NLP {{ $schedule->application->score ?? '—' }}</x-badge>
                                                <x-badge variant="slate">Jam {{ $schedule->interview_time }}</x-badge>
                                            </div>

                                            <dl class="mt-4 space-y-2 text-xs text-slate-500">
                                                <div class="flex items-start gap-2">
                                                    <dt class="w-24 shrink-0 font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                                                        Pewawancara
                                                    </dt>
                                                    <dd class="min-w-0 font-medium text-slate-700">
                                                        {{ $schedule->interviewer_name ?? '—' }}
                                                    </dd>
                                                </div>

                                                <div class="flex items-start gap-2">
                                                    <dt class="w-24 shrink-0 font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                                                        Telepon
                                                    </dt>
                                                    <dd class="min-w-0 font-medium text-slate-700">
                                                        {{ $schedule->application->user->phone ?? '—' }}
                                                    </dd>
                                                </div>

                                                <div class="flex items-start gap-2">
                                                    <dt class="w-24 shrink-0 font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                                                        Lokasi
                                                    </dt>
                                                    <dd class="min-w-0">
                                                        @if ($schedule->interview_type === 'online')
                                                            <a
                                                                href="{{ $schedule->interview_location }}"
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                class="inline-flex items-center gap-1.5 break-all font-semibold text-blue-900 transition-all duration-200 ease-in-out hover:text-blue-700 hover:underline"
                                                            >
                                                                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                                </svg>
                                                                {{ \Illuminate\Support\Str::limit($schedule->interview_location, 42) }}
                                                            </a>
                                                        @else
                                                            <span class="inline-flex items-center gap-1.5 break-all font-semibold text-slate-700">
                                                                {{ $schedule->interview_location }}
                                                            </span>
                                                        @endif
                                                    </dd>
                                                </div>
                                            </dl>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50 px-5 py-4">
                                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                                            SESI_{{ \Illuminate\Support\Str::padLeft((string) $schedule->id, 4, '0') }}
                                        </span>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <x-button href="{{ route('manajer.interviews.show', $schedule->application) }}" size="sm">
                                                Buka Lembar Penilaian
                                            </x-button>
                                            <x-button href="{{ route('files.cv', $schedule->application) }}" variant="secondary" size="sm">
                                                Unduh CV
                                            </x-button>
                                        </div>
                                    </div>
                                </x-card>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
