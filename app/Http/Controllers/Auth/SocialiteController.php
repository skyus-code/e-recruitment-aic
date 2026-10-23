<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleEnum;
use App\Enums\VerificationPurposeEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\VerificationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function __construct(
        private readonly VerificationCodeService $codes,
    ) {}

    /**
     * Pemetaan provider ke kolom penyimpanan ID OAuth-nya.
     *
     * Catatan LinkedIn: provider ini memakai OpenID Connect (socialiteproviders/
     * linkedin). Klaim "sub" dipakai sebagai ID unik dan email hanya tersedia
     * bila pengguna menyetujui scope "email", sehingga email tetap opsional.
     *
     * @var array<string, string>
     */
    private const PROVIDERS = [
        'google' => 'google_id',
        'linkedin' => 'linkedin_id',
        'facebook' => 'facebook_id',
    ];

    /**
     * Daftar provider yang didukung aplikasi (satu sumber kebenaran untuk route).
     *
     * @return list<string>
     */
    public static function supportedProviders(): array
    {
        return array_keys(self::PROVIDERS);
    }

    /**
     * Arahkan pengguna ke halaman otorisasi provider OAuth.
     */
    public function redirect(string $provider): RedirectResponse
    {
        if (! $this->isSupported($provider)) {
            return $this->unsupported($provider);
        }

        if (! $this->isConfigured($provider)) {
            return $this->notConfigured($provider);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Tangani callback provider, lalu tautkan atau buat akun pengguna.
     */
    public function callback(string $provider): RedirectResponse
    {
        if (! $this->isSupported($provider)) {
            return $this->unsupported($provider);
        }

        if (! $this->isConfigured($provider)) {
            return $this->notConfigured($provider);
        }

        try {
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (\Throwable $exception) {
            Log::error("Login OAuth {$provider} gagal.", ['exception' => $exception]);

            return redirect()->route('login')->withErrors([
                'email' => 'Login dengan '.$this->label($provider).' dibatalkan atau gagal. Silakan coba lagi.',
            ]);
        }

        $providerId = (string) $socialiteUser->getId();
        $email = $socialiteUser->getEmail();

        // Tanpa email kita tidak bisa membuat akun baru (email kolom wajib & unik).
        if (! $email && ! User::where(self::PROVIDERS[$provider], $providerId)->exists()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun '.$this->label($provider).' tidak membagikan alamat email. Silakan gunakan cara login lain.',
            ]);
        }

        $user = $this->resolveUser($provider, $providerId, $email, $socialiteUser);

        Auth::login($user, remember: true);

        // Akun yang belum terverifikasi diarahkan memverifikasi email dulu.
        if (! $user->hasVerifiedEmail()) {
            $this->codes->send($user, VerificationPurposeEnum::EmailVerification);

            return redirect()->route('verification.notice')
                ->with('status', 'Kami telah mengirim kode verifikasi ke '.$user->email.'.');
        }

        return redirect()->intended(route($user->role->dashboardRoute(), absolute: false));
    }

    /**
     * Temukan akun yang sudah ada atau buat akun pelamar baru.
     */
    private function resolveUser(string $provider, string $providerId, ?string $email, SocialiteUser $socialiteUser): User
    {
        $idColumn = self::PROVIDERS[$provider];
        $avatar = $socialiteUser->getAvatar();

        // 1) Akun yang sudah pernah ditautkan ke provider ini.
        $user = User::where($idColumn, $providerId)->first();

        // 2) Belum tertaut: cocokkan lewat email (akun hasil registrasi manual
        //    atau akun yang sebelumnya login memakai provider lain).
        if (! $user && $email) {
            $user = User::where('email', $email)->first();
        }

        if ($user) {
            // Tautkan ID provider dan perbarui avatar hanya bila datanya tersedia.
            $user->fill(array_filter([
                $idColumn => $providerId,
                'avatar' => $avatar,
            ], fn (mixed $value): bool => ! is_null($value)));

            $user->save();

            return $user;
        }

        // 3) Pengguna baru: daftar otomatis sebagai pelamar dengan kata sandi
        //    acak. password_set_at dibiarkan null karena pemiliknya belum
        //    pernah menetapkan sendiri, sehingga ia boleh membuat kata sandi
        //    baru tanpa mengisi kata sandi lama.
        $user = User::create([
            'name' => $socialiteUser->getName() ?: $socialiteUser->getNickname() ?: 'Pelamar',
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
            'role' => RoleEnum::Pelamar,
            $idColumn => $providerId,
            'avatar' => $avatar,
        ]);

        return $user;
    }

    /**
     * Pastikan nama provider termasuk daftar yang didukung.
     */
    private function isSupported(string $provider): bool
    {
        return array_key_exists($provider, self::PROVIDERS);
    }

    /**
     * Pastikan client id & secret provider sudah diisi di .env.
     */
    private function isConfigured(string $provider): bool
    {
        return (bool) config("services.{$provider}.client_id")
            && (bool) config("services.{$provider}.client_secret");
    }

    /**
     * Tangani permintaan provider yang tidak didukung.
     */
    private function unsupported(string $provider): RedirectResponse
    {
        Log::warning("Percobaan login dengan provider OAuth tak dikenal: {$provider}.");

        return redirect()->route('login')->withErrors([
            'email' => 'Metode login sosial tidak dikenali.',
        ]);
    }

    /**
     * Tangani provider yang belum dikonfigurasi pada server.
     */
    private function notConfigured(string $provider): RedirectResponse
    {
        Log::warning("OAuth {$provider} belum dikonfigurasi (client_id/client_secret kosong).");

        return redirect()->route('login')->withErrors([
            'email' => 'Login dengan '.$this->label($provider).' belum diaktifkan pada server ini.',
        ]);
    }

    /**
     * Label provider untuk ditampilkan ke pengguna.
     */
    private function label(string $provider): string
    {
        return match ($provider) {
            'google' => 'Google',
            'linkedin' => 'LinkedIn',
            'facebook' => 'Facebook',
            default => ucfirst($provider),
        };
    }
}
