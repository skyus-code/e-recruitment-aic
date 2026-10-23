<x-layouts.guest title="Daftar">

    <div>
        <p class="eyebrow">Portal Autentikasi</p>
        <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Buat akun pelamar</h1>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Satu akun untuk seluruh formasi. Data master yang Anda isi akan otomatis
            dilampirkan ke setiap lamaran.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <x-input
            name="name"
            label="Nama Lengkap"
            placeholder="contoh: Ahmad Fadhil"
            :value="old('name')"
            required
            autofocus
        />

        <x-input
            type="email"
            name="email"
            label="Alamat Email"
            placeholder="nama@email.com"
            :value="old('email')"
            required
        />

        <x-input
            type="password"
            name="password"
            label="Kata Sandi"
            placeholder="Minimal 8 karakter"
            hint="Gunakan kombinasi huruf dan angka."
            required
        />

        <x-input
            type="password"
            name="password_confirmation"
            label="Konfirmasi Kata Sandi"
            placeholder="Ulangi kata sandi"
            required
        />

        <button type="submit" class="btn btn-primary btn-lg w-full">
            Daftar Sekarang
        </button>

        @if ($errors->any())
            <div class="space-y-2">
                @foreach ($errors->all() as $error)
                    <p class="rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs font-medium text-red-600">
                        {{ $error }}
                    </p>
                @endforeach
            </div>
        @endif
    </form>

    <p class="mt-4 text-center text-xs leading-relaxed text-slate-500">
        Kami akan mengirimkan <strong>kode verifikasi 6 digit</strong> ke email Anda untuk mengaktifkan akun.
    </p>

    <div class="mt-6 rounded-xl border border-blue-100 bg-blue-50 p-4">
        <p class="font-mono text-[10px] font-semibold tracking-widest text-blue-900 uppercase">Catatan akses</p>
        <p class="mt-1.5 text-xs leading-relaxed text-blue-900/80">
            Akun <strong>Admin HRD</strong> dan <strong>Manajer</strong> dibuat oleh sistem
            dan dibagikan oleh Yayasan Generasi Cerdas Akhlak Mulia — tidak tersedia melalui pendaftaran mandiri.
        </p>
    </div>

    <p class="mt-8 border-t border-slate-100 pt-6 text-center text-sm text-slate-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-blue-900 transition-colors duration-200 hover:text-blue-700">
            Masuk di sini
        </a>
    </p>

</x-layouts.guest>
