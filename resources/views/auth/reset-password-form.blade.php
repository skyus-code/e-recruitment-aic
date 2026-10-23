<x-layouts.guest title="Kata Sandi Baru">

    <div>
        <p class="eyebrow">Pemulihan Akun</p>
        <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Tentukan kata sandi baru</h1>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Kode Anda sudah diterima. Sekarang tentukan kata sandi baru untuk akun
            <span class="font-semibold text-slate-700">{{ $email }}</span>.
        </p>
    </div>

    <form method="POST" action="{{ route('password.reset.store', ['token' => $token]) }}" class="mt-8 space-y-5">
        @csrf

        <x-input
            type="password"
            name="password"
            label="Kata Sandi Baru"
            placeholder="Minimal 8 karakter"
            hint="Gunakan kombinasi huruf dan angka yang tidak dipakai di layanan lain."
            autocomplete="new-password"
            required
            autofocus
        />

        <x-input
            type="password"
            name="password_confirmation"
            label="Ulangi Kata Sandi Baru"
            placeholder="Ulangi kata sandi"
            autocomplete="new-password"
            required
        />

        <button type="submit" class="btn btn-primary btn-lg w-full">
            Simpan Kata Sandi Baru
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

    <p class="mt-8 border-t border-slate-100 pt-6 text-center text-sm text-slate-500">
        <a href="{{ route('login') }}" class="font-semibold text-blue-900 transition-colors duration-200 hover:text-blue-700">
            Kembali ke halaman masuk
        </a>
    </p>

</x-layouts.guest>
