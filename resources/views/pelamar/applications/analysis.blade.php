<x-layouts.app title="Bedah CV">
    @php
        $score = $application->score !== null ? (float) $application->score : null;

        $scoreAccent = match (true) {
            $score === null => 'slate',
            $score >= 60 => 'emerald',
            $score >= 40 => 'amber',
            default => 'red',
        };

        $pct = $totalWeight > 0 ? (int) round($matchedWeight / $totalWeight * 100) : 0;
        $matchedCount = $keywords->where('is_matched', true)->count();
        $totalKeywords = $keywords->count();

        $hardKeywords = $keywords->filter(fn (array $kw): bool => $kw['category']->value === 'hard_skill');
        $softKeywords = $keywords->filter(fn (array $kw): bool => $kw['category']->value === 'soft_skill');

        $hardTotal = round((float) $hardKeywords->sum('weight'), 2);
        $hardMatched = round((float) $hardKeywords->where('is_matched', true)->sum('weight'), 2);
        $softTotal = round((float) $softKeywords->sum('weight'), 2);
        $softMatched = round((float) $softKeywords->where('is_matched', true)->sum('weight'), 2);

        $parsingStatusValue = new \Illuminate\Support\HtmlString('<span class="text-base">'.e($application->parsedData?->parsing_status ?? '—').'</span>');
        $rawSnippet = \Illuminate\Support\Str::limit($application->parsedData?->raw_text ?? '', 1200);
    @endphp

    <div class="space-y-6">
        <x-trace
            :items="['Pelamar', 'Lamaran', $application->job->title]"
            flow="PIPELINE: parse → preprocess → stem → match"
        />

        <x-section-header
            eyebrow="Analisis NLP"
            title="Bedah CV — {{ $application->job->title }}"
            subtitle="CV diparse dari PDF menjadi teks mentah, dinormalisasi lewat case folding, tokenizing, dan stopword removal, di-stem lewat stemmer Bahasa Indonesia, lalu dicocokkan dengan bobot kata kunci formasi."
        >
            <x-slot name="actions">
                <x-button href="{{ route('pelamar.applications.show', $application) }}" variant="secondary" size="sm">
                    ← Kembali ke Detail
                </x-button>
            </x-slot>
        </x-section-header>

        {{-- Ringkasan atas --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card
                label="Skor Kesesuaian"
                :value="$application->score ?? '—'"
                :accent="$scoreAccent"
                :caption="$matchedWeight.' / '.$totalWeight.' bobot terpakai'"
                meta="FINAL SCORE 0–100"
            />

            <x-stat-card
                label="Keyword Cocok"
                :value="$matchedCount.'/'.$totalKeywords"
                accent="blue"
                caption="kata kunci formasi ditemukan di CV"
                meta="MATCHING"
            />

            <x-stat-card
                label="Total Kata"
                :value="$application->parsedData?->total_words ?? 0"
                accent="slate"
                caption="kata terekstrak dari PDF"
                meta="TOKENISASI"
            />

            <x-stat-card
                label="Status Parsing"
                :value="$parsingStatusValue"
                accent="{{ $application->parsedData?->parsing_status === 'success' ? 'emerald' : 'red' }}"
                caption="hasil ekstraksi teks dari berkas CV"
                meta="PARSER: PDF"
            />
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- KIRI · Pratinjau dokumen --}}
            <div class="space-y-6">
                <x-card padding="p-0" class="overflow-hidden">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
                        <div class="min-w-0">
                            <p class="font-mono text-[10px] font-semibold tracking-widest text-blue-900 uppercase">Dokumen Sumber</p>
                            <p class="mt-1 truncate text-sm font-bold text-slate-900">
                                {{ $application->cvFile?->file_name ?? 'cv-terlampir.pdf' }}
                            </p>
                        </div>

                        @if ($application->cvFile)
                            <x-button href="{{ route('files.cv', $application) }}" variant="secondary" size="sm">Unduh</x-button>
                        @endif
                    </div>

                    <iframe
                        src="{{ route('files.cv.preview', $application) }}"
                        class="h-[70vh] w-full rounded-b-xl border-0 bg-slate-100"
                        title="Pratinjau CV {{ $application->user?->name ?? '' }}"
                    ></iframe>
                </x-card>

                <x-card>
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Output Parser</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Cuplikan Teks Hasil Parse</h2>
                        </div>
                        <span class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                            {{ $application->parsedData?->total_words ?? 0 }} KATA
                        </span>
                    </div>

                    @if ($rawSnippet)
                        <div class="mt-4 max-h-64 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-4 font-mono text-xs leading-relaxed whitespace-pre-wrap text-slate-600">
                            {{ $rawSnippet }}
                        </div>
                        <p class="mt-2 font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                            menampilkan 1.200 karakter pertama
                        </p>
                    @else
                        <p class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                            Teks hasil parse belum tersedia. Cuplikan akan muncul setelah pipeline NLP dijalankan.
                        </p>
                    @endif
                </x-card>
            </div>

            {{-- KANAN · Hasil analisis --}}
            <div class="space-y-6">
                @if (! $isScreened)
                    <x-empty-state
                        title="Belum ada hasil screening"
                        description="Admin HRD perlu menekan tombol Mulai Screening terlebih dahulu. Skor akan muncul di sini setelah pipeline NLP dijalankan."
                    />
                @else
                    <x-card class="border-t-4 border-t-emerald-600">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="eyebrow">Skor Akhir</p>
                                <p class="mt-2 font-heading text-4xl leading-none font-extrabold text-slate-900">
                                    {{ $application->score ?? '—' }}
                                </p>
                            </div>
                            <span class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">
                                {{ $matchedCount }}/{{ $totalKeywords }} KEYWORD
                            </span>
                        </div>

                        <div class="mt-4">
                            <x-progress :value="$pct" label="Skor Akhir" />
                        </div>

                        <div class="mt-4 rounded-xl border border-dashed border-blue-200 bg-blue-50 p-4 font-mono text-xs leading-relaxed text-blue-900">
                            Final Score = ({{ $matchedWeight }} / {{ $totalWeight }}) × 100 = {{ $application->score ?? '—' }}
                        </div>
                    </x-card>

                    {{-- Daftar kata kunci --}}
                    <x-card padding="p-0">
                        <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-5 py-4">
                            <div>
                                <p class="eyebrow">Matching</p>
                                <h2 class="mt-1 text-lg font-bold text-slate-900">Daftar Kata Kunci</h2>
                            </div>
                            <span class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                                {{ $totalKeywords }} ENTRI
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[680px] text-sm">
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
                                    @forelse ($keywords as $kw)
                                        <tr class="table-row">
                                            <td class="table-cell">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-semibold text-slate-900">{{ $kw['keyword'] }}</span>
                                                    @if ($kw['is_required'])
                                                        <x-badge variant="red">WAJIB</x-badge>
                                                    @endif
                                                </div>
                                            </td>

                                            <td class="table-cell">
                                                <x-badge :variant="$kw['category']->value === 'hard_skill' ? 'blue' : 'amber'">
                                                    {{ $kw['category']->label() }}
                                                </x-badge>
                                            </td>

                                            <td class="table-cell font-mono text-xs font-semibold text-slate-700">
                                                {{ number_format($kw['weight'], 2) }}
                                            </td>

                                            <td class="table-cell">
                                                @if ($kw['is_matched'])
                                                    <x-badge variant="emerald">✅ Ditemukan</x-badge>
                                                @else
                                                    <x-badge variant="red">✖ Tidak Ditemukan</x-badge>
                                                @endif
                                            </td>

                                            <td class="table-cell font-mono text-[11px] tracking-wider text-slate-500 uppercase">
                                                {{ $kw['found_in_section'] ?? '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="table-cell py-8 text-center text-sm text-slate-500" colspan="5">
                                                Formasi ini belum memiliki kata kunci.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-card>

                    {{-- Bobot & kontribusi --}}
                    <x-card>
                        <div class="border-b border-slate-100 pb-4">
                            <p class="eyebrow">Distribusi</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Bobot &amp; Kontribusi</h2>
                            <p class="mt-1 text-sm text-slate-500">Perbandingan bobot terpakai antar kategori kata kunci.</p>
                        </div>

                        <div class="mt-5 space-y-5">
                            <div>
                                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-2">
                                        <x-badge variant="blue">Hard Skill</x-badge>
                                        <span class="font-mono text-[11px] text-slate-500">{{ $hardMatched }}/{{ $hardTotal }} bobot</span>
                                    </span>
                                    <span class="font-mono text-xs font-bold text-slate-900">
                                        {{ $hardTotal > 0 ? (int) round($hardMatched / $hardTotal * 100) : 0 }}%
                                    </span>
                                </div>
                                <x-progress :value="$hardMatched" :max="$hardTotal" size="sm" />
                            </div>

                            <div>
                                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-2">
                                        <x-badge variant="amber">Soft Skill</x-badge>
                                        <span class="font-mono text-[11px] text-slate-500">{{ $softMatched }}/{{ $softTotal }} bobot</span>
                                    </span>
                                    <span class="font-mono text-xs font-bold text-slate-900">
                                        {{ $softTotal > 0 ? (int) round($softMatched / $softTotal * 100) : 0 }}%
                                    </span>
                                </div>
                                <x-progress :value="$softMatched" :max="$softTotal" size="sm" />
                            </div>
                        </div>
                    </x-card>

                    {{-- Hasil stemming --}}
                    <x-card>
                        <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                            <div>
                                <p class="eyebrow">data_stemmer</p>
                                <h2 class="mt-1 text-lg font-bold text-slate-900">Hasil Stemming</h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    Kata awal → kata dasar setelah normalisasi dan stopword removal.
                                </p>
                            </div>
                            <span class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                                {{ $stemmerSample->count() }} ENTRI
                            </span>
                        </div>

                        @if ($stemmerSample->isEmpty())
                            <p class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                                Belum ada data stemming untuk lamaran ini.
                            </p>
                        @else
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($stemmerSample as $s)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 font-mono text-[11px] ring-1 ring-inset ring-slate-200 transition-all duration-200 ease-in-out hover:border-blue-100 hover:ring-blue-100">
                                        <span class="text-slate-500">{{ $s->original_word }}</span>
                                        <span class="text-blue-900">→</span>
                                        <span class="font-semibold text-slate-900">{{ $s->stemmed_word }}</span>
                                        <span class="text-slate-400">×{{ $s->frequency_in_cv }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </x-card>

                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="font-mono text-[11px] leading-relaxed text-slate-500">
                            Final Score = ({{ $matchedWeight }} / {{ $totalWeight }}) × 100 = {{ $application->score ?? '—' }}
                        </p>

                        <x-button href="{{ route('pelamar.applications.show', $application) }}" variant="primary" size="sm">
                            Kembali ke Detail
                        </x-button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
