<?php

namespace App\Http\Controllers\Auth;

use App\Enums\VerificationPurposeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetCodeRequest;
use App\Http\Requests\Auth\PasswordResetEmailRequest;
use App\Http\Requests\Auth\PasswordResetUpdateRequest;
use App\Models\User;
use App\Services\VerificationCodeService;
use App\Services\VerificationResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Alur "Lupa Password" berbasis kode OTP 6 digit.
 *
 * Tahapan:
 *  1) Masukkan email -> kode dikirim. Respons selalu identik agar tidak
 *     membocorkan apakah email terdaftar (anti user-enumeration).
 *  2) Masukkan kode -> bila sah, server menerbitkan token reset di session.
 *  3) Tentukan kata sandi baru memakai token tersebut.
 *
 * State antar tahap disimpan di session server (token acak dibandingkan
 * dengan hash_equals), bukan di hidden field yang bisa dimanipulasi. Langkah 3
 * hanya bisa diakses setelah kode yang valid, dalam batas waktu tertentu.
 */
class PasswordResetController extends Controller
{
    /**
     * Kunci session yang menyimpan token reset aktif.
     */
    private const SESSION_TOKEN = 'password_reset.token';

    /**
     * Kunci session yang menyimpan email tujuan reset.
     */
    private const SESSION_EMAIL = 'password_reset.email';

    /**
     * Kunci session yang menyimpan batas waktu token reset.
     */
    private const SESSION_EXPIRES = 'password_reset.expires_at';

    /**
     * Masa berlaku token reset (menit).
     */
    private const TOKEN_TTL_MINUTES = 15;

    public function __construct(
        private readonly VerificationCodeService $codes,
    ) {}

    /**
     * Halaman permintaan kode reset (tahap 1).
     */
    public function request(Request $request): View
    {
        return view('auth.forgot-password', [
            'email' => (string) $request->session()->get(self::SESSION_EMAIL, ''),
        ]);
    }

    /**
     * Kirim kode reset ke email yang dimasukkan (tahap 1).
     *
     * Respons sengaja identik apakah email terdaftar atau tidak.
     */
    public function email(PasswordResetEmailRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $email = Str::lower($validated['email']);

        // Simpan email di session untuk tahap berikutnya. Email disimpan
        // meskipun tidak terdaftar, agar tahap 2 tetap dapat diakses dan
        // perilakunya tidak membocorkan keberadaan akun.
        $request->session()->put(self::SESSION_EMAIL, $email);

        // Token tahap 3 dicabut: pengguna harus melewati tahap 2 lagi.
        $request->session()->forget([self::SESSION_TOKEN, self::SESSION_EXPIRES]);

        $user = User::where('email', $email)->first();

        if ($user && $this->codes->secondsUntilResend($user, VerificationPurposeEnum::PasswordReset) === 0) {
            $this->codes->send($user, VerificationPurposeEnum::PasswordReset);
        }

        // Selalu pesan yang sama, apa pun hasilnya.
        return redirect()
            ->route('password.reset')
            ->with('status', 'Jika email terdaftar, kode telah dikirim. Silakan periksa kotak masuk Anda.');
    }

    /**
     * Halaman input kode (tahap 2).
     */
    public function reset(Request $request): View
    {
        return view('auth.reset-password', [
            'email' => (string) $request->session()->get(self::SESSION_EMAIL, ''),
            'resendCooldown' => $this->resendCooldown($request),
            'expiresInMinutes' => VerificationCodeService::expiresInMinutes(),
        ]);
    }

    /**
     * Verifikasi kode reset (tahap 2) lalu terbitkan token untuk tahap 3.
     */
    public function verify(PasswordResetCodeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = $this->userFromSession($request);

        if (! $user) {
            return back()->withErrors([
                'code' => 'Kode salah. Silakan periksa kembali atau minta kode baru.',
            ]);
        }

        $result = $this->codes->check($user, VerificationPurposeEnum::PasswordReset, $validated['code']);

        if (! $result->isValid()) {
            return back()->withErrors([
                'code' => $this->errorMessage($result),
            ]);
        }

        // Token reset diterbitkan di server dan terikat session.
        $token = Str::random(64);
        $request->session()->put(self::SESSION_TOKEN, $token);
        $request->session()->put(self::SESSION_EMAIL, $user->email);
        $request->session()->put(self::SESSION_EXPIRES, now()->addMinutes(self::TOKEN_TTL_MINUTES)->timestamp);

        return redirect()
            ->route('password.reset.form', ['token' => $token]);
    }

    /**
     * Halaman kata sandi baru (tahap 3).
     */
    public function form(Request $request, string $token): View|RedirectResponse
    {
        if (! $this->tokenIsValid($request, $token)) {
            return $this->expiredToken();
        }

        return view('auth.reset-password-form', [
            'token' => $token,
            'email' => (string) $request->session()->get(self::SESSION_EMAIL, ''),
        ]);
    }

    /**
     * Simpan kata sandi baru (tahap 3).
     */
    public function update(PasswordResetUpdateRequest $request, string $token): RedirectResponse
    {
        if (! $this->tokenIsValid($request, $token)) {
            return $this->expiredToken();
        }

        $validated = $request->validated();

        $user = $this->userFromSession($request);

        if (! $user) {
            return $this->expiredToken();
        }

        $user->update([
            'password' => Hash::make($validated['password']),
            'password_set_at' => now(),
        ]);

        // Kode reset lain milik pengguna dibersihkan.
        $this->codes->clear($user, VerificationPurposeEnum::PasswordReset);

        // Remember token dirotasi sehingga sesi "ingat saya" di perangkat lain
        // tidak lagi berlaku. Status verifikasi email tidak diubah.
        $user->setRememberToken(Str::random(60));
        $user->save();

        $request->session()->forget([self::SESSION_TOKEN, self::SESSION_EMAIL, self::SESSION_EXPIRES]);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Password berhasil diubah. Silakan login.');
    }

    /**
     * Pastikan token pada URL cocok dengan token yang tersimpan di session
     * dan belum kedaluwarsa.
     */
    private function tokenIsValid(Request $request, string $token): bool
    {
        $stored = $request->session()->get(self::SESSION_TOKEN);

        if (! is_string($stored) || $stored === '' || ! hash_equals($stored, $token)) {
            return false;
        }

        $expiresAt = $request->session()->get(self::SESSION_EXPIRES);

        return is_int($expiresAt) && $expiresAt > now()->timestamp;
    }

    /**
     * Ambil user yang terkait dengan sesi reset aktif.
     */
    private function userFromSession(Request $request): ?User
    {
        $email = $request->session()->get(self::SESSION_EMAIL);

        if (! is_string($email) || $email === '') {
            return null;
        }

        return User::where('email', $email)->first();
    }

    /**
     * Sisa detik cooldown sebelum kode baru boleh diminta.
     */
    private function resendCooldown(Request $request): int
    {
        $email = $request->session()->get(self::SESSION_EMAIL);

        if (! is_string($email) || $email === '') {
            return 0;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return 0;
        }

        return $this->codes->secondsUntilResend($user, VerificationPurposeEnum::PasswordReset);
    }

    /**
     * Tampilan saat token reset sudah tidak berlaku.
     */
    private function expiredToken(): RedirectResponse
    {
        return redirect()
            ->route('password.request')
            ->withErrors([
                'email' => 'Sesi atur ulang kata sandi tidak valid atau sudah berakhir. Silakan mulai lagi.',
            ]);
    }

    /**
     * Pesan kesalahan Indonesia sesuai hasil pemeriksaan kode.
     */
    private function errorMessage(VerificationResult $result): string
    {
        return match ($result) {
            VerificationResult::Expired => 'Kode sudah kedaluwarsa. Silakan minta kode baru.',
            VerificationResult::TooManyAttempts => 'Percobaan terlalu banyak. Silakan minta kode baru.',
            default => 'Kode salah. Silakan periksa kembali atau minta kode baru.',
        };
    }
}
