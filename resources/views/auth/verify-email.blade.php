<x-layouts.guest title="Verifikasi Email">

    <div>
        <p class="eyebrow">Verifikasi Akun</p>
        <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Masukkan kode verifikasi</h1>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Kami telah mengirim kode 6 digit ke
            <span class="font-semibold text-slate-700">{{ $email }}</span>.
            Masukkan kode tersebut untuk mengaktifkan akun dan membuka dasbor Anda.
        </p>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.verify') }}" class="mt-8 space-y-5">
        @csrf

        <x-input
            name="code"
            label="Kode Verifikasi"
            placeholder="Contoh: 123456"
            maxlength="6"
            inputmode="numeric"
            autocomplete="one-time-code"
            hint="Kode berlaku selama 15 menit."
            required
            autofocus
        />

        <button type="submit" class="btn btn-primary btn-lg w-full">
            Verifikasi &amp; Masuk Dasbor
        </button>
    </form>

    <form method="POST" action="{{ route('verification.send') }}" class="mt-4 text-center">
        @csrf
        <p class="text-sm text-slate-500">
            Tidak menerima kode?
            <button type="submit" class="font-semibold text-blue-900 underline decoration-blue-300 underline-offset-2 hover:text-blue-700">
                Kirim ulang kode
            </button>
        </p>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-8 text-center">
        @csrf
        <button type="submit" class="text-xs font-medium text-slate-400 hover:text-slate-600">
            Keluar
        </button>
    </form>

</x-layouts.guest>
