<x-layouts.app title="Detail Lamaran">
    @php
        $current = $application->status->step();
        $isRejected = $application->status === \App\Enums\ApplicationStatusEnum::Rejected;

        $timelineSteps = [
            ['label' => 'Dilamar', 'hint' => 'Lamaran terkirim ke sistem'],
            ['label' => 'Screening', 'hint' => 'Berkas diparse & dinilai NLP'],
            ['label' => 'Interview', 'hint' => 'Wawancara & microteaching'],
            ['label' => 'Diterima', 'hint' => 'Keputusan final yayasan'],
        ];
    @endphp

    <div class="space-y-6">
        <x-trace :items="['Pelamar', 'Lamaran', $application->job->title]" flow="STATUS · TIMELINE · BERKAS" />

        {{-- Kepala lamaran --}}
        <x-card class="border-t-4 border-t-blue-900">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <p class="font-mono text-[10px] font-semibold tracking-widest text-blue-900 uppercase">
                        {{ $application->job->department?->name ?? 'Departemen' }}
                    </p>
                    <h1 class="mt-2 font-heading text-2xl leading-tight font-extrabold text-slate-900 sm:text-3xl">
                        {{ $application->job->title }}
                    </h1>

                    <div class="mt-4 flex flex-wrap items-center gap-2.5">
                        <x-status-badge :status="$application->status" />

                        @if ($application->score !== null)
                            <x-badge variant="solid-blue">Skor Kesesuaian {{ $application->score }}</x-badge>
                        @else
                            <x-badge variant="slate">Belum discoring</x-badge>
                        @endif

                        <x-badge variant="slate">Dilamar {{ $application->applied_at?->translatedFormat('d F Y') ?? '—' }}</x-badge>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2.5">
                    @if ($application->score !== null)
                        <x-button href="{{ route('pelamar.applications.analysis', $application) }}" variant="primary">
                            Bedah CV
                        </x-button>
                    @endif
                    <x-button href="{{ route('pelamar.applications.index') }}" variant="secondary">
                        Kembali ke Daftar
                    </x-button>
                </div>
            </div>
        </x-card>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                {{-- Timeline proses --}}
                @if ($isRejected)
                    <x-alert variant="danger" title="Lamaran Ditolak">
                        Maaf, lamaran Anda untuk formasi {{ $application->job->title }} tidak lolos tahap ini.
                        Keputusan bersifat final untuk periode rekrutmen berjalan — Anda tetap dapat melamar
                        pada formasi lain yang dibuka.
                    </x-alert>
                @else
                    <x-card>
                        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                            <div>
                                <p class="eyebrow">Tahapan</p>
                                <h2 class="mt-1 text-lg font-bold text-slate-900">Status Proses Lamaran</h2>
                            </div>
                            <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">
                                TAHAP {{ $current + 1 }}/4
                            </span>
                        </div>

                        {{-- Horizontal (lg ke atas) --}}
                        <ol class="mt-6 hidden lg:flex">
                            @foreach ($timelineSteps as $index => $step)
                                <li @class([
                                    'relative flex-1',
                                    'pr-6' => ! $loop->last,
                                ])>
                                    @if (! $loop->last)
                                        <span @class([
                                            'absolute top-4 left-9 right-0 h-0.5',
                                            'bg-emerald-600' => $index < $current,
                                            'bg-slate-200' => $index >= $current,
                                        ])></span>
                                    @endif

                                    <span @class([
                                        'relative z-10 flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition-all duration-200 ease-in-out',
                                        'bg-emerald-600 text-white' => $index < $current,
                                        'bg-blue-900 text-white ring-4 ring-blue-100' => $index === $current,
                                        'bg-slate-100 text-slate-400 ring-1 ring-slate-200' => $index > $current,
                                    ])>
                                        @if ($index < $current)
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                                        @else
                                            {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                                        @endif
                                    </span>

                                    <p @class([
                                        'mt-3 text-sm font-bold',
                                        'text-blue-900' => $index === $current,
                                        'text-slate-900' => $index < $current,
                                        'text-slate-400' => $index > $current,
                                    ])>
                                        {{ $step['label'] }}
                                    </p>
                                    <p class="mt-0.5 pr-4 text-xs leading-relaxed text-slate-500">{{ $step['hint'] }}</p>
                                </li>
                            @endforeach
                        </ol>

                        {{-- Vertikal (mobile) --}}
                        <ol class="mt-6 space-y-5 lg:hidden">
                            @foreach ($timelineSteps as $index => $step)
                                <li @class([
                                    'relative flex gap-4',
                                    'pb-6' => ! $loop->last,
                                ])>
                                    @if (! $loop->last)
                                        <span @class([
                                            'absolute top-9 bottom-0 left-[15px] w-0.5',
                                            'bg-emerald-600' => $index < $current,
                                            'bg-slate-200' => $index >= $current,
                                        ])></span>
                                    @endif

                                    <span @class([
                                        'relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold transition-all duration-200 ease-in-out',
                                        'bg-emerald-600 text-white' => $index < $current,
                                        'bg-blue-900 text-white ring-4 ring-blue-100' => $index === $current,
                                        'bg-slate-100 text-slate-400 ring-1 ring-slate-200' => $index > $current,
                                    ])>
                                        @if ($index < $current)
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                                        @else
                                            {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                                        @endif
                                    </span>

                                    <div class="min-w-0 pt-1">
                                        <p @class([
                                            'text-sm font-bold',
                                            'text-blue-900' => $index === $current,
                                            'text-slate-900' => $index < $current,
                                            'text-slate-400' => $index > $current,
                                        ])>
                                            {{ $step['label'] }}
                                        </p>
                                        <p class="text-xs leading-relaxed text-slate-500">{{ $step['hint'] }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </x-card>
                @endif

                {{-- Berkas terlampir --}}
                <x-card>
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Dokumen</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Berkas Terlampir</h2>
                        </div>
                        <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">READ ONLY</span>
                    </div>

                    @php
                        $scoreDetails = $application->scoreDetails;
                        $jobKeywords = $application->job->keywords;
                        $matchedCount = $scoreDetails->where('is_matched', true)->count();
                        $totalKeywords = $jobKeywords->count();
                        $unmatchedCount = max(0, $totalKeywords - $matchedCount);
                        $assessmentDetail = $application->assessment;
                        $statusFinal = $application->status;
                    @endphp

                    <div class="mt-5 grid gap-3 rounded-lg border border-dashed border-slate-200 bg-slate-50/60 p-4 text-xs text-slate-600 sm:grid-cols-2">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Total Keyword</span>
                            <span class="font-semibold text-slate-800">{{ $totalKeywords }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Match / Miss</span>
                            <span class="font-semibold text-slate-800">
                                @if ($totalKeywords > 0)
                                    {{ $matchedCount }} / {{ $unmatchedCount }}
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Teks Parsing</span>
                            <span class="font-semibold text-slate-800">
                                @if ($application->parsedData)
                                    {{ $application->parsedData->parsing_status }} · {{ $application->parsedData->total_words ?? 0 }} kata
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                        @if ($statusFinal === \App\Enums\ApplicationStatusEnum::Hired)
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Hasil</span>
                                <span class="font-semibold text-emerald-700">Diterima (Hired)</span>
                            </div>
                            @if ($assessmentDetail?->final_score !== null)
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Nilai Wawancara</span>
                                    <span class="font-semibold text-slate-800">{{ number_format((float) $assessmentDetail->final_score, 2) }}</span>
                                </div>
                            @endif
                            @if ($assessmentDetail?->decision)
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Keputusan Penilaian</span>
                                    <span class="font-semibold text-slate-800">{{ ucfirst($assessmentDetail->decision) }}</span>
                                </div>
                            @endif
                        @endif
                        @if ($statusFinal === \App\Enums\ApplicationStatusEnum::Rejected)
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Hasil</span>
                                <span class="font-semibold text-red-700">Ditolak</span>
                            </div>
                            @if ($assessmentDetail?->final_score !== null)
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Nilai Wawancara</span>
                                    <span class="font-semibold text-slate-800">{{ number_format((float) $assessmentDetail->final_score, 2) }}</span>
                                </div>
                            @endif
                            @if ($assessmentDetail?->decision)
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Keputusan Penilaian</span>
                                    <span class="font-semibold text-slate-800">{{ ucfirst($assessmentDetail->decision) }}</span>
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                            <div class="min-w-0">
                                <p class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">CV · PDF</p>
                                <p class="mt-1 truncate text-sm font-semibold text-slate-900">
                                    {{ $application->cvFile?->file_name ?? 'Tidak ada berkas' }}
                                </p>
                                @if ($application->cvFile)
                                    <p class="mt-0.5 font-mono text-[11px] text-slate-400">
                                        {{ number_format($application->cvFile->file_size / 1024, 0) }} KB
                                    </p>
                                @endif
                            </div>

                            @if ($application->cvFile)
                                <x-button href="{{ route('files.cv', $application) }}" variant="secondary" size="sm">Unduh</x-button>
                            @endif
                        </div>

                        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                            <div class="min-w-0">
                                <p class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">IJAZAH · PDF/GAMBAR</p>
                                <p class="mt-1 truncate text-sm font-semibold text-slate-900">
                                    {{ $application->degreeCertificationFile?->file_name ?? 'Belum ada' }}
                                </p>
                                @if ($application->degreeCertificationFile)
                                    <p class="mt-0.5 font-mono text-[11px] text-slate-400">
                                        {{ number_format($application->degreeCertificationFile->file_size / 1024, 0) }} KB
                                    </p>
                                @endif
                            </div>

                            @if ($application->degreeCertificationFile)
                                <x-button href="{{ route('files.ijazah', $application) }}" variant="secondary" size="sm">Unduh</x-button>
                            @endif
                        </div>
                    </div>
                </x-card>

                {{-- Jadwal wawancara --}}
                <x-card>
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Agenda</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Jadwal Wawancara</h2>
                        </div>
                        @if ($application->interviewSchedule)
                            <x-badge variant="amber">Terjadwal</x-badge>
                        @endif
                    </div>

                    @if ($application->interviewSchedule)
                        @php $schedule = $application->interviewSchedule; @endphp

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                                <p class="font-mono text-[10px] font-semibold tracking-widest text-amber-700 uppercase">Tanggal &amp; Waktu</p>
                                <p class="mt-1.5 text-sm font-bold text-amber-900">
                                    {{ $schedule->interview_date->translatedFormat('l, d F Y') }}
                                </p>
                                <p class="mt-0.5 font-mono text-xs font-semibold text-amber-800">
                                    {{ \Illuminate\Support\Str::of((string) $schedule->interview_time)->substr(0, 5) }} WIB
                                </p>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="font-mono text-[10px] font-semibold tracking-widest text-slate-500 uppercase">Nama Wawancara</p>
                                <p class="mt-1.5 text-sm font-bold text-slate-900">
                                    {{ $schedule->interviewer_name ?? 'Belum ditentukan' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl border border-slate-200 p-4">
                            <p class="font-mono text-[10px] font-semibold tracking-widest text-slate-500 uppercase">
                                Lokasi · {{ $schedule->interview_type === 'online' ? 'Online' : 'Offline' }}
                            </p>
                            @if ($schedule->interview_type === 'online')
                                <a
                                    href="{{ $schedule->interview_location }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="mt-1.5 inline-flex items-center gap-2 break-all text-sm font-semibold text-blue-900 transition-all duration-200 ease-in-out hover:text-blue-700 hover:underline"
                                >
                                    {{ $schedule->interview_location }}
                                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V16.5m-13.5-3L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                </a>
                            @else
                                <p class="mt-1.5 inline-flex items-center gap-2 break-all text-sm font-semibold text-slate-900">
                                    {{ $schedule->interview_location }}
                                </p>
                            @endif
                        </div>

                        @if ($schedule->notes)
                            <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50 p-4">
                                <p class="font-mono text-[10px] font-semibold tracking-widest text-blue-900 uppercase">Catatan HRD</p>
                                <div class="mt-1.5 text-sm leading-relaxed text-blue-900">
                                    {!! nl2br(e($schedule->notes)) !!}
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="mt-5">
                            <x-empty-state
                                title="Belum ada jadwal"
                                description="Tim HRD akan menetapkan jadwal wawancara setelah tahap screening selesai. Pastikan nomor WhatsApp Anda aktif."
                            />
                        </div>
                    @endif
                </x-card>
            </div>

            {{-- Kolom kanan: antrian proses --}}
            <div class="space-y-6">
                <x-card>
                    <div class="border-b border-slate-100 pb-4">
                        <p class="eyebrow">Pipeline</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Antrian Proses</h2>
                        <p class="mt-1 text-xs text-slate-500">Urutan status yang dilewati lamaran Anda.</p>
                    </div>

                    <ol class="mt-4 space-y-1.5">
                        @foreach (\App\Enums\ApplicationStatusEnum::cases() as $stage)
                            @php $isActive = $stage === $application->status; @endphp
                            <li @class([
                                'flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 font-mono text-[11px] font-semibold tracking-wider uppercase transition-all duration-200 ease-in-out',
                                'bg-blue-100 text-blue-900 ring-1 ring-blue-200' => $isActive,
                                'text-slate-400 hover:bg-slate-50' => ! $isActive,
                            ])>
                                <span class="flex min-w-0 items-center gap-2.5">
                                    <span @class([
                                        'h-1.5 w-1.5 shrink-0 rounded-full',
                                        'bg-blue-900' => $isActive,
                                        'bg-slate-300' => ! $isActive,
                                    ])></span>
                                    <span class="truncate">{{ $stage->label() }}</span>
                                </span>

                                <span @class([
                                    'shrink-0 text-[10px]',
                                    'text-blue-900' => $isActive,
                                    'text-slate-300' => ! $isActive,
                                ])>
                                    {{ $isActive ? 'AKTIF' : str_pad((string) ($loop->index + 1), 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </x-card>

                <x-card class="border-t-4 border-t-emerald-600">
                    <p class="eyebrow">Butuh Bantuan?</p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        Pastikan master data Anda lengkap sebelum mengirim lamaran berikutnya.
                        Skor NLP setiap lamaran dapat dilihat lengkap pada halaman Bedah CV.
                    </p>

                    <div class="mt-4 flex flex-col gap-2.5">
                        <x-button href="{{ route('pelamar.profile.edit') }}" variant="secondary" size="sm">
                            Perbarui Master Data
                        </x-button>
                        <x-button href="{{ route('pelamar.jobs.index') }}" variant="ghost" size="sm">
                            Lihat Lowongan Lain
                        </x-button>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
