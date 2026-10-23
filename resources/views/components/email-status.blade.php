@props([
    'user' => null,
    'resendCooldown' => null,
    'expiresInMinutes' => null,
])

@php
    use App\Enums\VerificationPurposeEnum;
    use App\Services\VerificationCodeService;

    $user ??= auth()->user();

    $isVerified = $user->hasVerifiedEmail();

    // Bila cooldown tidak diberikan, dihitung dari kode terakhir yang dikirim
    // agar tombol "Kirim Ulang" konsisten di semua halaman.
    if ($resendCooldown !== null) {
        $cooldown = (int) $resendCooldown;
    } elseif ($isVerified) {
        $cooldown = 0;
    } else {
        $cooldown = app(VerificationCodeService::class)
            ->secondsUntilResend($user, VerificationPurposeEnum::EmailVerification);
    }

    $minutes = $expiresInMinutes ?? VerificationCodeService::expiresInMinutes();
@endphp

{{--
    Komponen status email yang dipakai bersama oleh halaman Pengaturan Akun.
    Sumber kebenaran adalah kolom users.email_verified_at di database, bukan
    state di sisi klien.
--}}
<div {{ $attributes->merge(['class' => 'card p-6']) }}>
    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
        <div>
            <p class="eyebrow">Status Email</p>
            <h2 class="mt-1 text-lg font-bold text-slate-900">Verifikasi Alamat Email</h2>
            <p class="mt-1 text-sm text-slate-500">
                Status verifikasi dibaca langsung dari basis data. Email baru terverifikasi
                setelah kode 6 digit yang kami kirim benar-benar Anda masukkan.
            </p>
        </div>
        <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block"> · </span>
    </div>

    {{-- Alamat email & badge status --}}
    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <p class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Alamat Email</p>
            <p class="mt-1 truncate text-sm font-semibold text-slate-700">{{ $user->email }}</p>
        </div>

        @if ($isVerified)
            <x-badge variant="emerald">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                </svg>
                Terverifikasi
            </x-badge>
        @else
            <x-badge variant="amber">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.168 2.625-1.515 2.625H3.72c-1.347 0-2.188-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                </svg>
                Belum Terverifikasi
            </x-badge>
        @endif
    </div>

    @if ($isVerified)
        <div class="mt-5 border-t border-dashed border-slate-200 pt-5">
            <p class="text-sm text-slate-500">
                Email ini terverifikasi pada
                <span class="font-semibold text-slate-700">{{ $user->email_verified_at->format('d F Y H:i') }}</span>.
                Kode verifikasi tidak lagi diperlukan.
            </p>
        </div>
    @else
        {{-- Penjelasan cara memverifikasi --}}
        <div class="mt-5">
            <x-alert variant="warning" title="Belum terverifikasi">
                Untuk melamar pekerjaan, email ini wajib diverifikasi. Ikuti langkah berikut:
                <ol class="mt-2 list-decimal space-y-1 pl-5">
                    <li>Klik <strong>Kirim Kode Verifikasi</strong> di bawah ini.</li>
                    <li>Buka email <strong>{{ $user->email }}</strong> dan catat kode 6 digitnya.</li>
                    <li>Masukkan kode tersebut, lalu tekan <strong>Verifikasi</strong>.</li>
                </ol>
                Kode berlaku {{ $minutes }} menit dan hanya bisa dipakai satu kali.
            </x-alert>
        </div>

        {{-- Formulir verifikasi --}}
        <div
            x-data="{
                sending: false,
                cooldown: {{ $cooldown }},
                init() {
                    if (this.cooldown > 0) {
                        const timer = setInterval(() => {
                            this.cooldown -= 1;
                            if (this.cooldown <= 0) clearInterval(timer);
                        }, 1000);
                    }
                },
            }"
            class="mt-5"
        >
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ route('password.edit') }}">
                <button
                    type="submit"
                    x-bind:disabled="cooldown > 0"
                    class="btn btn-primary w-full sm:w-auto disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span x-show="cooldown === 0">Kirim Kode Verifikasi</span>
                    <span x-show="cooldown > 0" x-cloak>Kirim Ulang Kode (@{{ cooldown }} dtk)</span>
                </button>
            </form>

            <form method="POST" action="{{ route('verification.verify') }}" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ route('password.edit') }}">

                <x-input
                    name="code"
                    label="Kode Verifikasi"
                    placeholder="Contoh: 123456"
                    maxlength="6"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    hint="Masukkan 6 digit kode yang kami kirim ke email Anda."
                    required
                />

                <div class="flex justify-end border-t border-dashed border-slate-200 pt-4">
                    <x-button type="submit" variant="success">Verifikasi</x-button>
                </div>
            </form>
        </div>
    @endif
</div>
