<footer class="border-t border-blue-800 bg-blue-900 text-blue-100">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-3">
            <div class="md:col-span-2">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-white p-0.5">
                        <img src="{{ asset('logo-aic.png') }}" alt="Ar-Rahman Islamic Center" class="h-full w-full object-contain">
                    </span>
                    <span class="leading-tight">
                        <span class="block font-heading text-sm font-bold text-white">Ar-Rahman Islamic Center</span>
                        <span class="block font-mono text-[10px] tracking-[0.2em] text-blue-300 uppercase">E-Recruitment System</span>
                    </span>
                </div>

                <p class="mt-5 max-w-md text-sm leading-relaxed text-blue-200">
                    Unit kerja di bawah Yayasan Generasi Cerdas Akhlak Mulia. Sistem ini mengelola
                    rekrutmen guru dan tenaga kependidikan secara terukur, transparan dan adil.
                </p>
            </div>

            <div>
                <p class="font-mono text-[10px] font-semibold tracking-[0.2em] text-blue-300 uppercase">Navigasi</p>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('jobs.index') }}" class="transition-colors duration-200 hover:text-white">Lowongan Tersedia</a></li>
                    <li><a href="{{ route('register') }}" class="transition-colors duration-200 hover:text-white">Daftar Pelamar</a></li>
                    <li><a href="{{ route('login') }}" class="transition-colors duration-200 hover:text-white">Masuk Portal</a></li>
                </ul>
            </div>

            <div>
                <p class="font-mono text-[10px] font-semibold tracking-[0.2em] text-blue-300 uppercase">Panduan</p>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('register') }}" class="transition-colors duration-200 hover:text-white">Isi data diri di Profil</a></li>
                    <li><a href="{{ route('jobs.index') }}" class="transition-colors duration-200 hover:text-white">Kirim berkas lamaran</a></li>
                    <li><a href="{{ route('login') }}" class="transition-colors duration-200 hover:text-white">Pantau hasil seleksi</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-2 border-t border-blue-800 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-blue-300">
                © {{ date('Y') }} Yayasan Generasi Cerdas Akhlak Mulia — Ar-Rahman Islamic Center.
            </p>
            <p class="font-mono text-[10px] tracking-wider text-blue-400 uppercase">Sistem Informasi Rekrutmen v1.0.0</p>
        </div>
    </div>
</footer>
