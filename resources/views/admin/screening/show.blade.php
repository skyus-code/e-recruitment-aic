<x-layouts.app title="Detail Screening">
    @php
        $dp = $application->user->detailPerson;
        $matchedCount = $keywords->where('is_matched', true)->count();
        $keywordTotal = $keywords->count();
        $scorePct = $totalWeight > 0 ? (int) round($matchedWeight / $totalWeight * 100) : 0;
        $isFinal = in_array($application->status->value, ['hired', 'rejected'], true);
        $decisionMade = in_array($application->status->value, ['passed', 'interview', 'hired', 'rejected'], true);
        $decisionLabel = $application->status->value === 'rejected' ? 'Tertolak' : 'Lolos';
        $schedule = $application->interviewSchedule;

        $formatSize = function ($file): string {
            if (! $file) {
                return '—';
            }

            $bytes = (int) $file->file_size;

            if ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 1).' MB';
            }

            if ($bytes >= 1024) {
                return number_format($bytes / 1024, 0).' KB';
            }

            return $bytes.' B';
        };
    @endphp

    <div class="space-y-6">
        <x-trace
            :items="['Admin', 'Seleksi Berkas NLP', 'Detail Screening']"
            flow="PIPELINE: NlpScoringService → Stemmer → ScoreAggregator"
        />

        {{-- Header --}}
        <x-card class="border-t-4 border-t-blue-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <p class="eyebrow">{{ $application->job->department?->name ?? 'Departemen' }}</p>
                    <h1 class="mt-1 text-xl font-extrabold text-slate-900 sm:text-2xl">{{ $application->job->title }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $application->user->name }} · <span class="font-mono">{{ $application->user->email }}</span>
                        · Lamar {{ $application->applied_at?->format('d M Y') ?? '—' }}
                    </p>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <x-status-badge :status="$application->status" />
                        <x-badge variant="solid-blue">Skor {{ $application->score ?? '—' }}</x-badge>
                        <x-badge variant="slate">ID: {{ $application->id }}</x-badge>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2.5">
                    <form method="POST" action="{{ route('admin.screening.start', $application) }}">
                        @csrf
                        <x-button type="submit" variant="primary">Screening Pelamar</x-button>
                    </form>

                    @if (in_array($application->status->value, ['passed', 'interview'], true))
                        <x-button
                            variant="warning"
                            x-on:click="$dispatch('open-modal', 'schedule')"
                        >Jadwalkan Wawancara</x-button>
                    @endif

                    <x-button href="{{ route('admin.screening.index') }}" variant="secondary">Kembali</x-button>
                </div>
            </div>

            @if ($schedule)
                <div class="mt-4 border-t border-dashed border-slate-200 pt-4">
                    <x-alert variant="info" title="Jadwal wawancara sudah tersimpan">
                        {{ $schedule->interview_date->format('d M Y') }} · {{ \Illuminate\Support\Carbon::parse($schedule->interview_time)->format('H:i') }}
                        · Pewawancara: {{ $schedule->interviewer_name ?? '—' }}
                        · {{ $schedule->interview_type === 'online' ? 'Online' : 'Offline' }} —
                        @if ($schedule->interview_type === 'online')
                            <a href="{{ $schedule->interview_location }}" target="_blank" rel="noopener" class="font-semibold text-blue-900 underline decoration-blue-200 transition-colors duration-200 hover:decoration-blue-900">
                                {{ $schedule->interview_location }}
                            </a>
                        @else
                            <span class="font-semibold text-slate-700">{{ $schedule->interview_location }}</span>
                        @endif
                    </x-alert>
                </div>
            @endif
        </x-card>

        {{-- Split screen --}}
        <div class="grid gap-6 xl:grid-cols-2">
            {{-- Kolom kiri: Data Pelamar & Berkas --}}
            <div class="flex flex-col gap-6">
                <p class="eyebrow">Panel A · Data Pelamar & Berkas</p>

                {{-- Identitas --}}
                <x-card>
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">01 · Identitas</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Data Diri Pelamar</h2>
                        </div>
                        <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">TABLE · detail_persons</span>
                    </div>

                    <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">Nama Lengkap</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $application->user->name }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">Email</dt>
                            <dd class="mt-1 font-mono text-xs text-slate-700">{{ $application->user->email }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">No. HP / WA</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $application->user->phone ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">Jenis Kelamin</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $dp?->gender ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">Tempat / Tanggal Lahir</dt>
                            <dd class="mt-1 text-sm text-slate-700">
                                {{ $dp?->birth_place ?? '—' }}@if ($dp?->birth_date), {{ $dp->birth_date->format('d M Y') }}@endif
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">Alamat Lengkap</dt>
                            <dd class="mt-1 text-sm leading-relaxed text-slate-700">
                                @if ($dp?->full_address)
                                    {!! nl2br(e($dp->full_address)) !!}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    </dl>
                </x-card>

                {{-- Pendidikan, pengalaman, keahlian --}}
                <x-card>
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">02 · Riwayat</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Pendidikan, Pengalaman & Keahlian</h2>
                        </div>
                        <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">MASTER DATA</span>
                    </div>

                    <div class="mt-4 space-y-5">
                        <div>
                            <p class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">Pendidikan</p>
                            @if ($application->user->educations->isEmpty())
                                <p class="mt-2 text-sm text-slate-500">Belum ada riwayat pendidikan.</p>
                            @else
                                <ul class="mt-2 space-y-2">
                                    @foreach ($application->user->educations as $edu)
                                        <li class="rounded-lg border border-slate-100 bg-slate-50/60 px-3.5 py-2.5">
                                            <p class="text-sm font-semibold text-slate-900">{{ $edu->institution }}</p>
                                            <p class="text-xs text-slate-500">
                                                @if ($edu->degree || $edu->major)
                                                    {{ collect([$edu->degree, $edu->major])->filter()->implode(' — ') }}
                                                @else
                                                    Jenjang belum diisi
                                                @endif
                                            </p>
                                            <p class="mt-0.5 font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                                                {{ $edu->start_year ? $edu->start_year.'–'.($edu->end_year ?? 'sekarang') : 'Tahun belum diisi' }}
                                                @if ($edu->gpa !== null)
                                                    · IPK {{ number_format((float) $edu->gpa, 2) }}
                                                @endif
                                            </p>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div>
                            <p class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">Pengalaman</p>
                            @if ($application->user->experiences->isEmpty())
                                <p class="mt-2 text-sm text-slate-500">Belum ada pengalaman kerja.</p>
                            @else
                                <ul class="mt-2 space-y-2">
                                    @foreach ($application->user->experiences as $exp)
                                        <li class="rounded-lg border border-slate-100 bg-slate-50/60 px-3.5 py-2.5">
                                            <p class="text-sm font-semibold text-slate-900">
                                                {{ $exp->position }} <span class="font-normal text-slate-400">·</span>
                                                <span class="text-slate-700">{{ $exp->company }}</span>
                                            </p>
                                            @if ($exp->start_date)
                                                <p class="mt-0.5 font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                                                    {{ $exp->start_date->format('d M Y') }} — {{ $exp->end_date?->format('d M Y') ?? 'Sekarang' }}
                                                </p>
                                            @endif
                                            @if ($exp->description)
                                                <p class="mt-1 text-xs leading-relaxed text-slate-500">{!! nl2br(e($exp->description)) !!}</p>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div>
                            <p class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">Keahlian</p>
                            @if ($application->user->skills->isEmpty())
                                <p class="mt-2 text-sm text-slate-500">Belum ada keahlian terdaftar.</p>
                            @else
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($application->user->skills as $skill)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-200">
                                            {{ $skill->skill_name }}
                                            @if ($skill->level)
                                                <x-badge variant="slate">{{ $skill->level }}</x-badge>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </x-card>

                {{-- Berkas --}}
                <x-card>
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">03 · Berkas</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Dokumen Lamaran</h2>
                            <p class="mt-1 text-sm text-slate-500">Unduh CV dan ijazah yang dilampirkan pelamar.</p>
                        </div>
                        <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">STORAGE · LOCAL</span>
                    </div>

                    <div class="mt-4 space-y-2.5">
                        @if ($application->cvFile)
                            <a href="{{ route('files.cv', $application) }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition-all duration-200 ease-in-out hover:border-blue-100 hover:bg-blue-50/50 hover:shadow-sm">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-900">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-slate-900">{{ $application->cvFile->file_name }}</span>
                                    <span class="block font-mono text-[10px] tracking-wider text-slate-400 uppercase">{{ $formatSize($application->cvFile) }} · CV</span>
                                </span>
                                <svg class="h-4 w-4 shrink-0 text-slate-300 transition-colors duration-200 group-hover:text-blue-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            </a>
                        @else
                            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3.5 text-sm text-slate-500">
                                CV belum diunggah oleh pelamar.
                            </div>
                        @endif

                        @if ($application->degreeCertificationFile)
                            <a href="{{ route('files.ijazah', $application) }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition-all duration-200 ease-in-out hover:border-blue-100 hover:bg-blue-50/50 hover:shadow-sm">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-slate-900">{{ $application->degreeCertificationFile->file_name }}</span>
                                    <span class="block font-mono text-[10px] tracking-wider text-slate-400 uppercase">{{ $formatSize($application->degreeCertificationFile) }} · IJAZAH</span>
                                </span>
                                <svg class="h-4 w-4 shrink-0 text-slate-300 transition-colors duration-200 group-hover:text-blue-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            </a>
                        @else
                            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3.5 text-sm text-slate-500">
                                Ijazah belum diunggah oleh pelamar.
                            </div>
                        @endif
                    </div>
                </x-card>

                {{-- Pratinjau PDF --}}
                <x-card padding="p-0" class="overflow-hidden">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-4">
                        <div>
                            <p class="eyebrow">04 · Pratinjau</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Pratinjau Dokumen CV</h2>
                            <p class="mt-1 text-sm text-slate-500">Tampilan langsung berkas CV yang dilampirkan.</p>
                        </div>
                        <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">VIEWER · PDF</span>
                    </div>

                    @if ($application->cvFile)
                        <iframe
                            src="{{ route('files.cv.preview', $application) }}"
                            title="Pratinjau CV {{ $application->user->name }}"
                            class="h-[60vh] w-full bg-slate-100"
                        ></iframe>
                    @else
                        <div class="bg-slate-100 px-6 py-16 text-center">
                            <p class="text-sm text-slate-500">Berkas CV tidak tersedia untuk dipratinjau.</p>
                        </div>
                    @endif
                </x-card>
            </div>

            {{-- Kolom kanan: Hasil NLP --}}
            <div class="flex flex-col gap-6">
                <p class="eyebrow">Panel B · Hasil NLP</p>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-mono text-[10px] font-semibold tracking-widest text-slate-500 uppercase">Skor</p>
                        <p class="mt-1.5 font-heading text-2xl font-extrabold text-blue-900">{{ $application->score ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-mono text-[10px] font-semibold tracking-widest text-slate-500 uppercase">Keyword Cocok</p>
                        <p class="mt-1.5 font-heading text-2xl font-extrabold text-slate-900">
                            {{ $matchedCount }}<span class="text-base font-bold text-slate-400">/{{ $keywordTotal }}</span>
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-mono text-[10px] font-semibold tracking-widest text-slate-500 uppercase">Total Kata</p>
                        <p class="mt-1.5 font-heading text-2xl font-extrabold text-slate-900">{{ $application->parsedData?->total_words ?? 0 }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-mono text-[10px] font-semibold tracking-widest text-slate-500 uppercase">Kata Unik</p>
                        <p class="mt-1.5 font-heading text-2xl font-extrabold text-slate-900">{{ $uniqueStems }}</p>
                    </div>
                </div>

                <x-card>
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Aggregate</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Skor Akhir</h2>
                        </div>
                        <x-badge variant="solid-blue">{{ $scorePct }}%</x-badge>
                    </div>

                    <div class="mt-4">
                        <x-progress label="Skor Akhir" :value="$scorePct" />
                        <p class="mt-2 font-mono text-[11px] leading-relaxed text-slate-400">
                            {{ $matchedWeight }} bobot cocok ÷ {{ $totalWeight }} bobot total × 100
                        </p>
                    </div>
                </x-card>

                {{-- Tabel kata kunci --}}
                <x-card padding="p-0" class="overflow-hidden">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-4">
                        <div>
                            <p class="eyebrow">Matching</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Detail Pencocokan Kata Kunci</h2>
                        </div>
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            {{ $matchedCount }}/{{ $keywordTotal }} cocok
                        </span>
                    </div>

                    @if ($keywords->isEmpty())
                        <div class="px-6 py-10 text-center">
                            <p class="text-sm text-slate-500">
                                Formasi ini belum memiliki kata kunci. Konfigurasikan bobot kata kunci pada menu Kelola Lowongan terlebih dahulu.
                            </p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr>
                                        <th class="table-head">Kata Kunci</th>
                                        <th class="table-head">Kategori</th>
                                        <th class="table-head">Bobot</th>
                                        <th class="table-head">Status</th>
                                        <th class="table-head">Bagian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($keywords as $kw)
                                        <tr class="table-row">
                                            <td class="table-cell">
                                                <p class="font-semibold text-slate-900">
                                                    {{ $kw['keyword'] }}
                                                    @if ($kw['is_required'])
                                                        <span class="ml-1 font-mono text-[10px] font-semibold tracking-widest text-red-600 uppercase">Wajib</span>
                                                    @endif
                                                </p>
                                            </td>
                                            <td class="table-cell">
                                                <x-badge :variant="$kw['category']->value === 'hard_skill' ? 'blue' : 'amber'">
                                                    {{ $kw['category']->label() }}
                                                </x-badge>
                                            </td>
                                            <td class="table-cell font-mono text-xs font-bold text-slate-900">{{ $kw['weight'] }}</td>
                                            <td class="table-cell">
                                                @if ($kw['is_matched'])
                                                    <x-badge variant="solid-emerald">✓ Ditemukan</x-badge>
                                                @else
                                                    <x-badge variant="red">✖ Tidak</x-badge>
                                                @endif
                                            </td>
                                            <td class="table-cell">
                                                <span class="font-mono text-[10px] tracking-wider text-slate-500 uppercase">
                                                    {{ $kw['found_in_section'] ?? '—' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-card>

                {{-- Stemming --}}
                <x-card>
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Stemming</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Kata Hasil Stemming</h2>
                            <p class="mt-1 text-sm text-slate-500">Contoh token yang dinormalisasi menjadi kata dasar.</p>
                        </div>
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            {{ $totalStemmed }} token · {{ $uniqueStems }} unik
                        </span>
                    </div>

                    <div class="mt-4">
                        @if ($stemmerTop->isEmpty())
                            <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                                Belum ada data stemming. Jalankan pipeline NLP terlebih dahulu.
                            </p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach ($stemmerTop as $s)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 font-mono text-[11px] text-slate-700 ring-1 ring-inset ring-slate-200">
                                        {{ $s->original_word }}
                                        <span class="font-bold text-blue-900">→</span>
                                        {{ $s->stemmed_word }}
                                        <span class="font-semibold text-slate-400">×{{ $s->frequency_in_cv }}</span>
                                    </span>
                                @endforeach
                            </div>
                            <p class="mt-4 border-t border-dashed border-slate-200 pt-3 font-mono text-[11px] tracking-wide text-slate-400">
                                STEM_COUNT: {{ $totalStemmed }} total · {{ $uniqueStems }} unik
                                @if ($application->parsedData)
                                    · STATUS: {{ strtoupper($application->parsedData->parsing_status) }}
                                @endif
                            </p>
                        @endif
                    </div>
                </x-card>

                {{-- Formula --}}
                <x-card class="border-t-4 border-t-blue-900 bg-blue-900 text-white">
                    <p class="font-mono text-[10px] font-semibold tracking-[0.2em] text-blue-300 uppercase">Formula Scoring</p>
                    <p class="mt-4 font-mono text-base leading-relaxed text-blue-100">
                        Skor = (Σ bobot cocok ÷ Σ bobot total) × 100
                    </p>
                    <p class="mt-3 font-mono text-xs leading-relaxed text-blue-200/80">
                        IMPL: {{ $matchedWeight }} ÷ {{ $totalWeight }} × 100 = {{ $scorePct }}%
                    </p>
                </x-card>
            </div>
        </div>

        {{-- Panel keputusan --}}
        <x-card class="border-t-4 border-t-blue-900">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <p class="eyebrow">Keputusan</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Putuskan Hasil Seleksi Berkas</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        @if ($decisionMade)
                            Keputusan seleksi berkas untuk kandidat ini sudah ditetapkan.
                        @else
                            Loloskan kandidat ke tahap wawancara, atau tolak lamaran ini.
                        @endif
                    </p>
                </div>
                <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">DECISION PANEL</span>
            </div>

            @if ($decisionMade)
                <div id="decision-outcome" class="mt-5 flex items-center justify-center rounded-xl border px-6 py-10 {{ $application->status->value === 'rejected' ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50' }}">
                    <span class="text-3xl font-extrabold tracking-wide {{ $application->status->value === 'rejected' ? 'text-red-700' : 'text-emerald-700' }}">
                        {{ $decisionLabel }}
                    </span>
                </div>
            @else
                <div class="mt-4 grid gap-5 lg:grid-cols-2">
                    <form method="POST" action="{{ route('admin.screening.decide', $application) }}">
                        @csrf
                        <input type="hidden" name="decision" value="passed">

                        <x-textarea
                            name="decision_note"
                            label="Catatan Keputusan (opsional)"
                            rows="2"
                            placeholder="Catatan untuk pewawancara / manajer..."
                        ></x-textarea>

                        <x-button
                            type="submit"
                            variant="success"
                            size="lg"
                            class="mt-4 w-full"
                            :disabled="$isFinal"
                        >✓ LOLOS KE WAWANCARA</x-button>
                    </form>

                    <form method="POST" action="{{ route('admin.screening.decide', $application) }}">
                        @csrf
                        <input type="hidden" name="decision" value="rejected">

                        <x-textarea
                            name="decision_note"
                            label="Catatan Keputusan (opsional)"
                            rows="2"
                            placeholder="Alasan penolakan (opsional)..."
                        ></x-textarea>

                        <x-button
                            type="submit"
                            variant="danger"
                            size="lg"
                            class="mt-4 w-full"
                            :disabled="$isFinal"
                        >✖ TOLAK</x-button>
                    </form>
                </div>
            @endif
        </x-card>
    </div>

    {{-- Modal jadwal wawancara --}}
    @php
        $savedInterviewer = old('interviewer_name', $schedule?->interviewer_name);
        $isCustomInterviewer = filled($savedInterviewer) && ! $interviewers->has($savedInterviewer);
        $selectedInterviewer = $isCustomInterviewer ? '__other__' : $savedInterviewer;
        $interviewerOptions = $interviewers->put('__other__', 'Nama Pewawancara Lain...');
    @endphp
    <x-modal name="schedule" title="Form Jadwal Wawancara" subtitle="Datepicker, timepicker, dan lokasi/tautan wawancara." maxWidth="2xl">
        <form method="POST" action="{{ route('admin.schedule.store', $application) }}" class="space-y-4" x-data="{ other: @js($isCustomInterviewer || $savedInterviewer === '__other__') }">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <x-input type="date" name="interview_date" label="Tanggal Wawancara" :value="$schedule?->interview_date?->format('Y-m-d')" required />
                <x-input type="time" name="interview_time" label="Jam Wawancara" :value="$schedule ? \Illuminate\Support\Carbon::parse($schedule->interview_time)->format('H:i') : ''" required />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-select
                    name="interview_type"
                    label="Jenis Wawancara"
                    :options="['online' => 'Online (video call)', 'offline' => 'Offline (tatap muka)']"
                    :selected="$schedule?->interview_type ?? 'online'"
                    required
                />

                <x-input
                    name="interview_location"
                    label="Lokasi / Tautan"
                    placeholder="https://meet.google.com/… atau Gedung Rektorat Lt. 2"
                    :value="$schedule?->interview_location"
                    required
                />
            </div>

            <x-select
                name="interviewer_name"
                label="Nama Pewawancara"
                :options="$interviewerOptions"
                placeholder="Pilih pewawancara"
                :selected="$selectedInterviewer"
                required
                x-on:change="other = $event.target.value === '__other__'"
            />

            <div x-show="other" x-cloak>
                <label for="interviewer_other" class="label">Nama Pewawancara Lain</label>
                <input id="interviewer_other" name="interviewer_other" type="text" maxlength="255" x-bind:required="other" value="{{ old('interviewer_other', $isCustomInterviewer ? $savedInterviewer : '') }}" class="input" placeholder="Masukkan nama pewawancara">
                <x-input-error name="interviewer_other" />
            </div>

            <x-textarea
                name="notes"
                label="Catatan / Agenda"
                rows="3"
                placeholder="Materi microteaching, sesi wawancara, dan hal yang perlu disiapkan..."
            >{{ $schedule?->notes ?? '' }}</x-textarea>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                <x-button variant="secondary" x-on:click="$dispatch('close-modal', 'schedule')">Batal</x-button>
                <x-button type="submit" variant="warning">Simpan Jadwal</x-button>
            </div>
        </form>
    </x-modal>
</x-layouts.app>
