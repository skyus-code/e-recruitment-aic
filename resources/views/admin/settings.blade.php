<x-layouts.app title="Master & Settings">
    <div class="space-y-8">
        <x-trace
            :items="['Admin', 'Master & Settings']"
            flow="MODULE 06 · CONF · TABLE: departments"
        />

        <x-section-header
            eyebrow="   "
            title="Master Data & Settings"
            subtitle="Ringkasan akun sistem, konfigurasi node NLP, dan pengelolaan master departemen tempat lowongan bertaut."
        />

        <div class="grid items-stretch gap-8 lg:grid-cols-2">
            {{-- Ringkasan akun --}}
            <x-card class="h-full">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Akun</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Ringkasan Akun</h2>
                            <p class="mt-1 text-sm text-slate-500">Distribusi pengguna dan jumlah data inti sistem.</p>
                        </div>
                        <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">·</span>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-4">
                        @foreach ([
                            ['TOTAL PENGGUNA', $overview['users']],
                            ['ADMIN HRD', $overview['admins']],
                            ['MANAJER', $overview['manajer']],
                            ['PELAMAR', $overview['pelamar']],
                            ['LOWONGAN', $overview['jobs']],
                            ['DEPARTEMEN', $overview['departments']],
                        ] as [$label, $value])
                            <div>
                                <dt class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">{{ $label }}</dt>
                                <dd class="mt-1 font-heading text-2xl font-extrabold text-slate-900">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
            </x-card>

            {{-- NLP node --}}
            <x-card class="h-full border-t-4 border-t-blue-900 bg-blue-900 text-white">
                    <div class="flex items-start justify-between gap-4 border-b border-blue-800 pb-4">
                        <div>
                            <p class="font-mono text-[10px] font-semibold tracking-[0.2em] text-blue-300 uppercase">NLP Node</p>
                            <h2 class="mt-1 text-lg font-bold text-white">Engine Scoring Lokal</h2>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-600/20 px-2.5 py-1 font-mono text-[10px] font-semibold tracking-wider text-emerald-300 uppercase ring-1 ring-emerald-500/30">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            Online
                        </span>
                    </div>

                    <div class="mt-4 space-y-2.5 font-mono text-[11px] leading-relaxed text-blue-100/90">
                        <p>VERSION: <span class="font-semibold text-white">V2.4-LOCAL</span></p>
                        <p>STEMMER: <span class="text-blue-200">Nazief–Adriani + Snowball EN</span></p>
                        <p>PARSER: <span class="text-blue-200">smalot/pdfparser</span></p>
                        <p>PREPROCESS: <span class="text-blue-200">casefold → tokenize → stopword → stem</span></p>
                        <p class="pt-2 border-t border-blue-800">SCORE RANGE: 0–100 · WEIGHT SCALE: 100</p>
                    </div>
            </x-card>
        </div>

                <x-card>
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Master</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Master Departemen</h2>
                            <p class="mt-1 text-sm text-slate-500">Departemen tempat setiap lowongan (formasi) dikelompokkan.</p>
                        </div>
                        <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">TABLE · departments</span>
                    </div>

                    {{-- Form tambah --}}
                    <form method="POST" action="{{ route('admin.settings.departments.store') }}" class="mt-5 grid gap-3 border-b border-dashed border-slate-200 pb-5 sm:grid-cols-2 lg:grid-cols-4 sm:items-end">
                        @csrf

                        <div class="sm:col-span-1">
                            <x-input name="name" label="Nama Departemen" placeholder="cth: Tarbiyah" required />
                        </div>
                        <div>
                            <x-input name="address" label="Alamat" placeholder="Alamat kantor" />
                        </div>
                        <div>
                            <x-input type="email" name="email" label="Email" placeholder="departemen@ar-rahman.ac.id" />
                        </div>

                        <div class="shrink-0">
                            <x-button type="submit" variant="primary" class="w-full">+ Tambah Departemen</x-button>
                        </div>
                    </form>

                    {{-- Tabel departemen --}}
                    @if ($departments->isEmpty())
                        <p class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                            Belum ada departemen terdaftar. Tambahkan departemen pertama melalui formulir di atas.
                        </p>
                    @else
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr>
                                        <th class="table-head">Nama</th>
                                        <th class="table-head">Alamat</th>
                                        <th class="table-head">Email</th>
                                        <th class="table-head">Lowongan</th>
                                        <th class="table-head">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody x-data="{ editing: null }">
                                    @foreach ($departments as $d)
                                        <tr class="table-row">
                                            <td class="table-cell">
                                                <p class="font-semibold text-slate-900">{{ $d->name }}</p>
                                            </td>
                                            <td class="table-cell text-xs text-slate-600">{{ $d->address ?? '—' }}</td>
                                            <td class="table-cell font-mono text-xs text-slate-500">{{ $d->email ?? '—' }}</td>
                                            <td class="table-cell">
                                                <span class="font-mono text-xs font-bold text-slate-900">{{ $d->jobs_count }}</span>
                                                <span class="text-slate-400"> lowongan</span>
                                            </td>
                                            <td class="table-cell">
                                                <div class="flex items-center justify-end gap-2">
                                                    <x-button
                                                        size="sm"
                                                        variant="secondary"
                                                        x-on:click="editing = editing === {{ $d->id }} ? null : {{ $d->id }}"
                                                    >Ubah</x-button>

                                                    <form method="POST" action="{{ route('admin.settings.departments.destroy', $d) }}" onsubmit="return confirm('Hapus departemen?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-button type="submit" size="sm" variant="danger">Hapus</x-button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr x-show="editing === {{ $d->id }}" x-cloak class="bg-blue-50/40">
                                            <td colspan="5" class="table-cell">
                                                <form method="POST" action="{{ route('admin.settings.departments.update', $d) }}" class="grid gap-3 md:grid-cols-[1.2fr_1.2fr_1.2fr_auto] md:items-end">
                                                    @csrf
                                                    @method('PUT')

                                                    <x-input name="name" label="Nama Departemen" :value="$d->name" required />
                                                    <x-input name="address" label="Alamat" :value="$d->address" />
                                                    <x-input type="email" name="email" label="Email" :value="$d->email" />

                                                    <div class="flex items-center gap-2 pb-1">
                                                        <x-button type="submit" variant="success" size="sm">Simpan</x-button>
                                                        <x-button size="sm" variant="ghost" x-on:click="editing = null">Batal</x-button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-card>

        {{-- Input keyword full-width --}}
        <x-card>
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <p class="eyebrow">Keyword</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Input Keyword Skoring</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Kata kunci beserta bobot &amp; kategori yang dipakai pipeline screening untuk menilai CV pelamar.
                    </p>
                </div>
                <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">TABLE · job_keywords</span>
            </div>

            <form method="POST" action="{{ route('admin.keywords.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-6 lg:items-end">
                @csrf

                <div class="sm:col-span-2 lg:col-span-2">
                    <x-select
                        name="job_id"
                        label="Lowongan"
                        :options="$jobs->mapWithKeys(fn ($job) => [$job->id => $job->title])->all()"
                        placeholder="Pilih lowongan"
                        required
                    />
                </div>
                <div class="lg:col-span-1">
                    <x-input name="keyword" label="Keyword" placeholder="cth: fikih" required />
                </div>
                <div class="lg:col-span-1">
                    <x-input type="number" name="weight" label="Bobot (1–100)" :value="old('weight', 1)" required />
                </div>
                <div class="lg:col-span-1">
                    <x-input name="category" label="Kategori" placeholder="cth: hard_skill" />
                </div>
                <div class="lg:col-span-1">
                    <x-button type="submit" variant="primary" class="w-full">+ Simpan</x-button>
                </div>
            </form>

            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-dashed border-slate-200 pt-4">
                <p class="text-xs text-slate-500">Atur bobot, kategori, dan hapus keyword lewat halaman kelola.</p>
                <x-button href="{{ route('admin.keywords.index') }}" variant="secondary" size="sm">
                    Kelola Semua Keyword
                </x-button>
            </div>
        </x-card>
    </div>
</x-layouts.app>
