<x-layouts.app title="Form Lamaran">
    @php
        $hasUploadErrors = $errors->has('cv_file') || $errors->has('ijazah_file') || $errors->has('agreement');

        $stepItems = [
            ['no' => '01', 'label' => 'Pilih Lowongan', 'hint' => 'Konfirmasi formasi'],
            ['no' => '02', 'label' => 'Master Data', 'hint' => 'Cek kelengkapan data'],
            ['no' => '03', 'label' => 'Unggah Berkas dan Submit', 'hint' => 'CV, ijazah & kirim'],
        ];

        $completenessLabels = [
            'identitas' => ['label' => 'Identitas & data diri', 'hint' => 'Nomor WhatsApp aktif agar HRD dapat menghubungi Anda'],
            'pendidikan' => ['label' => 'Riwayat pendidikan', 'hint' => 'Minimal satu jenjang pendidikan tercatat'],
            'pengalaman' => ['label' => 'Pengalaman kerja', 'hint' => 'Relevan untuk penilaian berkas'],
            'keahlian' => ['label' => 'Keahlian', 'hint' => 'Dicocokkan dengan bobot keyword formasi'],
        ];
    @endphp

    <div
        x-data="{ step: {{ $hasUploadErrors ? 3 : 1 }}, cvName: @js($defaultCv?->file_name ?? ''), ijazahName: @js($defaultDegree?->file_name ?? '') }"
        class="space-y-6"
    >
        <x-trace :items="['Pelamar', 'Lowongan', 'Form Lamaran']" flow="{{ $job->title }}" />

        <x-section-header
            eyebrow="Portal Pelamar"
            title="Formulir Lamaran"
            subtitle="Tiga langkah: konfirmasi formasi, pastikan master data lengkap, lalu unggah berkas dan submit lamaran. Satu formulir untuk satu formasi."
        >
            <x-slot name="actions">
                <x-button href="{{ route('pelamar.jobs.index') }}" variant="secondary" size="sm">Batal &amp; Kembali</x-button>
            </x-slot>
        </x-section-header>

        {{-- Indikator langkah --}}
        <ol class="grid gap-3 sm:grid-cols-3">
            @foreach ($stepItems as $index => $stepItem)
                @php $stepNumber = $index + 1; @endphp
                <li
                    class="flex items-center gap-3 rounded-xl border px-4 py-3 transition-all duration-200 ease-in-out"
                    :class="step > {{ $stepNumber }} ? 'border-emerald-200 bg-emerald-50' : (step === {{ $stepNumber }} ? 'border-blue-900 bg-blue-100 shadow-sm' : 'border-slate-200 bg-white')"
                >
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg font-mono text-xs font-bold transition-colors duration-200"
                        :class="step > {{ $stepNumber }} ? 'bg-emerald-600 text-white' : (step === {{ $stepNumber }} ? 'bg-blue-900 text-white' : 'bg-slate-100 text-slate-400')"
                    >
                        <span x-show="step <= {{ $stepNumber }}">{{ $stepItem['no'] }}</span>
                        <span x-show="step > {{ $stepNumber }}">✓</span>
                    </span>

                    <span class="min-w-0">
                        <span
                            class="block truncate text-sm font-bold"
                            :class="step === {{ $stepNumber }} ? 'text-blue-900' : (step > {{ $stepNumber }} ? 'text-emerald-700' : 'text-slate-500')"
                        >
                            {{ $stepItem['label'] }}
                        </span>
                        <span class="block truncate font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                            {{ $stepItem['hint'] }}
                        </span>
                    </span>
                </li>
            @endforeach
        </ol>

        <form method="POST" action="{{ route('pelamar.apply.store', $job) }}" enctype="multipart/form-data">
            @csrf

            {{-- 01 · Pilih Lowongan --}}
            <div x-show="step === 1" x-cloak class="space-y-6">
                <x-card padding="p-0">
                    <div class="border-b border-slate-100 bg-slate-50 px-6 py-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <span class="font-mono text-[10px] font-semibold tracking-widest text-blue-900 uppercase">
                                    {{ $job->department?->name ?? 'Departemen' }}
                                </span>
                                <h2 class="mt-2 font-heading text-2xl leading-tight font-extrabold text-slate-900">
                                    {{ $job->title }}
                                </h2>
                            </div>
                            <x-badge variant="blue">{{ $job->job_type->label() }}</x-badge>
                        </div>
                    </div>

                    <div class="p-6">
                        <dl class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <dt class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">Lokasi</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $job->location ?? 'Seluruh Indonesia' }}</dd>
                            </div>
                            <div>
                                <dt class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">Rentang Gaji</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $job->salaryRange() ?? 'Negosiasi' }}</dd>
                            </div>
                            <div>
                                <dt class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">Dibuka</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $job->created_at->translatedFormat('d F Y') }}</dd>
                            </div>
                        </dl>

                        <div class="mt-6 grid gap-6 lg:grid-cols-2">
                            <div>
                                <p class="eyebrow">Deskripsi Posisi</p>
                                <div class="mt-2.5 text-sm leading-relaxed text-slate-600">
                                    {!! nl2br(e($job->description)) !!}
                                </div>
                            </div>

                            <div>
                                <p class="eyebrow">Persyaratan</p>
                                <div class="mt-2.5 text-sm leading-relaxed text-slate-600">
                                    @if ($job->requirements)
                                        {!! nl2br(e($job->requirements)) !!}
                                    @else
                                        <span class="text-slate-400">Persyaratan tidak dirinci — lihat deskripsi posisi.</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-dashed border-slate-200 px-6 py-4">
                        <x-button type="button" variant="primary" x-on:click="step = 2">
                            Lanjut ke Master Data
                        </x-button>
                    </div>
                </x-card>
            </div>

            {{-- 02 · Master Data --}}
            <div x-show="step === 2" x-cloak class="space-y-6">
                <x-card>
                    <div class="border-b border-slate-100 pb-4">
                        <p class="eyebrow">02 · Master Data</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Kelengkapan Data Pelamar</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Data diambil dari halaman Profil & Master Data — tidak perlu diisi ulang.
                        </p>
                    </div>

                    <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach ($completenessLabels as $key => $meta)
                            @php $filled = (bool) ($completeness[$key] ?? false); @endphp
                            <li @class([
                                'flex items-start gap-3 rounded-xl border px-4 py-3.5 transition-all duration-200 ease-in-out',
                                'border-emerald-200 bg-emerald-50' => $filled,
                                'border-amber-200 bg-amber-50' => ! $filled,
                            ])>
                                <span @class([
                                    'mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[11px] font-bold text-white',
                                    'bg-emerald-600' => $filled,
                                    'bg-amber-600' => ! $filled,
                                ])>{{ $filled ? '✓' : '!' }}</span>

                                <span class="min-w-0">
                                    <span @class([
                                        'block text-sm font-bold',
                                        'text-emerald-800' => $filled,
                                        'text-amber-800' => ! $filled,
                                    ])>{{ $meta['label'] }}</span>
                                    <span class="mt-0.5 block text-xs leading-relaxed text-slate-600">{{ $meta['hint'] }}</span>
                                    <span class="mt-1 block font-mono text-[10px] tracking-widest uppercase {{ $filled ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $filled ? 'LENGKAP' : 'BELUM LENGKAP' }}
                                    </span>
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">Nama</dt>
                                    <dd class="mt-0.5 font-semibold text-slate-900">{{ $user->name }}</dd>
                                </div>
                                <div>
                                    <dt class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">Email</dt>
                                    <dd class="mt-0.5 font-semibold text-slate-900">{{ $user->email }}</dd>
                                </div>
                                <div>
                                    <dt class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">WhatsApp</dt>
                                    <dd class="mt-0.5 font-semibold text-slate-900">{{ $user->phone ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">Pendidikan / Pengalaman</dt>
                                    <dd class="mt-0.5 font-semibold text-slate-900">
                                        {{ $user->educations->count() }} jenjang · {{ $user->experiences->count() }} pengalaman
                                    </dd>
                                </div>
                            </dl>

                            <x-button href="{{ route('pelamar.profile.edit') }}" variant="secondary" size="sm">
                                Lengkapi Data
                            </x-button>
                        </div>

                        <div class="mt-4 border-t border-dashed border-slate-200 pt-4">
                            <p class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">Keahlian Terdaftar</p>

                            @if ($user->skills->isEmpty())
                                <p class="mt-2 text-sm text-slate-500">Belum ada keahlian terdaftar.</p>
                            @else
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($user->skills as $skill)
                                        <x-badge variant="blue">{{ $skill->skill_name }}</x-badge>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap justify-between gap-3 border-t border-dashed border-slate-200 pt-5">
                        <x-button type="button" variant="secondary" x-on:click="step--">Kembali</x-button>
                        <x-button type="button" variant="primary" x-on:click="step = 3">Lanjut ke Unggah Berkas &amp; Submit</x-button>
                    </div>
                </x-card>
            </div>

            {{-- 03 · Unggah Berkas dan Submit --}}
            <div x-show="step === 3" x-cloak class="space-y-6">
                <x-card>
                    <div class="border-b border-slate-100 pb-4">
                        <p class="eyebrow">03 · Unggah Berkas dan Submit</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">CV, Ijazah &amp; Pernyataan</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Berkas akan diparse menjadi teks untuk penilaian. Pastikan dokumen terbaca jelas.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="mt-5 space-y-2">
                            @foreach ($errors->all() as $error)
                                <p class="rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs font-medium text-red-600">
                                    {{ $error }}
                                </p>
                            @endforeach
                        </div>
                    @endif

                    @if ($defaultCv)
                        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-[11px] font-bold text-white">✓</span>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-emerald-800">CV default terpilih otomatis</p>
                                    <p class="mt-0.5 truncate text-xs text-emerald-700">
                                        {{ $defaultCv->file_name }} · diunggah {{ $defaultCv->uploaded_at?->translatedFormat('d M Y') }}
                                    </p>
                                    <p class="mt-1 text-xs leading-relaxed text-slate-600">
                                        Unggah CV baru hanya jika ingin mengganti berkas untuk lamaran ini.
                                    </p>
                                </div>
                            </div>
                            <x-button href="{{ route('pelamar.cv-files.index') }}" variant="secondary" size="sm">
                                Kelola Berkas
                            </x-button>
                        </div>
                    @endif

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="flex h-60 cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-9 text-center transition-all duration-200 ease-in-out hover:border-blue-900 hover:bg-blue-50">
                            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-100 text-blue-900">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 12 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H6.75m6.75 4.875-2.25 2.25m0 0-2.25 2.25m2.25-2.25L9 12.375M6.75 9.75 4.5 7.5m15 2.25 2.25-2.25M12 6.75V4.5m0 18v-2.25M12 12H4.5"/></svg>
                            </span>

                            <span class="text-sm font-bold text-slate-900">Unggah CV</span>
                            <span class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">
                                PDF, maksimal 3 MB{{ $defaultCv ? ' · opsional' : '' }}
                            </span>
                            <span class="max-w-full truncate text-xs font-semibold text-blue-900" x-text="cvName">{{ $defaultCv?->file_name ?? 'Belum ada berkas dipilih' }}</span>

                            <input
                                type="file"
                                name="cv_file"
                                accept="application/pdf"
                                @if (! $defaultCv) required @endif
                                class="sr-only"
                                data-preview-target="cv-current-preview"
                                data-preview-types="application/pdf"
                                x-on:change="cvName = $event.target.files[0]?.name ?? ''"
                            />
                            </label>
                            @if ($defaultCv)
                                <div class="mt-2 flex justify-center">
                                    <x-button id="cv-current-preview" href="{{ route('pelamar.cv-files.preview', $defaultCv) }}" data-preview-fallback="{{ route('pelamar.cv-files.preview', $defaultCv) }}" variant="secondary" size="sm" target="_blank" rel="noopener">Lihat CV saat ini</x-button>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="flex h-60 cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-9 text-center transition-all duration-200 ease-in-out hover:border-blue-900 hover:bg-blue-50">
                            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-100 text-blue-900">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25V6.75a3.375 3.375 0 0 0-3.375-3.375h-9A3.375 3.375 0 0 0 3.75 6.75v10.5A2.25 2.25 0 0 0 6 19.5h9a3.375 3.375 0 0 0 3.375-3.375Zm0-2.25a2.25 2.25 0 0 0 2.25-2.25v-3.75a2.25 2.25 0 0 0-2.25-2.25h-1.5m-4.5 6.75H12m-2.25 0v3.75m0 0 1.875-1.875M9.75 16.5l-1.875 1.875"/></svg>
                            </span>

                            <span class="text-sm font-bold text-slate-900">Unggah Ijazah</span>
                            <span class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">
                                PDF/JPG/PNG, maksimal 3 MB{{ $defaultDegree ? ' · opsional, pilih file untuk mengganti' : '' }}
                            </span>
                            <span class="max-w-full truncate text-xs font-semibold text-blue-900" x-text="ijazahName">Belum ada berkas dipilih</span>

                            <input
                                type="file"
                                name="ijazah_file"
                                accept="application/pdf,image/jpeg,image/png"
                                @if (! $defaultDegree) required @endif
                                class="sr-only"
                                data-preview-target="ijazah-current-preview"
                                data-preview-types="application/pdf,image/jpeg,image/png"
                                x-on:change="ijazahName = $event.target.files[0]?.name ?? ''"
                            />
                            </label>
                            @if ($defaultDegree)
                                <div class="mt-2 flex justify-center">
                                    <x-button id="ijazah-current-preview" href="{{ route('files.ijazah.preview', $defaultDegree->application) }}" data-preview-fallback="{{ route('files.ijazah.preview', $defaultDegree->application) }}" variant="secondary" size="sm" target="_blank" rel="noopener">Lihat Ijazah saat ini</x-button>
                                </div>
                            @else
                                <div class="mt-2 text-center">
                                    <a href="{{ route('pelamar.cv-files.index') }}" class="text-sm font-semibold text-blue-900 underline">Kelola Berkas Lamaran</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        <x-input-error name="cv_file" />
                        <x-input-error name="ijazah_file" />
                    </div>

                    <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 transition-all duration-200 ease-in-out hover:border-blue-100">
                        <input
                            type="checkbox"
                            name="agreement"
                            value="1"
                            @checked(old('agreement'))
                            class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer rounded border-slate-300 text-blue-900 focus:ring-blue-900"
                        />
                        <span class="text-sm leading-relaxed text-slate-600">
                            Saya menyatakan bahwa seluruh data dan berkas yang saya lampirkan adalah
                            <strong class="font-semibold text-slate-900">benar, asli, dan dapat dipertanggungjawabkan</strong>.
                            Saya memahami bahwa data yang tidak benar dapat mengakibatkan lamaran saya dibatalkan.
                        </span>
                    </label>
                    <x-input-error name="agreement" />

                    <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-dashed border-slate-200 pt-5">
                        <x-button type="button" variant="secondary" x-on:click="step--">Kembali</x-button>

                        <x-button type="submit" variant="primary" size="lg">Kirim Lamaran</x-button>
                    </div>
                </x-card>
            </div>
        </form>
    </div>
</x-layouts.app>
