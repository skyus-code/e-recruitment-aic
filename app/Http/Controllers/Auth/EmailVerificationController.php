<?php

namespace App\Http\Controllers\Auth;

use App\Enums\VerificationPurposeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmailVerificationRequest;
use App\Models\User;
use App\Services\VerificationCodeService;
use App\Services\VerificationResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Alur verifikasi email berbasis kode OTP 6 digit.
 *
 * Pengguna yang belum terverifikasi diarahkan ke halaman input kode. Setelah
 * kode benar, email ditandai terverifikasi dan pengguna masuk ke dasbornya.
 */
class EmailVerificationController extends Controller
{
    public function __construct(
        private readonly VerificationCodeService $codes,
    ) {}

    /**
     * Tampilkan halaman input kode verifikasi.
     */
    public function notice(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route($user->role->dashboardRoute());
        }

        return view('auth.verify-email', [
            'email' => $user->email,
            'resendCooldown' => $this->codes->secondsUntilResend($user, VerificationPurposeEnum::EmailVerification),
            'expiresInMinutes' => VerificationCodeService::expiresInMinutes(),
        ]);
    }

    /**
     * Verifikasi kode yang dimasukkan pengguna.
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = $request->user();
        [$user, $result] = DB::transaction(function () use ($user, $validated): array {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $result = $this->codes->check($lockedUser, VerificationPurposeEnum::EmailVerification, $validated['code']);

            if ($result->isValid()) {
                $lockedUser->forceFill(['email_verified_at' => now()])->save();
            }

            return [$lockedUser, $result];
        });

        if (! $result->isValid()) {
            return back()->withErrors([
                'code' => $this->errorMessage($result),
            ]);
        }

        return redirect()
            ->route($this->successRoute($request))
            ->with('success', 'Email berhasil diverifikasi. Selamat datang di portal rekrutmen!');
    }

    /**
     * Kirim ulang kode verifikasi baru (dengan cooldown).
     */
    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route($user->role->dashboardRoute());
        }

        $wait = $this->codes->secondsUntilResend($user, VerificationPurposeEnum::EmailVerification);

        if ($wait > 0) {
            return back()->withErrors([
                'code' => "Mohon tunggu {$wait} detik sebelum meminta kode baru.",
            ]);
        }

        $this->codes->send($user, VerificationPurposeEnum::EmailVerification);

        return back()->with('success', 'Kode verifikasi baru telah dikirim ke '.$user->email.'.');
    }

    /**
     * Pesan kesalahan Indonesia sesuai hasil pemeriksaan kode.
     */
    private function errorMessage(VerificationResult $result): string
    {
        return match ($result) {
            VerificationResult::Expired => 'Kode verifikasi sudah kedaluwarsa. Silakan minta kode baru.',
            VerificationResult::TooManyAttempts => 'Percobaan terlalu banyak. Silakan minta kode verifikasi baru.',
            default => 'Kode verifikasi salah. Silakan periksa kembali atau minta kode baru.',
        };
    }

    /**
     * Halaman tujuan setelah verifikasi berhasil. Nilai "redirect_to" hanya
     * dipercaya bila merupakan nama route di dalam area terautentikasi,
     * sehingga tidak dapat dipakai untuk open redirect.
     */
    private function successRoute(Request $request): string
    {
        $fallback = $request->user()->role->dashboardRoute();
        $target = $request->input('redirect_to');

        if (! is_string($target) || ! Route::has($target)) {
            return $fallback;
        }

        $allowedPrefixes = ['admin.', 'manajer.', 'pelamar.', 'password.', 'verification.'];

        return Str::startsWith($target, $allowedPrefixes) ? $target : $fallback;
    }
}
