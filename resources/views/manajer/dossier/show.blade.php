<x-layouts.app title="Dossier Kandidat">
    @php
        $tabs = [
            'berkas' => 'Berkas & Parse',
            'profil' => 'Profil Kandidat',
            'keyword' => 'Cocok Keyword',
            'stemming' => 'Hasil Stemming',
        ];
        $detail = $application->user->detailPerson;
        $keywordMatched = $keywords->where('is_matched', true)->count();
    @endphp

    <div class="space-y-6">
        <x-trace :items="['Manajer', 'Dossier', $application->user->name]" flow="DOSSIER_NODE: OPEN" />

        {{-- Kepala dossier --}}
        <x-card class="border-t-4 border-t-blue-900">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-900 font-heading text-base font-extrabold text-white">
                        {{ $application->user->initials() }}
                    </span>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="page-title">{{ $application->user->name }}</h1>
                            <x-status-badge :status="$application->status" />
                            <x-badge variant="solid-blue">Skor NLP {{ $application->score ?? '—' }}</x-badge>
                        </div>

                        <p class="mt-1.5 text-sm text-slate-500">
                            {{ $application->job->title }}
                            · {{ $application->job->department?->name ?? '—' }}
                        </p>

                        <p class="mt-2 font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            DOSSIER_ID: {{ \Illuminate\Support\Str::padLeft((string) $application->id, 6, '0') }}
                            · MASUK {{ $application->applied_at?->translatedFormat('d M Y') ?? '—' }}
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2.5">
                    <x-button href="{{ route('manajer.dossier.index') }}" variant="secondary" size="sm">Kembali</x-button>
                    <x-button href="{{ route('manajer.interviews.show', $application) }}" size="sm">Buka Lembar Keputusan</x-button>
                </div>
            </div>
        </x-card>

        {{-- Tab dossier --}}
        <div x-data="{ tab: 'berkas' }">
            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-4">
                @foreach ($tabs as $key => $label)
                    <button
                        type="button"
                        x-on:click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}'
                            ? 'bg-blue-900 text-white shadow-sm ring-1 ring-blue-900'
                            : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-blue-100 hover:text-blue-900 hover:ring-blue-200'"
                        class="rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-200 ease-in-out"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Tab: berkas --}}
            <div
                x-show="tab === 'berkas'"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mt-6"
            >
                <x-card padding="p-0" class="overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                        <div>
                            <p class="eyebrow">Arsip Digital</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Berkas &amp; Pratinjau CV</h2>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-button href="{{ route('files.cv', $application) }}" variant="secondary" size="sm">Unduh CV</x-button>
                            <x-button href="{{ route('files.ijazah', $application) }}" variant="secondary" size="sm">Unduh Ijazah</x-button>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 px-6 pt-4">
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            CV: {{ $application->cvFile?->file_name ?? 'belum diunggah' }}
                        </span>
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            IJAZAH: {{ $application->degreeCertificationFile?->file_name ?? 'belum diunggah' }}
                        </span>
                    </div>

                    <iframe
                        src="{{ route('files.cv.preview', $application) }}"
                        title="Pratinjau CV {{ $application->user->name }}"
                        class="mt-3 h-[55vh] w-full rounded-b-xl border-0 bg-slate-100"
                    ></iframe>
                </x-card>

                <x-card class="mt-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Pipeline NLP</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Teks Hasil Parse</h2>
                        </div>
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            {{ $application->parsedData?->total_words ?? 0 }} KATA · STATUS {{ strtoupper($application->parsedData?->parsing_status ?? 'N/A') }}
                        </span>
                    </div>

                    <div class="mt-4 max-h-96 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50 p-4 font-mono text-xs whitespace-pre-wrap text-slate-600">
                        @if ($application->parsedData?->raw_text)
                            {{ $application->parsedData->raw_text }}
                        @else
                            <span class="text-slate-400">Belum ada teks hasil parse untuk kandidat ini.</span>
                        @endif
                    </div>
                </x-card>
            </div>

            {{-- Tab: profil --}}
            <div
                x-show="tab === 'profil'"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mt-6 space-y-6"
            >
                <x-card>
                    <div class="border-b border-slate-100 pb-4">
                        <p class="eyebrow">Identitas</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Data Diri</h2>
                    </div>

                    <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <dt class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Email</dt>
                            <dd class="mt-1 break-all text-sm text-slate-700">{{ $application->user->email }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Telepon</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $application->user->phone ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Jenis Kelamin</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $detail?->gender ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Tempat, Tanggal Lahir</dt>
                            <dd class="mt-1 text-sm text-slate-700">
                                {{ $detail?->birth_place ?? '—' }}, {{ $detail?->birth_date?->translatedFormat('d M Y') ?? '—' }}
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Alamat</dt>
                            <dd class="mt-1 text-sm leading-relaxed text-slate-700">{!! nl2br(e($detail?->full_address ?? '—')) !!}</dd>
                        </div>
                    </dl>
                </x-card>

                <x-card>
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Riwayat</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Pendidikan</h2>
                        </div>
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            {{ $application->user->educations->count() }} entri
                        </span>
                    </div>

                    @if ($application->user->educations->isEmpty())
                        <p class="mt-4 text-sm text-slate-500">Belum ada data pendidikan.</p>
                    @else
                        <ul class="mt-4 space-y-3">
                            @foreach ($application->user->educations as $education)
                                <li class="rounded-lg border border-slate-100 bg-slate-50 p-4 transition-all duration-200 ease-in-out hover:border-blue-100 hover:bg-blue-50">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-slate-900">{{ $education->institution }}</p>
                                            <p class="mt-0.5 text-xs text-slate-500">
                                                {{ collect([$education->degree, $education->major])->filter()->implode(' · ') ?: '—' }}
                                            </p>
                                        </div>
                                        <span class="shrink-0 font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                                            {{ $education->start_year ?? '—' }}–{{ $education->end_year ?? '—' }}
                                        </span>
                                    </div>
                                    @if ($education->gpa)
                                        <span class="mt-2 inline-flex rounded-full bg-blue-100 px-2.5 py-1 font-mono text-[10px] font-semibold text-blue-900">
                                            IPK {{ $education->gpa }}
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>

                <x-card>
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Karier</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Pengalaman Kerja</h2>
                        </div>
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            {{ $application->user->experiences->count() }} entri
                        </span>
                    </div>

                    @if ($application->user->experiences->isEmpty())
                        <p class="mt-4 text-sm text-slate-500">Belum ada pengalaman kerja.</p>
                    @else
                        <ul class="mt-4 space-y-4">
                            @foreach ($application->user->experiences as $experience)
                                <li class="border-l-2 border-blue-900 pl-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-sm font-bold text-slate-900">{{ $experience->position }}</p>
                                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                                            {{ $experience->start_date?->format('M Y') ?? '—' }} → {{ $experience->end_date?->format('M Y') ?? 'Sekarang' }}
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-xs font-semibold text-blue-900">{{ $experience->company }}</p>
                                    @if ($experience->description)
                                        <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{!! nl2br(e($experience->description)) !!}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>

                <x-card>
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Kompetensi</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Keahlian</h2>
                        </div>
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            {{ $application->user->skills->count() }} keahlian
                        </span>
                    </div>

                    @if ($application->user->skills->isEmpty())
                        <p class="mt-4 text-sm text-slate-500">Belum ada data keahlian.</p>
                    @else
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($application->user->skills as $skill)
                                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition-all duration-200 ease-in-out hover:border-blue-100 hover:bg-blue-50 hover:text-blue-900">
                                    {{ $skill->skill_name }}
                                    @if ($skill->level)
                                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">{{ $skill->level }}</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    @endif
                </x-card>
            </div>

            {{-- Tab: keyword --}}
            <div
                x-show="tab === 'keyword'"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mt-6 space-y-6"
            >
                <x-card>
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Mesin NLP</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Skor Kesesuaian</h2>
                        </div>
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            {{ $keywordMatched }}/{{ $keywords->count() }} keyword cocok
                        </span>
                    </div>

                    <div class="mt-4">
                        <x-progress label="Skor Kesesuaian CV" :value="(float) ($application->score ?? 0)" />
                    </div>

                    <div class="mt-4 rounded-lg border border-dashed border-slate-200 bg-slate-50 p-4">
                        <p class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Rumus Skor NLP</p>
                        <p class="mt-2 font-mono text-xs font-semibold leading-relaxed text-slate-700">
                            Skor = (bobot keyword cocok ÷ total bobot keyword) × 100 = {{ $application->score ?? '—' }}
                        </p>
                    </div>
                </x-card>

                <x-card padding="p-0">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                        <div>
                            <p class="eyebrow">Detail</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Tabel Pencocokan Keyword</h2>
                        </div>
                        <x-badge variant="solid-blue">{{ $keywordMatched }}/{{ $keywords->count() }} cocok</x-badge>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[560px] text-sm">
                            <thead>
                                <tr>
                                    <th class="table-head">Kata Kunci</th>
                                    <th class="table-head">Kategori</th>
                                    <th class="table-head text-right">Bobot</th>
                                    <th class="table-head text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($keywords as $keyword)
                                    <tr class="table-row">
                                        <td class="table-cell font-semibold text-slate-900">{{ $keyword['keyword'] }}</td>
                                        <td class="table-cell">
                                            <x-badge :variant="$keyword['category']->value === 'hard_skill' ? 'blue' : 'amber'">
                                                {{ $keyword['category']->label() }}
                                            </x-badge>
                                        </td>
                                        <td class="table-cell text-right font-mono text-xs font-semibold text-slate-600">
                                            {{ $keyword['weight'] }}
                                        </td>
                                        <td class="table-cell text-right">
                                            @if ($keyword['is_matched'])
                                                <span class="text-xs font-bold text-emerald-600">✅ Cocok</span>
                                            @else
                                                <span class="text-xs font-bold text-red-600">✖ Tidak</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">
                                            Belum ada keyword untuk formasi ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>

            {{-- Tab: stemming --}}
            <div
                x-show="tab === 'stemming'"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mt-6"
            >
                <x-card>
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Stopword &amp; Stemming</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Data Stemmer Kandidat</h2>
                        </div>
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            menampilkan maks. 120 baris
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            <span class="h-2 w-2 rounded-full bg-blue-500"></span> hard_skill
                        </span>
                        <span class="inline-flex items-center gap-1.5 font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            <span class="h-2 w-2 rounded-full bg-amber-500"></span> soft_skill
                        </span>
                        <span class="inline-flex items-center gap-1.5 font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            <span class="h-2 w-2 rounded-full bg-slate-400"></span> general
                        </span>
                    </div>

                    @if ($stemmer->isEmpty())
                        <p class="mt-4 text-sm text-slate-500">Belum ada data stemming untuk kandidat ini.</p>
                    @else
                        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach ($stemmer as $row)
                                @php
                                    $categoryRing = match ($row->word_category) {
                                        'hard_skill' => 'border-blue-200 ring-1 ring-blue-100 hover:ring-blue-300',
                                        'soft_skill' => 'border-amber-200 ring-1 ring-amber-100 hover:ring-amber-300',
                                        default => 'border-slate-200 ring-1 ring-slate-100 hover:ring-slate-300',
                                    };
                                @endphp
                                <div class="flex items-center justify-between gap-2 rounded-lg border bg-white px-3 py-2 font-mono text-[11px] transition-all duration-200 ease-in-out {{ $categoryRing }}">
                                    <span class="min-w-0 truncate text-slate-500">{{ $row->original_word }}</span>
                                    <span class="shrink-0 text-slate-300">→</span>
                                    <span class="min-w-0 truncate font-semibold text-slate-900">{{ $row->stemmed_word }}</span>
                                    <span class="shrink-0 font-semibold text-slate-400">×{{ $row->frequency_in_cv }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
