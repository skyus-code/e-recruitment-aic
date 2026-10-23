<x-layouts.app title="Profil & Master Data">
    @php
        $detail = $user->detailPerson;

        $completionChecks = [
            'Identitas & data diri' => (bool) ($user->phone && $detail?->gender && $detail?->birth_date && $detail?->full_address),
            'Riwayat pendidikan' => $user->educations->isNotEmpty(),
            'Pengalaman kerja' => $user->experiences->isNotEmpty(),
            'Keahlian' => $user->skills->isNotEmpty(),
        ];
        // Rekalkulasi agar jika sebagian kosong, persentase menurun tepat
        $totalChecks = count($completionChecks);
        $completedItems = 0;
        foreach ($completionChecks as $val) {
            if ($val) $completedItems++;
        }
        $completionPct = $totalChecks > 0 ? (int) round(($completedItems / $totalChecks) * 100) : 0;
        $completedItems = count(array_filter($completionChecks));
        $completionPct = (int) round($completedItems / count($completionChecks) * 100);
    @endphp

    <div class="space-y-6">
        <x-trace :items="['Pelamar', 'Profil & Master Data']" flow="MASTER DATA → DIPAKAI DI SEMUA LAMARAN" />

        <x-section-header
            eyebrow="Portal Pelamar"
            title="Profil & Master Data"
            subtitle="Data yang Anda isi di halaman ini dipakai sekali dan otomatis dilampirkan ke setiap lamaran berikutnya — tidak perlu mengisi ulang."
        >
            <x-slot name="actions">
                <span class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">
                    KELENGKAPAN {{ $completionPct }}%
                </span>
            </x-slot>
        </x-section-header>

        @if ($errors->any())
            <div class="space-y-2">
                @foreach ($errors->all() as $error)
                    <p class="rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs font-medium text-red-600">
                        {{ $error }}
                    </p>
                @endforeach
            </div>
        @endif

        {{-- Kelengkapan profil --}}
        <x-card>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="font-mono text-[10px] font-semibold tracking-[0.15em] text-slate-500 uppercase">
                        Kelengkapan Profil
                    </p>
                    <p class="mt-1.5 text-sm text-slate-500">
                        {{ $completedItems }}/{{ count($completionChecks) }} blok data sudah terisi.
                    </p>
                </div>

                <div class="w-full sm:max-w-xs">
                    <x-progress :value="$completionPct" label="Kelengkapan" size="sm" />
                </div>
            </div>

            <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($completionChecks as $label => $filled)
                    <div @class([
                        'flex items-center gap-2 rounded-lg border px-3 py-2.5 text-xs font-semibold transition-all duration-200 ease-in-out',
                        'border-emerald-200 bg-emerald-50 text-emerald-700' => $filled,
                        'border-slate-200 bg-slate-50 text-slate-500' => ! $filled,
                    ])>
                        <span @class([
                            'flex h-4 w-4 shrink-0 items-center justify-center rounded-full text-[10px] font-bold',
                            'bg-emerald-600 text-white' => $filled,
                            'bg-slate-300 text-white' => ! $filled,
                        ])>{{ $filled ? '✓' : '!' }}</span>
                        {{ $label }}
                    </div>
                @endforeach
            </div>
        </x-card>

        {{-- 01 · Identitas & Data Diri --}}
        <x-card>
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <p class="eyebrow">01 · Identitas</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Identitas & Data Diri</h2>
                    <p class="mt-1 text-sm text-slate-500">Data utama yang muncul di setiap berkas lamaran.</p>
                </div>
                <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">TABLE · users</span>
            </div>

            <form method="POST" action="{{ route('pelamar.profile.update') }}" class="mt-5">
                @csrf
                @method('PUT')

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input
                        name="name"
                        label="Nama Lengkap"
                        placeholder="cth: Ahmad Fadhil Rahman"
                        :value="$user->name"
                        required
                    />

                    <x-select
                        name="gender"
                        label="Jenis Kelamin"
                        placeholder="Pilih jenis kelamin"
                        :options="['Laki-laki' => 'Laki-laki', 'Perempuan' => 'Perempuan']"
                        :selected="$detail?->gender"
                    />

                    <x-input
                        name="birth_place"
                        label="Tempat Lahir"
                        placeholder="cth: Yogyakarta"
                        :value="$detail?->birth_place"
                    />

                    <x-input
                        type="date"
                        name="birth_date"
                        label="Tanggal Lahir"
                        :value="$detail?->birth_date?->format('Y-m-d')"
                    />

                    <x-input
                        type="tel"
                        name="phone"
                        label="Nomor WhatsApp"
                        placeholder="081234567890"
                        hint="Format: 08xxxxxxxxxx"
                        :value="$user->phone"
                    />

                    <div class="hidden sm:block"></div>

                    <div class="sm:col-span-2">
                        <x-textarea name="full_address" label="Alamat Lengkap" rows="3" placeholder="Alamat domisili saat ini">{{ $detail?->full_address ?? '' }}</x-textarea>
                    </div>
                </div>

                <div class="mt-5 flex justify-end border-t border-dashed border-slate-200 pt-5">
                    <x-button type="submit" variant="success">Simpan Data Diri</x-button>
                </div>
            </form>
        </x-card>

        {{-- 02 · Riwayat Pendidikan --}}
        <x-card>
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <p class="eyebrow">02 · Pendidikan</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Riwayat Pendidikan</h2>
                    <p class="mt-1 text-sm text-slate-500">Formal terakhir lebih dulu — digunakan untuk penilaian berkas.</p>
                </div>
                <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                    {{ $user->educations->count() }} baris
                </span>
            </div>

            <div class="mt-5">
                @if ($user->educations->isEmpty())
                    <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                        Belum ada riwayat pendidikan. Tambahkan minimal satu jenjang pendidikan.
                    </p>
                @else
                    <ul class="space-y-3">
                        @foreach ($user->educations as $education)
                            <li class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 transition-all duration-200 ease-in-out hover:border-blue-100 hover:shadow-sm sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="truncate font-heading text-sm font-bold text-slate-900">
                                        {{ $education->institution }}
                                    </p>
                                    <p class="mt-0.5 text-sm text-slate-500">
                                        @if ($education->degree || $education->major)
                                            {{ collect([$education->degree, $education->major])->filter()->implode(' — ') }}
                                        @else
                                            Jenjang belum diisi
                                        @endif
                                    </p>
                                    <p class="mt-1.5 font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                                        {{ $education->start_year ? $education->start_year.'–'.($education->end_year ?? 'sekarang') : 'Tahun belum diisi' }}
                                        @if ($education->gpa !== null)
                                            · IPK {{ number_format((float) $education->gpa, 2) }}
                                        @endif
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('pelamar.profile.educations.destroy', $education) }}" class="shrink-0" onsubmit="return confirm('Hapus riwayat pendidikan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="ghost" size="sm">Hapus</x-button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div x-data="{ showForm: false }" class="mt-5">
                <button
                    type="button"
                    x-on:click="showForm = !showForm"
                    class="flex w-full cursor-pointer items-center justify-between gap-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3.5 text-left text-sm font-semibold text-blue-900 transition-all duration-200 ease-in-out hover:border-blue-900 hover:bg-blue-100"
                >
                    <span class="inline-flex items-center gap-2">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-900 text-xs font-bold text-white transition-transform duration-200" :class="showForm ? 'rotate-45' : ''">+</span>
                        Tambah Pendidikan
                    </span>
                    <span class="font-mono text-[10px] tracking-widest text-slate-400 uppercase" x-text="showForm ? 'Tutup' : 'Buka'">Buka</span>
                </button>

                <form
                    method="POST"
                    action="{{ route('pelamar.profile.educations.store') }}"
                    x-show="showForm"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="mt-3 rounded-xl border border-slate-200 bg-slate-50/70 p-5"
                >
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <x-input name="institution" label="Institusi / Perguruan Tinggi" placeholder="cth: UIN Sunan Kalijaga" required />

                        <x-input name="degree" label="Jenjang" placeholder="cth: S-1" />

                        <x-input name="major" label="Jurusan / Prodi" placeholder="cth: Pendidikan Agama Islam" />

                        <x-input type="number" name="start_year" label="Tahun Mulai" placeholder="cth: 2018" />

                        <x-input type="number" name="end_year" label="Tahun Selesai" placeholder="cth: 2022" />

                        <div class="w-full">
                            <label for="gpa" class="label">IPK</label>
                            <input
                                type="number"
                                name="gpa"
                                id="gpa"
                                value="{{ old('gpa') }}"
                                placeholder="cth: 3.45"
                                step="0.01"
                                min="0"
                                max="4"
                                class="input"
                            />
                            <p class="mt-1.5 text-xs text-slate-500">Skala 0.00 – 4.00.</p>
                            <x-input-error name="gpa" />
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <x-button type="submit" variant="primary" size="sm">Simpan Pendidikan</x-button>
                    </div>
                </form>
            </div>
        </x-card>

        {{-- 03 · Pengalaman Kerja --}}
        <x-card>
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <p class="eyebrow">03 · Pengalaman</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Pengalaman Kerja</h2>
                    <p class="mt-1 text-sm text-slate-500">Riwayat pekerjaan, magang, atau pengabdian yang relevan.</p>
                </div>
                <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                    {{ $user->experiences->count() }} baris
                </span>
            </div>

            <div class="mt-5">
                @if ($user->experiences->isEmpty())
                    <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                        Belum ada pengalaman kerja. Anda tetap bisa melamar tanpa pengalaman, namun skor kecocokan akan lebih rendah.
                    </p>
                @else
                    <ul class="space-y-3">
                        @foreach ($user->experiences as $experience)
                            <li class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 transition-all duration-200 ease-in-out hover:border-blue-100 hover:shadow-sm sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <p class="font-heading text-sm font-bold text-slate-900">
                                        {{ $experience->position }} <span class="font-normal text-slate-400">·</span>
                                        <span class="text-slate-700">{{ $experience->company }}</span>
                                    </p>

                                    <p class="mt-1 font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                                        {{ $experience->start_date?->translatedFormat('d F Y') ?? '—' }}
                                        —
                                        {{ $experience->end_date?->translatedFormat('d F Y') ?? 'Sekarang' }}
                                    </p>

                                    @if ($experience->description)
                                        <p class="mt-2 text-sm leading-relaxed text-slate-500">{!! nl2br(e($experience->description)) !!}</p>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('pelamar.profile.experiences.destroy', $experience) }}" class="shrink-0" onsubmit="return confirm('Hapus pengalaman kerja ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="ghost" size="sm">Hapus</x-button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div x-data="{ showForm: false }" class="mt-5">
                <button
                    type="button"
                    x-on:click="showForm = !showForm"
                    class="flex w-full cursor-pointer items-center justify-between gap-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3.5 text-left text-sm font-semibold text-blue-900 transition-all duration-200 ease-in-out hover:border-blue-900 hover:bg-blue-100"
                >
                    <span class="inline-flex items-center gap-2">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-900 text-xs font-bold text-white transition-transform duration-200" :class="showForm ? 'rotate-45' : ''">+</span>
                        Tambah Pengalaman
                    </span>
                    <span class="font-mono text-[10px] tracking-widest text-slate-400 uppercase" x-text="showForm ? 'Tutup' : 'Buka'">Buka</span>
                </button>

                <form
                    method="POST"
                    action="{{ route('pelamar.profile.experiences.store') }}"
                    x-show="showForm"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="mt-3 rounded-xl border border-slate-200 bg-slate-50/70 p-5"
                >
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input name="company" label="Nama Instansi / Perusahaan" placeholder="cth: MA Ar-Rahman" required />

                        <x-input name="position" label="Jabatan" placeholder="cth: Guru Mata Pelajaran" required />

                        <x-input type="date" name="start_date" label="Tanggal Mulai" required />

                        <x-input type="date" name="end_date" label="Tanggal Selesai (kosongkan bila masih berjalan)" />

                        <div class="sm:col-span-2">
                            <x-textarea name="description" label="Deskripsi Pekerjaan" rows="3" placeholder="Tugas dan tanggung jawab utama..."></x-textarea>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <x-button type="submit" variant="primary" size="sm">Simpan Pengalaman</x-button>
                    </div>
                </form>
            </div>
        </x-card>

        {{-- 04 · Keahlian --}}
        <x-card>
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <p class="eyebrow">04 · Keahlian</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Keahlian</h2>
                    <p class="mt-1 text-sm text-slate-500">Kata kunci keahlian yang akan dicocokkan dengan bobot formasi.</p>
                </div>
                <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                    {{ $user->skills->count() }} skill
                </span>
            </div>

            <form method="POST" action="{{ route('pelamar.profile.skills.store') }}" class="mt-5 space-y-4 border-t border-dashed border-slate-200 pt-5" x-data="{ skills: @js($user->skills->pluck('skill_name')->values()), draft: '', addMany(values) { values.forEach((value) => { const skill = value.trim(); if (skill && skill.length <= 80 && this.skills.length < 30 && !this.skills.some((item) => item.toLocaleLowerCase() === skill.toLocaleLowerCase())) this.skills.push(skill); }); }, addDraft() { this.addMany([this.draft]); this.draft = ''; }, paste(event) { const text = event.clipboardData.getData('text'); if (text.includes(',') || text.includes('\n') || text.includes('\r')) { event.preventDefault(); this.addMany(text.split(/[,\r\n]+/)); } } }">
                @csrf

                <div class="flex flex-wrap gap-2">
                    <template x-for="(skill, index) in skills" :key="skill.toLocaleLowerCase()">
                        <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 py-1 pr-1 pl-3 text-xs font-semibold text-blue-900">
                            <span x-text="skill"></span>
                            <input type="hidden" name="skills[]" :value="skill">
                            <button type="button" class="flex h-5 w-5 items-center justify-center rounded-full bg-white text-slate-500 hover:bg-red-600 hover:text-white" x-on:click="skills.splice(index, 1)" x-bind:aria-label="'Hapus keahlian ' + skill">&times;</button>
                        </span>
                    </template>
                </div>

                <div>
                    <label for="skill-input" class="label">Tambah Keahlian</label>
                    <input id="skill-input" type="text" x-model="draft" x-on:keydown.enter.prevent="addDraft()" x-on:paste="paste($event)" maxlength="80" class="input" placeholder="Ketik lalu tekan Enter; tempel daftar dipisahkan koma atau baris baru" autocomplete="off">
                    <p class="mt-1 text-xs text-slate-500">Maksimal 30 keahlian, 80 karakter per keahlian. Enter tidak mengirim formulir.</p>
                    <x-input-error name="skills" />
                    <x-input-error name="skills.*" />
                </div>

                <div class="flex justify-end">
                    <x-button type="submit" variant="primary">Simpan</x-button>
                </div>
            </form>
        </x-card>

    </div>
</x-layouts.app>
