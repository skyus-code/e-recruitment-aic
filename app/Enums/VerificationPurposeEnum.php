<?php

namespace App\Enums;

/**
 * Tujuan pemakaian kode OTP 6 digit.
 *
 * Kode dengan tujuan berbeda disimpan terpisah sehingga kode verifikasi email
 * tidak bisa dipakai untuk mengatur ulang kata sandi, dan sebaliknya.
 */
enum VerificationPurposeEnum: string
{
    case EmailVerification = 'email_verification';
    case PasswordReset = 'password_reset';

    public function label(): string
    {
        return match ($this) {
            self::EmailVerification => 'Verifikasi Email',
            self::PasswordReset => 'Atur Ulang Kata Sandi',
        };
    }

    /**
     * Subjek email yang dipakai saat mengirim kode untuk tujuan ini.
     */
    public function mailSubject(): string
    {
        return match ($this) {
            self::EmailVerification => 'Kode Verifikasi Email - Ar-Rahman Islamic Center',
            self::PasswordReset => 'Kode Atur Ulang Kata Sandi - Ar-Rahman Islamic Center',
        };
    }
}
