@props([
    'user' => null,
    'linkedProviders' => [],
    'redirectTo' => null,
])

@php
    $user ??= auth()->user();

    // Bila akun hanya dibuat lewat OAuth (kata sandi acak), pengguna tidak
    // mengetahui kata sandi lamanya sehingga cukup mengisi kata sandi baru.
    $isOAuthOnly = $user !== null
        && $user->password_set_at === null
        && $user->hasLinkedSocialAccount();
@endphp

<div {{ $attributes->merge(['class' => 'card p-6']) }}>
    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
        <div>
            <p class="eyebrow">Keamanan Akun</p>
            <h2 class="mt-1 text-lg font-bold text-slate-900">
                {{ $isOAuthOnly ? 'Buat Kata Sandi' : 'Ganti Kata Sandi' }}
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                @if ($isOAuthOnly)
                    Akun ini dibuat lewat {{ $linkedProviders ? implode(' / ', $linkedProviders) : 'login sosial' }}. Buat kata sandi agar Anda juga bisa masuk memakai email dan kata sandi.
                @else
                    Gunakan kata sandi yang kuat dan tidak dipakai di layanan lain.
                @endif
            </p>
        </div>
        <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block"> · </span>
    </div>

    @if ($errors->hasAny($isOAuthOnly ? ['password'] : ['current_password', 'password']))
        <div class="mt-4">
            <x-alert variant="danger" title="Kata sandi belum dapat disimpan">
                Periksa kembali isian di bawah ini.
            </x-alert>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="mt-5">
        @csrf
        @method('PUT')

        @if ($redirectTo)
            <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            @unless ($isOAuthOnly)
                <div class="sm:col-span-2">
                    <x-input
                        type="password"
                        name="current_password"
                        label="Kata Sandi Saat Ini"
                        autocomplete="current-password"
                        required
                    />
                </div>
            @endunless

            <x-input
                type="password"
                name="password"
                label="Kata Sandi Baru"
                hint="Minimal 8 karakter."
                autocomplete="new-password"
                required
            />

            <x-input
                type="password"
                name="password_confirmation"
                label="Ulangi Kata Sandi Baru"
                autocomplete="new-password"
                required
            />
        </div>

        <div class="mt-5 flex justify-end border-t border-dashed border-slate-200 pt-5">
            <x-button type="submit" variant="success">Simpan Kata Sandi</x-button>
        </div>
    </form>
</div>
