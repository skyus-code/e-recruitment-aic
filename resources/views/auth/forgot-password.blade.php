<x-layouts.guest title="Lupa Password">

    <div>
        <p class="eyebrow">Pemulihan Akun</p>
        <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Lupa kata sandi?</h1>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Masukkan email akun Anda. Kami akan mengirimkan <strong>kode 6 digit</strong> untuk
            mengatur ulang kata sandi. Kode berlaku singkat dan hanya bisa dipakai sekali.
        </p>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf

        <x-input
            type="email"
            name="email"
            label="Alamat Email"
            placeholder="nama@email.com"
            :value="old('email', $email)"
            required
            autofocus
        />

        <button type="submit" class="btn btn-primary btn-lg w-full">
            Kirim Kode Reset
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
