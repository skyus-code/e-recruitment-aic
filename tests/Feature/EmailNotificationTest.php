<?php

namespace Tests\Feature;

use App\Enums\VerificationPurposeEnum;
use App\Mail\ApplicationStatusMail;
use App\Mail\VerificationCodeMail;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    public function test_application_status_email_uses_a_dynamic_subject(): void
    {
        (new ApplicationStatusMail('Fadhil', 'passed'))
            ->assertHasSubject('Anda Lolos Seleksi Berkas - Ar-Rahman Islamic Center');

        (new ApplicationStatusMail('Fadhil', 'hired'))
            ->assertHasSubject('Selamat! Lamaran Anda Diterima - Ar-Rahman Islamic Center');

        (new ApplicationStatusMail('Fadhil', 'rejected'))
            ->assertHasSubject('Pemberitahuan Hasil Seleksi - Ar-Rahman Islamic Center');
    }

    public function test_application_status_email_renders_inline_css_and_greets_the_user(): void
    {
        $mailable = new ApplicationStatusMail('Fadhil', 'hired');

        $mailable->assertSeeInHtml('Fadhil');
        $mailable->assertSeeInHtml('#1E3A8A');
        $mailable->assertSeeInHtml('#059669');
        $mailable->assertSeeInHtml('Lihat Dashboard');
    }

    public function test_rejected_email_uses_the_red_accent(): void
    {
        (new ApplicationStatusMail('Fadhil', 'rejected'))
            ->assertSeeInHtml('#DC2626');
    }

    public function test_verification_email_renders_the_code(): void
    {
        (new VerificationCodeMail('Fadhil', '654321', VerificationPurposeEnum::EmailVerification, 10))
            ->assertSeeInHtml('654321')
            ->assertSeeInHtml('Ar-Rahman Islamic Center');
    }

    public function test_password_reset_email_uses_its_own_subject_and_expiry(): void
    {
        $mailable = new VerificationCodeMail('Fadhil', '654321', VerificationPurposeEnum::PasswordReset, 10);

        $mailable->assertHasSubject('Kode Atur Ulang Kata Sandi - Ar-Rahman Islamic Center');
        $mailable->assertSeeInHtml('654321');
        $mailable->assertSeeInHtml('10 menit');
    }
}
