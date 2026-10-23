<x-layouts.app title="Lamaran Saya">
    <div class="space-y-6">
        <x-trace :items="['Pelamar', 'Lamaran Saya']" flow="TERKIRIM → SCREENING → INTERVIEW → KEPUTUSAN" />

        <x-section-header
            eyebrow="Portal Pelamar"
            title="Lamaran Saya"
            subtitle="Pantau status setiap lamaran, lihat skor kesesuaian hasil screening NLP, dan buka bedah CV untuk rincian analisisnya."
        >
            <x-slot name="actions">
                <x-button href="{{ route('pelamar.jobs.index') }}" variant="primary" size="sm">Lamar Formasi Baru</x-button>
            </x-slot>
        </x-section-header>

        @if ($applications->isEmpty())
            <x-empty-state
                title="Belum ada lamaran terkirim"
                description="Anda belum mengirim lamaran ke formasi mana pun. Pilih lowongan yang sesuai dengan keahlian Anda untuk memulai."
            >
                <x-slot name="actions">
                    <x-button href="{{ route('pelamar.jobs.index') }}" variant="primary">Lihat Lowongan Tersedia</x-button>
                </x-slot>
            </x-empty-state>
        @else
            <p class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">
                {{ $applications->total() }} lamaran tercatat
            </p>

            <div class="grid gap-5 lg:grid-cols-2">
                @foreach ($applications as $application)
                    <x-card class="card-hover flex h-full flex-col">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-mono text-[10px] font-semibold tracking-widest text-blue-900 uppercase">
                                    {{ $application->job->department?->name ?? 'Departemen' }}
                                </p>
                                <h3 class="mt-2 font-heading text-lg leading-snug font-bold text-slate-900">
                                    {{ $application->job->title }}
                                </h3>
                            </div>

                            <x-status-badge :status="$application->status" />
                        </div>

                        @php
                            $scoreDetails = $application->scoreDetails;
                            $matchedCount = $scoreDetails->where('is_matched', true)->count();
                            $totalKeywords = $application->job->keywords->count();
                            $unmatchedCount = max(0, $totalKeywords - $matchedCount);
                            $statusValue = $application->status;
                            $assessment = $application->assessment;
                        @endphp

                        <dl class="mt-5 space-y-2.5 text-xs text-slate-500">
                            <div class="flex items-center justify-between gap-3 border-b border-dashed border-slate-100 pb-2.5">
                                <dt class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Dilamar Pada</dt>
                                <dd class="font-semibold text-slate-700">{{ $application->applied_at?->translatedFormat('d F Y') ?? '—' }}</dd>
                            </div>

                            <div class="flex items-center justify-between gap-3 border-b border-dashed border-slate-100 pb-2.5">
                                <dt class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Skor Kesesuaian</dt>
                                <dd>
                                    @if ($application->score !== null)
                                        <x-badge variant="solid-blue">Skor {{ $application->score }}</x-badge>
                                    @else
                                        <span class="text-slate-400">Belum discoring</span>
                                    @endif
                                </dd>
                            </div>

                            <div class="flex items-center justify-between gap-3 border-b border-dashed border-slate-100 pb-2.5">
                                <dt class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Keyword Formasi</dt>
                                <dd class="font-semibold text-slate-700">{{ $totalKeywords }} kata kunci</dd>
                            </div>

                            <div class="flex items-center justify-between gap-3 border-b border-dashed border-slate-100 pb-2.5">
                                <dt class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Match / Miss</dt>
                                <dd class="font-semibold text-slate-700">
                                    @if ($totalKeywords > 0)
                                        {{ $matchedCount }} / {{ $unmatchedCount }}
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>

                            <div class="flex items-center justify-between gap-3 border-b border-dashed border-slate-100 pb-2.5">
                                <dt class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Teks Parsing</dt>
                                <dd class="font-semibold text-slate-700">
                                    @if ($application->parsedData)
                                        @if ($application->parsedData->total_words > 0)
                                            {{ $application->parsedData->total_words }} kata · {{ $application->parsedData->parsing_status }}
                                        @else
                                            {{ $application->parsedData->parsing_status }} · {{ $application->parsedData->total_words }} kata
                                        @endif
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>

                            @if ($statusValue === \App\Enums\ApplicationStatusEnum::Hired)
                                <div class="flex items-center justify-between gap-3 border-b border-dashed border-slate-100 pb-2.5">
                                    <dt class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Hasil Akhir</dt>
                                    <dd><x-badge variant="solid-emerald">Diterima (Hired)</x-badge></dd>
                                </div>
                                @if ($assessment?->final_score !== null)
                                    <div class="flex items-center justify-between gap-3 border-b border-dashed border-slate-100 pb-2.5">
                                        <dt class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Nilai Wawancara</dt>
                                        <dd class="font-semibold text-slate-700">{{ number_format((float) $assessment->final_score, 2) }}</dd>
                                    </div>
                                @endif
                                @if ($assessment?->decision)
                                    <div class="flex items-center justify-between gap-3 border-b border-dashed border-slate-100 pb-2.5">
                                        <dt class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Keputusan Penilaian</dt>
                                        <dd class="font-semibold text-slate-700">{{ ucfirst($assessment->decision) }}</dd>
                                    </div>
                                @endif
                            @endif

                            @if ($statusValue === \App\Enums\ApplicationStatusEnum::Rejected)
                                <div class="flex items-center justify-between gap-3 border-b border-dashed border-slate-100 pb-2.5">
                                    <dt class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Hasil Akhir</dt>
                                    <dd><x-badge variant="danger">Ditolak</x-badge></dd>
                                </div>
                                @if ($assessment?->final_score !== null)
                                    <div class="flex items-center justify-between gap-3 border-b border-dashed border-slate-100 pb-2.5">
                                        <dt class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Nilai Wawancara</dt>
                                        <dd class="font-semibold text-slate-700">{{ number_format((float) $assessment->final_score, 2) }}</dd>
                                    </div>
                                @endif
                                @if ($assessment?->decision)
                                    <div class="flex items-center justify-between gap-3">
                                        <dt class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Keputusan Penilaian</dt>
                                        <dd class="font-semibold text-slate-700">{{ ucfirst($assessment->decision) }}</dd>
                                    </div>
                                @endif
                            @endif
                        </dl>

                        <div class="mt-auto flex flex-wrap items-center gap-2.5 border-t border-dashed border-slate-200 pt-5">
                            <x-button href="{{ route('pelamar.applications.show', $application) }}" variant="primary" size="sm">
                                Detail
                            </x-button>

                            @if ($application->score !== null)
                                <x-button href="{{ route('pelamar.applications.analysis', $application) }}" variant="secondary" size="sm">
                                    Bedah CV
                                </x-button>
                            @endif

                            @if ($application->interviewSchedule)
                                <x-button href="{{ route('pelamar.interviews.index') }}" variant="warning" size="sm">
                                    Lihat Jadwal
                                </x-button>
                            @endif
                        </div>
                    </x-card>
                @endforeach
            </div>

            <div>
                {{ $applications->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
