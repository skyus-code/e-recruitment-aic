<?php

namespace App\Http\Controllers\Auth;

use App\Enums\VerificationPurposeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateEmailRequest;
use App\Http\Requests\Auth\UpdateUsernameRequest;
use App\Services\VerificationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Halaman Pengaturan Akun yang dipakai seluruh peran (pelamar, admin,
 * manajer): status email, verifikasi email, dan ganti kata sandi.
 */
class AccountController extends Controller
{
    public function __construct(
        private readonly VerificationCodeService $codes,
    ) {}

    /**
     * Tampilkan halaman Pengaturan Akun.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('account.password', [
            'user' => $user,
            'linkedProviders' => $user->linkedSocialProviders(),
            'resendCooldown' => $this->codes->secondsUntilResend($user, VerificationPurposeEnum::EmailVerification),
            'expiresInMinutes' => VerificationCodeService::expiresInMinutes(),
        ]);
    }

    public function updateUsername(UpdateUsernameRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('password.edit')->with('success', 'Username berhasil diperbarui.');
    }

    public function updateEmail(UpdateEmailRequest $request): RedirectResponse
    {
        $user = $request->user();
        $email = $request->validated('email');

        if ($email === $user->email) {
            return redirect()->route('password.edit')->with('success', 'Alamat email tidak berubah.');
        }

        DB::transaction(function () use ($user, $email): void {
            $lockedUser = $user->newQuery()->lockForUpdate()->findOrFail($user->id);
            $lockedUser->forceFill([
                'email' => $email,
                'email_verified_at' => null,
            ])->save();

            $this->codes->clear($lockedUser, VerificationPurposeEnum::EmailVerification);
            $this->codes->clear($lockedUser, VerificationPurposeEnum::PasswordReset);
        });

        $user->refresh();

        return redirect()->route('password.edit')->with('email_changed', true);
    }
}
