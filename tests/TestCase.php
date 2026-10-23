<?php

namespace Tests;

use App\Enums\VerificationPurposeEnum;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Services\VerificationCodeService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Mail;

abstract class TestCase extends BaseTestCase
{
    /**
     * Kirim kode OTP untuk sebuah pengguna dan kembalikan kode plaintext-nya.
     *
     * Kode diambil dari mailable yang tertangkap mail fake, sehingga test
     * memakai jalur pengiriman yang sama dengan aplikasi.
     */
    protected function issueVerificationCode(
        User $user,
        VerificationPurposeEnum $purpose = VerificationPurposeEnum::EmailVerification,
    ): string {
        $captured = null;

        Mail::fake();

        app(VerificationCodeService::class)->send($user, $purpose);

        Mail::assertSent(VerificationCodeMail::class, function (VerificationCodeMail $mail) use (&$captured): bool {
            $captured = $mail->code;

            return true;
        });

        return (string) $captured;
    }

    /**
     * Ambil kode OTP plaintext dari mail yang sudah terkirim (mail fake aktif).
     */
    protected function captureVerificationCode(): ?string
    {
        $captured = null;

        Mail::assertSent(VerificationCodeMail::class, function (VerificationCodeMail $mail) use (&$captured): bool {
            $captured = $mail->code;

            return true;
        });

        return $captured;
    }
}
