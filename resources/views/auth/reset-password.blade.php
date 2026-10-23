<x-layouts.guest title="Kode Reset Password">

    <div>
        <p class="eyebrow">Pemulihan Akun</p>
        <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Masukkan kode</h1>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Jika email terdaftar, kami sudah mengirimkan kode 6 digit ke
            <span class="font-semibold text-slate-700">{{ $email ?: 'alamat email Anda' }}</span>.
            Masukkan kode tersebut untuk melanjutkan.
        </p>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.code.verify') }}" class="mt-8 space-y-5">
        @csrf

        <x-input
            name="code"
            label="Kode 6 Digit"
            placeholder="Contoh: 123456"
            maxlength="6"
            inputmode="numeric"
            autocomplete="one-time-code"
            hint="Kode berlaku selama {{ $expiresInMinutes }} menit dan hanya bisa dipakai satu kali."
            required
            autofocus
        />

        <button type="submit" class="btn btn-primary btn-lg w-full">
            Verifikasi Kode
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

    {{-- Kirim ulang kode dengan cooldown --}}
    <div
        x-data="{
            cooldown: {{ $resendCooldown }},
            init() {
                if (this.cooldown > 0) {
                    const timer = setInterval(() => {
                        this.cooldown -= 1;
                        if (this.cooldown <= 0) clearInterval(timer);
                    }, 1000);
                }
            },
        }"
        class="mt-6"
    >
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <input type="hidden" name="email" value="{{ old('email', $email) }}">

            <p class="text-center text-sm text-slate-500">
                Tidak menerima kode?
                <button
                    type="submit"
                    x-bind:disabled="cooldown > 0"
                    class="font-semibold text-blue-900 underline decoration-blue-300 underline-offset-2 hover:text-blue-700 disabled:cursor-not-allowed disabled:text-slate-400 disabled:no-underline"
                >
                    <span x-show="cooldown === 0">Kirim ulang kode</span>
                    <span x-show="cooldown > 0" x-cloak>Kirim ulang kode (@{{ cooldown }} dtk)</span>
                </button>
            </p>
        </form>
    </div>

    <p class="mt-8 border-t border-slate-100 pt-6 text-center text-sm text-slate-500">
        <a href="{{ route('login') }}" class="font-semibold text-blue-900 transition-colors duration-200 hover:text-blue-700">
            Kembali ke halaman masuk
        </a>
    </p>

</x-layouts.guest>
