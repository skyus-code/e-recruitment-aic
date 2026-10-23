<x-layouts.app title="Edit Lowongan">
    <div class="space-y-6">
        <x-trace
            :items="['Admin', 'Kelola Lowongan', 'Edit']"
            flow="ROUTE: admin.jobs.edit → PUT admin.jobs.update"
        />

        <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
            {{-- Kolom kiri: form lowongan --}}
            <div class="flex flex-col gap-6">
                @if ($errors->any())
                    <x-alert variant="danger" title="Periksa kembali isian formulir">
                        <ul class="list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert>
                @endif

                <form method="POST" action="{{ route('admin.jobs.update', $job) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <x-card>
                        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                            <div>
                                <p class="eyebrow">01 · Identitas Lowongan</p>
                                <h2 class="mt-1 text-lg font-bold text-slate-900">Formasi & Deskripsi</h2>
                                <p class="mt-1 text-sm text-slate-500">Informasi dasar yang ditampilkan kepada pelamar di katalog lowongan.</p>
                            </div>
                            <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">TABLE · job_postings</span>
                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-select
                                    name="department_id"
                                    label="Departemen"
                                    :options="$departments->pluck('name', 'id')"
                                    placeholder="Pilih departemen"
                                    :selected="$job->department_id"
                                    required
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <x-input
                                    name="title"
                                    label="Judul Lowongan"
                                    placeholder="contoh: Guru Pendidikan Agama Islam"
                                    :value="$job->title"
                                    required
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <x-textarea
                                    name="description"
                                    label="Deskripsi Pekerjaan"
                                    rows="6"
                                    placeholder="Tulis gambaran umum pekerjaan, tanggung jawab, dan lingkungan kerja..."
                                    required
                                >{{ old('description', $job->description) }}</x-textarea>
                                <p class="mt-1.5 text-xs text-slate-500">
                                    Gunakan karakter <code class="rounded bg-slate-100 px-1 font-mono text-[11px] font-semibold text-slate-700">\n</code> untuk baris baru.
                                </p>
                            </div>

                            <div class="sm:col-span-2">
                                <x-textarea
                                    name="requirements"
                                    label="Persyaratan"
                                    rows="6"
                                    placeholder="Kualifikasi yang wajib dan diharapkan dari pelamar..."
                                    required
                                >{{ old('requirements', $job->requirements) }}</x-textarea>
                                <p class="mt-1.5 text-xs text-slate-500">
                                    Gunakan karakter <code class="rounded bg-slate-100 px-1 font-mono text-[11px] font-semibold text-slate-700">\n</code> untuk baris baru.
                                </p>
                            </div>
                        </div>
                    </x-card>

                    <x-card>
                        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                            <div>
                                <p class="eyebrow">02 · Penempatan & Kompensasi</p>
                                <h2 class="mt-1 text-lg font-bold text-slate-900">Detail Penempatan</h2>
                                <p class="mt-1 text-sm text-slate-500">Lokasi, kompensasi, tipe kerja, dan status lowongan.</p>
                            </div>
                            <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">NLP NODE · READY</span>
                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <x-input
                                type="number"
                                name="salary_min"
                                label="Gaji Minimal (Rp)"
                                placeholder="contoh: 3000000"
                                :value="$job->salary_min"
                            />

                            <x-input
                                type="number"
                                name="salary_max"
                                label="Gaji Maksimal (Rp)"
                                placeholder="contoh: 5000000"
                                :value="$job->salary_max"
                            />

                            <x-input
                                name="location"
                                label="Lokasi"
                                placeholder="contoh: Yogyakarta"
                                :value="$job->location"
                                required
                            />

                            <x-select
                                name="job_type"
                                label="Tipe Pekerjaan"
                                :options="$jobTypes"
                                placeholder="Pilih tipe"
                                :selected="$job->job_type->value"
                                required
                            />

                            <x-select
                                name="status"
                                label="Status"
                                :options="$statuses"
                                placeholder="Pilih status"
                                :selected="$job->status->value"
                                required
                            />
                        </div>
                    </x-card>

                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <x-button href="{{ route('admin.jobs.index') }}" variant="secondary">Batal</x-button>
                        <x-button type="submit" variant="success">Simpan Perubahan</x-button>
                    </div>
                </form>
            </div>

            {{-- Kolom kanan: bobot kata kunci NLP --}}
            <div class="flex flex-col gap-6">
                <x-card>
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">03 · NLP Config</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Bobot Kata Kunci NLP</h2>
                            <p class="mt-1 text-sm text-slate-500">Kata kunci yang dicocokkan dengan hasil parsing CV pelamar.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-badge variant="solid-blue">{{ $job->keywords->count() }} keyword</x-badge>
                            <x-badge variant="amber">Total bobot {{ number_format($totalWeight, 2, ',', '.') }}</x-badge>
                        </div>
                    </div>

                    <div class="mt-5">
                        <x-progress :value="$totalWeight" label="Bobot terkonfigurasi" />
                        <p class="mt-2 text-xs leading-relaxed text-slate-500">
                            Bobot seluruh kata kunci diarahkan menuju skala 100 poin. Idealnya total bobot ≈ 100.
                        </p>
                    </div>

                    {{-- Form tambah kata kunci --}}
                    <form method="POST" action="{{ route('admin.jobs.keywords.store', $job) }}" class="mt-5 border-t border-dashed border-slate-200 pt-5">
                        @csrf

                        <p class="mb-3 font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">+ Tambah Kata Kunci</p>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-input
                                    name="keyword"
                                    label="Kata Kunci"
                                    placeholder="contoh: tahfidz, microteaching, public speaking"
                                    required
                                />
                            </div>

                            <x-select
                                name="category"
                                label="Kategori"
                                :options="['hard_skill' => 'Hard Skill', 'soft_skill' => 'Soft Skill']"
                                selected="hard_skill"
                                required
                            />

                            <x-input
                                type="number"
                                name="weight"
                                label="Bobot"
                                placeholder="contoh: 2.50"
                                step="0.01"
                                min="0.01"
                                max="99.99"
                                required
                            />

                            <div class="sm:col-span-2">
                                <label for="is_required" class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600">
                                    <input
                                        type="checkbox"
                                        id="is_required"
                                        name="is_required"
                                        value="1"
                                        class="h-4 w-4 rounded border-slate-300 text-blue-900 focus:ring-blue-900"
                                        @checked(old('is_required'))
                                    >
                                    Kata kunci wajib (jika tidak cocok, kandidat gugur)
                                </label>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <x-button type="submit" variant="primary" size="sm">Tambah Kata Kunci</x-button>
                        </div>
                    </form>
                </x-card>

                {{-- Tabel kata kunci --}}
                <x-card padding="p-0" class="overflow-hidden">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <p class="eyebrow">Daftar Keyword</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">{{ $job->keywords->count() }} Kata Kunci Terdaftar</h2>
                    </div>

                    @if ($job->keywords->isEmpty())
                        <div class="px-6 py-10 text-center">
                            <p class="text-sm text-slate-500">
                                Belum ada kata kunci. Tambahkan kata kunci beserta bobotnya agar pipeline NLP dapat menilai CV pelamar.
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
                                        <th class="table-head">Wajib</th>
                                        <th class="table-head">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody x-data="{ editing: null }">
                                    @foreach ($job->keywords as $kw)
                                        <tr class="table-row">
                                            <td class="table-cell">
                                                <p class="font-semibold text-slate-900">{{ $kw->keyword }}</p>
                                            </td>
                                            <td class="table-cell">
                                                <x-badge :variant="$kw->category->value === 'hard_skill' ? 'blue' : 'amber'">
                                                    {{ $kw->category->label() }}
                                                </x-badge>
                                            </td>
                                            <td class="table-cell font-mono text-xs font-bold text-slate-900">{{ $kw->weight }}</td>
                                            <td class="table-cell">
                                                @if ($kw->is_required)
                                                    <x-badge variant="red">Wajib</x-badge>
                                                @else
                                                    <span class="text-slate-300">—</span>
                                                @endif
                                            </td>
                                            <td class="table-cell">
                                                <div class="flex items-center justify-end gap-2">
                                                    <x-button
                                                        size="sm"
                                                        variant="secondary"
                                                        x-on:click="editing = editing === {{ $kw->id }} ? null : {{ $kw->id }}"
                                                    >Ubah</x-button>

                                                    <form method="POST" action="{{ route('admin.jobs.keywords.destroy', [$job, $kw]) }}" onsubmit="return confirm('Hapus kata kunci ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-button type="submit" size="sm" variant="danger">Hapus</x-button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr x-show="editing === {{ $kw->id }}" x-cloak class="bg-blue-50/40">
                                            <td colspan="5" class="table-cell">
                                                <form method="POST" action="{{ route('admin.jobs.keywords.update', [$job, $kw]) }}" class="flex flex-wrap items-end gap-3">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="min-w-40 flex-1">
                                                        <x-input
                                                            name="keyword"
                                                            label="Kata Kunci"
                                                            :value="$kw->keyword"
                                                            required
                                                        />
                                                    </div>

                                                    <div class="w-36">
                                                        <x-select
                                                            name="category"
                                                            label="Kategori"
                                                            :options="['hard_skill' => 'Hard Skill', 'soft_skill' => 'Soft Skill']"
                                                            :selected="$kw->category->value"
                                                            required
                                                        />
                                                    </div>

                                                    <div class="w-28">
                                                        <x-input
                                                            type="number"
                                                            name="weight"
                                                            label="Bobot"
                                                            step="0.01"
                                                            min="0.01"
                                                            max="99.99"
                                                            :value="$kw->weight"
                                                            required
                                                        />
                                                    </div>

                                                    <label class="flex cursor-pointer items-center gap-2 pb-2.5 text-xs font-semibold text-slate-600">
                                                        <input
                                                            type="checkbox"
                                                            name="is_required"
                                                            value="1"
                                                            class="h-4 w-4 rounded border-slate-300 text-blue-900 focus:ring-blue-900"
                                                            @checked($kw->is_required)
                                                        >
                                                        Wajib
                                                    </label>

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

                {{-- Catatan rumus --}}
                <x-card class="border-t-4 border-t-blue-900 bg-blue-900 text-white">
                    <p class="font-mono text-[10px] font-semibold tracking-[0.2em] text-blue-300 uppercase">Formula Scoring</p>
                    <p class="mt-4 font-mono text-sm leading-relaxed text-blue-100">
                        Skor = (Σ bobot cocok ÷ Σ bobot total) × 100
                    </p>
                    <p class="mt-3 text-xs leading-relaxed text-blue-200/80">
                        Panjang bobot total (denominator) dihitung dari seluruh kata kunci formasi ini; bobot cocok dihitung dari kata kunci yang ditemukan pada CV pelamar.
                    </p>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>