<x-layouts.app title="Lembar Keputusan">
    @php
        $scoreValue = $application->score !== null ? (float) $application->score : null;
        $scoreAccent = match (true) {
            $scoreValue === null => 'slate',
            $scoreValue >= 60 => 'emerald',
            $scoreValue >= 40 => 'amber',
            default => 'red',
        };
        $statusValue = '<span class="text-xl font-extrabold">'.$application->status->label().'</span>';
        $scoreCaption = $matchedWeight.'/'.$totalWeight.' bobot';
        $keywordMatched = $keywords->where('is_matched', true)->count();
        $interviewDate = $application->interviewSchedule?->interview_date;
        $detail = $application->user->detailPerson;
    @endphp

    <div class="space-y-6">
        <x-trace
            :items="['Manajer', 'Lembar Keputusan', $application->user->name]"
            :flow="'DECISION_PORTAL → '.$application->status->label()"
        />

        <x-section-header
            eyebrow="REVIEW DECISION PORTAL"
            :title="'Lembar Keputusan — '.$application->user->name"
            :subtitle="$application->job->title.' · '.($application->job->department?->name ?? '—')"
        >
            <x-slot name="actions">
                <x-button href="{{ route('files.cv', $application) }}" variant="secondary" size="sm">Unduh CV</x-button>
                <x-button href="{{ route('files.ijazah', $application) }}" variant="secondary" size="sm">Unduh Ijazah</x-button>
                <x-button href="{{ route('manajer.interviews.index') }}" variant="ghost" size="sm">Kembali</x-button>
            </x-slot>
        </x-section-header>

        {{-- Ringkasan --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Skor NLP" :value="$application->score ?? '—'" :caption="$scoreCaption" :accent="$scoreAccent" />
            <x-stat-card label="Status" :value="$statusValue" accent="amber" />
            <x-stat-card label="Keyword Cocok" :value="$keywordMatched.'/'.$keywords->count()" accent="blue" />
            <x-stat-card
                label="Jadwal"
                :value="$interviewDate?->translatedFormat('d M Y') ?? '—'"
                :caption="$application->interviewSchedule?->interview_time ?? 'Belum dijadwalkan'"
                accent="slate"
            />
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            {{-- Kiri: berkas data kandidat --}}
            <div class="min-w-0 space-y-6">
                {{-- Data pelamar --}}
                <x-card>
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Identitas</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Data Pelamar</h2>
                        </div>
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            NODE_{{ \Illuminate\Support\Str::padLeft((string) $application->id, 5, '0') }}
                        </span>
                    </div>

                    <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Nama Lengkap</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $application->user->name }}</dd>
                        </div>
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
                            <dt class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Tempat Lahir</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $detail?->birth_place ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Tanggal Lahir</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $detail?->birth_date?->translatedFormat('d M Y') ?? '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Alamat</dt>
                            <dd class="mt-1 text-sm leading-relaxed text-slate-700">{!! nl2br(e($detail?->full_address ?? '—')) !!}</dd>
                        </div>
                    </dl>
                </x-card>

                {{-- Pendidikan --}}
                <x-card>
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Riwayat</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Riwayat Pendidikan</h2>
                        </div>
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            {{ $application->user->educations->count() }} entri
                        </span>
                    </div>

                    @if ($application->user->educations->isEmpty())
                        <p class="mt-4 text-sm text-slate-500">Belum ada data pendidikan pada profil kandidat.</p>
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

                {{-- Pengalaman --}}
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
                        <p class="mt-4 text-sm text-slate-500">Belum ada pengalaman kerja pada profil kandidat.</p>
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

                {{-- Keahlian --}}
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
                        <p class="mt-4 text-sm text-slate-500">Belum ada data keahlian pada profil kandidat.</p>
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

            {{-- Kanan: pratinjau & keputusan --}}
            <div class="min-w-0 space-y-6">
                <x-card padding="p-0" class="overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                        <div>
                            <p class="eyebrow">Berkas Lamaran</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Berkas &amp; Pratinjau</h2>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-button href="{{ route('files.cv', $application) }}" variant="secondary" size="sm">CV</x-button>
                            <x-button href="{{ route('files.ijazah', $application) }}" variant="secondary" size="sm">Ijazah</x-button>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 px-6 pt-4">
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            PRATINJAU_CV · {{ $application->cvFile?->file_name ?? 'belum diunggah' }}
                        </span>
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            {{ $application->parsedData?->total_words ?? 0 }} KATA
                        </span>
                    </div>

                    <iframe
                        src="{{ route('files.cv.preview', $application) }}"
                        title="Pratinjau CV {{ $application->user->name }}"
                        class="mt-3 h-[55vh] w-full rounded-b-xl border-0 bg-slate-100"
                    ></iframe>
                </x-card>

                {{-- Kartu keputusan --}}
                <x-card class="border-t-4 border-t-blue-900">
                    <p class="eyebrow">Lembar Penilaian Wawancara</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Formulir Keputusan Akhir</h2>

                    <div class="mt-4">
                        <x-progress label="Skor Kesesuaian CV" :value="(float) ($application->score ?? 0)" />
                    </div>

                    @if ($decided)
                        <x-alert
                            :variant="$application->status->value === 'rejected' ? 'danger' : 'success'"
                            title="Keputusan sudah ditetapkan"
                            class="mt-5"
                        >
                            Kandidat dinyatakan <span class="font-bold">{{ $application->status->label() }}</span>.
                            <span class="mt-2 block font-semibold">Catatan wawancara:</span>
                            <span class="mt-1 block">{!! nl2br(e($application->interviewSchedule?->notes ?? '—')) !!}</span>
                        </x-alert>

                        <p class="mt-4 font-mono text-[11px] font-semibold tracking-wider text-slate-400 uppercase">
                            DIPUTUSKAN: {{ $application->updated_at->translatedFormat('d F Y H:i') }}
                        </p>
                    @else
                        <form method="POST" action="{{ route('manajer.decisions.update', $application) }}" class="mt-5 space-y-5">
                            @csrf

                            <x-input-error name="decision" />

                            <x-textarea
                                name="notes"
                                label="Catatan Wawancara"
                                rows="6"
                                required
                                placeholder="Catatan hasil wawancara, penilaian sikap, catatan kompetensi…"
                            >{{ old('notes', $application->interviewSchedule?->notes ?? '') }}</x-textarea>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <button type="submit" name="decision" value="hired" class="btn btn-success btn-lg w-full">
                                    HIRE · Terima Kandidat
                                </button>
                                <button type="submit" name="decision" value="rejected" class="btn btn-danger btn-lg w-full">
                                    REJECT · Tolak Kandidat
                                </button>
                            </div>

                            <x-alert variant="info" title="Keputusan bersifat final">
                                Status kandidat akan langsung berubah menjadi DITERIMA (HIRE) atau DITOLAK (REJECT)
                                dan tidak dapat diubah kembali dari halaman ini. Catatan wawancara tersimpan pada
                                berkas kandidat.
                            </x-alert>
                        </form>
                    @endif

                    <div class="mt-5 rounded-lg border border-dashed border-slate-200 bg-slate-50 p-4">
                        <p class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            Rumus Skor NLP
                        </p>
                        <p class="mt-2 font-mono text-xs leading-relaxed font-semibold text-slate-700">
                            Skor = ({{ $matchedWeight }} / {{ $totalWeight }}) × 100 = {{ $application->score ?? '—' }}
                        </p>
                    </div>
                </x-card>
            </div>

        </div>

            {{-- Keyword --}}
            <x-card padding="p-0">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                    <div>
                        <p class="eyebrow">Mesin NLP</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Hasil Pencocokan Keyword</h2>
                    </div>
                    <x-badge variant="solid-blue">{{ $keywordMatched }}/{{ $keywords->count() }} cocok</x-badge>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[600px] text-sm">
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
                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600">✅ Cocok</span>
                                            @if ($keyword['found_in_section'])
                                                <span class="mt-1 block font-mono text-[10px] font-medium tracking-wider text-slate-400 uppercase">
                                                    {{ $keyword['found_in_section'] }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-red-600">✖ Tidak</span>
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

            {{-- Form Penilaian Aspek Pelamar --}}
            @php
                $savedAssessment = $application->assessment;
                $savedItems = $savedAssessment?->items->keyBy('aspect') ?? collect();
                $aspects = \App\Models\InterviewAssessment::ASPECTS;
            @endphp

            <x-card class="border-t-4 border-t-blue-900" id="form-penilaian">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <p class="eyebrow">Form Penilaian</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Form Penilaian Aspek Pelamar</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Nilai = (Skor ÷ 5) × Bobot. Nilai akhir = jumlah seluruh nilai (maksimal 100). Skala skor 1 (Sangat Kurang) sampai 5 (Sangat Baik).
                        </p>
                    </div>
                    <span class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">ASSESSMENT FORM</span>
                </div>

                @if ($savedAssessment)
                    <x-alert variant="success" title="Penilaian sudah tersimpan" class="mt-4">
                        Nilai akhir <span class="font-bold">{{ number_format((float) $savedAssessment->final_score, 2) }}</span>
                        @if ($savedAssessment->decision) · Keputusan <span class="font-bold">{{ \App\Models\InterviewAssessment::decisionOptions()[$savedAssessment->decision] ?? $savedAssessment->decision }}</span> @endif
                        · Disimpan {{ $savedAssessment->updated_at->translatedFormat('d F Y H:i') }}.
                    </x-alert>
                @endif

                <form
                    id="assessment-form"
                    method="POST"
                    action="{{ route('manajer.interviews.assessment.store', $application) }}"
                    class="mt-5 space-y-5"
                    x-data="{ saved: @js($savedAssessment !== null), editing: @js($savedAssessment === null || $errors->any()) }"
                >
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Nama Pelamar</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $application->user->name }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Posisi / Formasi</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $application->job->title }}</p>
                        </div>
                        <x-input name="assessor_name" label="Penilai" :value="old('assessor_name', $savedAssessment?->assessor_name ?? auth()->user()->name)" x-bind:disabled="saved && !editing" />
                        <x-input type="date" name="assessed_at" label="Tanggal Penilaian" :value="old('assessed_at', $savedAssessment?->assessed_at?->format('Y-m-d') ?? now()->toDateString())" x-bind:disabled="saved && !editing" />
                    </div>

                    <div class="mt-2 overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full min-w-[1100px] text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-3 py-2.5 text-center text-[11px] font-semibold tracking-wider text-slate-500 uppercase">No</th>
                                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Aspek</th>
                                    <th class="px-3 py-2.5 text-center text-[11px] font-semibold tracking-wider text-slate-500 uppercase w-16">Bobot</th>
                                    <th class="px-3 py-2.5 text-center text-[11px] font-semibold tracking-wider text-slate-500 uppercase w-24">Skor (1-5)</th>
                                    <th class="px-3 py-2.5 text-center text-[11px] font-semibold tracking-wider text-slate-500 uppercase w-20">Nilai</th>
                                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($aspects as $index => $aspect)
                                    @php
                                        $item = $savedItems->get($aspect['code']);
                                        $scoreValue = old('scores.'.$aspect['code'], $item?->score);
                                        $noteValue = old('notes.'.$aspect['code'], $item?->note);
                                        $initialValue = $item?->value !== null ? number_format((float) $item->value, 2) : '—';
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-3 text-center font-semibold text-slate-500">{{ $index + 1 }}</td>
                                        <td class="px-3 py-3">
                                            <p class="font-semibold text-slate-800">{{ $aspect['label'] }}</p>
                                            <p class="mt-0.5 text-xs text-slate-500">{{ $aspect['detail'] }}</p>
                                        </td>
                                        <td class="px-3 py-3 text-center font-mono text-xs font-semibold text-slate-600">
                                            {{ rtrim(rtrim(number_format($aspect['weight'], 2, '.', ''), '0'), '.') }}%
                                        </td>
                                        <td class="px-3 py-3">
                                            <input
                                                type="number"
                                                name="scores[{{ $aspect['code'] }}]"
                                                value="{{ $scoreValue }}"
                                                min="1"
                                                max="5"
                                                step="1"
                                                inputmode="numeric"
                                                data-weight="{{ $aspect['weight'] }}"
                                                data-aspect="{{ $aspect['code'] }}"
                                                oninput="calculateAssessment()"
                                                x-bind:disabled="saved && !editing"
                                                class="input text-center"
                                            >
                                            <x-input-error :name="'scores.'.$aspect['code']" />
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span data-value-for="{{ $aspect['code'] }}" class="font-mono text-sm font-bold text-slate-900">{{ $initialValue }}</span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <input type="text" name="notes[{{ $aspect['code'] }}]" value="{{ $noteValue }}" placeholder="Catatan aspek…" class="input" x-bind:disabled="saved && !editing">
                                            <x-input-error :name="'notes.'.$aspect['code']" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50">
                                <tr>
                                    <td colspan="2" class="px-3 py-3 text-right font-bold text-slate-700">TOTAL</td>
                                    <td class="px-3 py-3 text-center font-mono text-xs font-bold text-slate-700">100%</td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3 text-center">
                                        <span id="assessment-total" class="font-mono text-base font-extrabold text-blue-900">{{ $savedAssessment?->final_score !== null ? number_format((float) $savedAssessment->final_score, 2) : '0.00' }}</span>
                                    </td>
                                    <td class="px-3 py-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-2">
                        <div class="space-y-3">
                            <p class="label">Keputusan (batas lulus {{ (int) \App\Models\InterviewAssessment::PASSING_SCORE }})</p>
                            <div class="grid gap-2 sm:grid-cols-3">
                                @foreach (\App\Models\InterviewAssessment::decisionOptions() as $value => $label)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-700 transition-colors duration-200 hover:border-blue-200 hover:bg-blue-50">
                                        <input type="radio" name="decision" value="{{ $value }}" @checked(old('decision', $savedAssessment?->decision) === $value) class="text-blue-900" x-bind:disabled="saved && !editing">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :name="'decision'" />
                            <p id="assessment-hint" class="font-mono text-[11px] text-slate-400"></p>
                        </div>

                        <x-textarea name="conclusion" label="Kesimpulan / Rekomendasi" rows="4" placeholder="Kesimpulan penilaian dan rekomendasi untuk manajemen…" x-bind:disabled="saved && !editing">{{ old('conclusion', $savedAssessment?->conclusion) }}</x-textarea>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <span x-show="saved && !editing" class="text-sm font-semibold text-emerald-700">Penilaian Berhasil Tersimpan.</span>
                        <x-button type="button" variant="secondary" x-show="saved && !editing" x-on:click="editing = true">Edit Penilaian</x-button>
                        <span x-show="!saved || editing"><x-button type="submit" variant="primary">Simpan Penilaian</x-button></span>
                    </div>
                </form>

                <script>
                    function calculateAssessment() {
                        var form = document.getElementById('assessment-form');
                        if (!form) { return; }
                        var total = 0;
                        var inputs = form.querySelectorAll('input[data-weight]');
                        for (var i = 0; i < inputs.length; i++) {
                            var input = inputs[i];
                            var weight = parseFloat(input.getAttribute('data-weight')) || 0;
                            var score = parseInt(input.value, 10);
                            var target = form.querySelector('[data-value-for="' + input.getAttribute('data-aspect') + '"]');
                            if (!isNaN(score) && score >= 1 && score <= 5) {
                                var value = (score / 5) * weight;
                                total += value;
                                if (target) { target.textContent = value.toFixed(2); }
                            } else if (target) {
                                target.textContent = '—';
                            }
                        }
                        var totalEl = document.getElementById('assessment-total');
                        if (totalEl) { totalEl.textContent = total.toFixed(2); }
                        var hint = document.getElementById('assessment-hint');
                        if (hint) {
                            hint.textContent = total > 0
                                ? (total >= {{ (float) \App\Models\InterviewAssessment::PASSING_SCORE }} ? 'Mencapai batas lulus (≥ {{ (int) \App\Models\InterviewAssessment::PASSING_SCORE }}).' : 'Di bawah batas lulus {{ (int) \App\Models\InterviewAssessment::PASSING_SCORE }}.')
                                : '';
                        }
                    }
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', calculateAssessment);
                    } else {
                        calculateAssessment();
                    }
                </script>
            </x-card>
        </div>
    </div>
</x-layouts.app>
