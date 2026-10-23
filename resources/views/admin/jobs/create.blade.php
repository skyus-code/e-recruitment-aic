<x-layouts.app title="Lowongan Baru">
    <div class="space-y-6">
        <x-trace
            :items="['Admin', 'Kelola Lowongan', 'Form Baru']"
            flow="ROUTE: admin.jobs.create → POST admin.jobs.store"
        />

        <div class="mx-auto w-full max-w-4xl">
            <form method="POST" action="{{ route('admin.jobs.store') }}" class="space-y-6">
                @csrf

                @if ($errors->any())
                    <x-alert variant="danger" title="Periksa kembali isian formulir">
                        <ul class="list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert>
                @endif

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
                                required
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input
                                name="title"
                                label="Judul Lowongan"
                                placeholder="contoh: Guru Pendidikan Agama Islam"
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
                            ></x-textarea>
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
                            ></x-textarea>
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
                        />

                        <x-input
                            type="number"
                            name="salary_max"
                            label="Gaji Maksimal (Rp)"
                            placeholder="contoh: 5000000"
                        />

                        <x-input
                            name="location"
                            label="Lokasi"
                            placeholder="contoh: Yogyakarta"
                            required
                        />

                        <x-select
                            name="job_type"
                            label="Tipe Pekerjaan"
                            :options="$jobTypes"
                            placeholder="Pilih tipe"
                            required
                        />

                        <x-select
                            name="status"
                            label="Status"
                            :options="$statuses"
                            placeholder="Pilih status"
                            required
                        />
                    </div>
                </x-card>

                <x-alert variant="info" title="Setelah disimpan">
                    Anda akan diarahkan ke halaman <span class="font-semibold">pengaturan bobot kata kunci NLP</span>
                    untuk formasi ini. Skor lamaran dihitung dari bobot kata kunci yang Anda konfigurasikan di sana.
                </x-alert>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <x-button href="{{ route('admin.jobs.index') }}" variant="secondary">Batal</x-button>
                    <x-button type="submit" variant="primary">Simpan Lowongan</x-button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>