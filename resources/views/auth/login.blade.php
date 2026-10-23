<x-layouts.guest title="Masuk">

    <div>
        <p class="eyebrow">Portal Autentikasi</p>
        <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Selamat datang kembali</h1>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Masuk untuk melamar formasi staff &amp; tenaga kependidikan Ar-Rahman Islamic Center.
        </p>
    </div>

    <div class="mt-8 space-y-3">
        @foreach (['google', 'linkedin', 'facebook'] as $provider)
            <x-social-button :provider="$provider" :href="route('auth.'.$provider.'.redirect')" />
        @endforeach

        <div class="flex items-center gap-4 pt-3">
            <span class="h-px flex-1 bg-slate-200"></span>
            <span class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">atau</span>
            <span class="h-px flex-1 bg-slate-200"></span>
        </div>
    </div>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <x-input
            type="email"
            name="email"
            label="Alamat Email"
            placeholder="nama@email.com"
            :value="old('email')"
            required
            autofocus
        />

        <x-input
            type="password"
            name="password"
            label="Kata Sandi"
            placeholder="••••••••"
            required
        />

        <div class="flex items-center justify-between gap-3">
            <label for="remember" class="flex cursor-pointer items-center gap-2.5 pt-1 text-sm text-slate-600">
                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    @checked(old('remember'))
                    class="h-4 w-4 rounded border-slate-300 text-blue-900 focus:ring-blue-900"
                >
                Ingat saya pada perangkat ini
            </label>

            <a
                href="{{ route('password.request') }}"
                class="shrink-0 text-sm font-semibold text-blue-900 underline decoration-blue-300 underline-offset-2 transition-colors duration-200 hover:text-blue-700"
            >
                Lupa Password?
            </a>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-full">
            Masuk ke Portal
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.6L10.72 5.53a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.63-3.64H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
            </svg>
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
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-blue-900 transition-colors duration-200 hover:text-blue-700">
            Daftar sebagai pelamar
        </a>
    </p>

</x-layouts.guest>
