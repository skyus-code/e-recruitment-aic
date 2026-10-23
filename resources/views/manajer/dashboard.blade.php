<x-layouts.app title="Keputusan Final">
    <div class="space-y-6">
        <x-trace :items="['Manajer', 'Keputusan Final']" flow="QUEUE: {{ $stats['menunggu'] }} MENUNGGU" />

        <x-section-header
            eyebrow="MODULE 01 · DECISION"
            title="Portal Keputusan Rekrutmen"
            subtitle="Tinjau hasil interview, beri catatan wawancara, lalu tetapkan HIRE atau REJECT."
        >
            <x-slot name="actions">
                <x-button href="{{ route('manajer.interviews.index') }}" variant="secondary">Jadwal &amp; Penilaian</x-button>
                <x-button href="{{ route('manajer.dossier.index') }}">Review Dossier</x-button>
            </x-slot>
        </x-section-header>

        {{-- Rekap singkat --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card
                label="Menunggu Keputusan"
                :value="$stats['menunggu']"
                accent="amber"
                meta="kandidat berstatus interview"
            />
            <x-stat-card
                label="Diterima"
                :value="$stats['hired']"
                accent="emerald"
                meta="final decision: hire"
            />
            <x-stat-card
                label="Ditolak"
                :value="$stats['rejected']"
                accent="red"
                meta="final decision: reject"
            />
            <x-stat-card
                label="Total Lamaran"
                :value="$stats['total']"
                accent="blue"
                meta="RECRUITMENT_NODE: ACTIVE"
            />
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Kiri: antrean keputusan --}}
            <div class="space-y-4 lg:col-span-2">
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <p class="eyebrow">Antrean Keputusan</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Kandidat Menunggu Keputusan</h2>
                    </div>
                    <span class="shrink-0 font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                        {{ $awaiting->count() }} antrian
                    </span>
                </div>

                @if ($awaiting->isEmpty())
                    <x-empty-state
                        title="Tidak ada kandidat menunggu"
                        description="Semua kandidat yang diwawancarai sudah ditentukan keputusannya."
                    />
                @else
                    <x-card padding="p-0">
                        <ul class="divide-y divide-slate-100">
                            @foreach ($awaiting as $application)
                                <li class="flex flex-col gap-4 px-5 py-5 transition-all duration-200 ease-in-out hover:bg-slate-50/70 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-heading text-base font-bold text-slate-900">
                                                {{ $application->user->name }}
                                            </p>
                                            <x-badge variant="amber">Skor NLP {{ $application->score }}</x-badge>
                                        </div>

                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $application->job->title }}
                                            <span class="text-slate-300">·</span>
                                            {{ $application->job->department?->name ?? '—' }}
                                        </p>

                                        <p class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 font-mono text-[11px] tracking-wider text-slate-400 uppercase">
                                            @if ($application->interviewSchedule)
                                                <span>
                                                    Interview: {{ $application->interviewSchedule->interview_date->translatedFormat('d M Y') }} · {{ $application->interviewSchedule->interview_time }}
                                                </span>
                                            @else
                                                <span class="text-amber-600">Interview: belum dijadwalkan</span>
                                            @endif
                                            @if ($application->user->phone)
                                                <span>{{ $application->user->phone }}</span>
                                            @endif
                                        </p>
                                    </div>

                                    <x-button
                                        href="{{ route('manajer.interviews.show', $application) }}"
                                        size="sm"
                                        class="shrink-0"
                                    >
                                        Buka Lembar Keputusan
                                    </x-button>
                                </li>
                            @endforeach
                        </ul>
                    </x-card>
                @endif
            </div>

            {{-- Kanan: jadwal & keputusan terakhir --}}
            <div class="space-y-6">
                <div class="space-y-4">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow">Agenda</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Jadwal Terdekat</h2>
                        </div>
                        <span class="shrink-0 font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            {{ $upcoming->count() }} sesi
                        </span>
                    </div>

                    @if ($upcoming->isEmpty())
                        <x-empty-state
                            title="Belum ada jadwal"
                            description="Jadwal wawancara untuk kandidat menunggu keputusan belum ditetapkan."
                        />
                    @else
                        <x-card padding="p-0">
                            <ul class="divide-y divide-slate-100">
                                @foreach ($upcoming as $schedule)
                                    <li class="flex items-start gap-3 px-5 py-4 transition-all duration-200 ease-in-out hover:bg-slate-50/70">
                                        <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-lg bg-blue-100 leading-none">
                                            <span class="font-heading text-base font-extrabold text-blue-900">
                                                {{ $schedule->interview_date->format('d') }}
                                            </span>
                                            <span class="mt-1 font-mono text-[9px] font-semibold tracking-wider text-blue-900/70 uppercase">
                                                {{ $schedule->interview_date->format('M') }}
                                            </span>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-bold text-slate-900">
                                                {{ $schedule->application->user->name }}
                                            </p>
                                            <p class="truncate text-xs text-slate-500">
                                                {{ $schedule->application->job->title }}
                                                · {{ $schedule->application->job->department?->name ?? '—' }}
                                            </p>
                                            <p class="mt-1.5 font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                                                {{ $schedule->interview_date->translatedFormat('d M Y') }} · {{ $schedule->interview_time }}
                                            </p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </x-card>
                    @endif
                </div>

                <div class="space-y-4">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow">Log</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Keputusan Terakhir</h2>
                        </div>
                        <span class="shrink-0 font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            {{ $recentDecisions->count() }} entri
                        </span>
                    </div>

                    @if ($recentDecisions->isEmpty())
                        <x-empty-state
                            title="Belum ada keputusan"
                            description="Belum ada kandidat yang ditetapkan sebagai DITERIMA atau DITOLAK."
                        />
                    @else
                        <x-card padding="p-0">
                            <ul class="divide-y divide-slate-100">
                                @foreach ($recentDecisions as $decision)
                                    <li class="flex items-center justify-between gap-3 px-5 py-4 transition-all duration-200 ease-in-out hover:bg-slate-50/70">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-bold text-slate-900">
                                                {{ $decision->user->name }}
                                            </p>
                                            <p class="truncate text-xs text-slate-500">
                                                {{ $decision->job->title }}
                                                · {{ $decision->job->department?->name ?? '—' }}
                                            </p>
                                            <p class="mt-1 font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                                                {{ $decision->updated_at->translatedFormat('d M Y H:i') }}
                                            </p>
                                        </div>

                                        <div class="flex shrink-0 flex-col items-end gap-1.5">
                                            <x-status-badge :status="$decision->status" />
                                            <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400">
                                                SKOR {{ $decision->score }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </x-card>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
