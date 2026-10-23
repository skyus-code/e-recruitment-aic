<?php

namespace App\Services;

use App\Enums\VerificationPurposeEnum;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Layanan OTP 6 digit yang dipakai bersama oleh verifikasi email dan atur
 * ulang kata sandi.
 *
 * Aturan keamanan (berlaku untuk kedua fitur):
 * - Kode dibuat dengan CSPRNG (`random_int`), lalu disimpan dalam bentuk hash.
 * - Sekali pakai dan dikonsumsi secara atomik (row lock) agar tidak bisa
 *   dipakai dua kali lewat request paralel.
 * - Maksimal 5 percobaan salah per kode, setelah itu kode hangus.
 * - Cooldown kirim ulang 60 detik; rate limit per akun/IP ditangani route.
 * - Kode dipisah per purpose sehingga tidak bisa dipakai lintas fitur.
 */
class VerificationCodeService
{
    /** Panjang kode numerik. */
    public const CODE_LENGTH = 6;

    /**
     * Masa berlaku kode dalam menit.
     */
    public static function expiresInMinutes(): int
    {
        return (int) config('verification.expires_in_minutes', 10);
    }

    /**
     * Jeda minimum antar permintaan kode dalam detik.
     */
    public static function resendCooldownSeconds(): int
    {
        return (int) config('verification.resend_cooldown_seconds', 60);
    }

    /**
     * Jumlah maksimum percobaan verifikasi kode sebelum hangus.
     */
    public static function maxAttempts(): int
    {
        return (int) config('verification.max_attempts', 5);
    }

    /**
     * Buat kode baru untuk sebuah tujuan, ganti kode lama, lalu kirim via email.
     *
     * Mengembalikan record kode (berisi hash), bukan kode plaintext.
     */
    public function send(User $user, VerificationPurposeEnum $purpose): VerificationCode
    {
        $code = $this->generateCode();
        $now = now();

        $record = DB::transaction(function () use ($user, $purpose, $code, $now): VerificationCode {
            // Satu kode aktif per (user, purpose): kode lama langsung diganti.
            VerificationCode::query()
                ->where('user_id', $user->id)
                ->where('purpose', $purpose->value)
                ->delete();

            return VerificationCode::create([
                'user_id' => $user->id,
                'purpose' => $purpose,
                'code_hash' => Hash::make($code),
                'expires_at' => $now->copy()->addMinutes(self::expiresInMinutes()),
                'attempts' => 0,
                'last_sent_at' => $now,
            ]);
        });

        Mail::to($user->email)->send(new VerificationCodeMail(
            $user->name,
            $code,
            $purpose,
            self::expiresInMinutes(),
        ));

        return $record;
    }

    /**
     * Hasil pemeriksaan kode.
     */
    public function check(User $user, VerificationPurposeEnum $purpose, string $code): VerificationResult
    {
        $result = DB::transaction(function () use ($user, $purpose, $code): VerificationResult {
            $record = VerificationCode::query()
                ->where('user_id', $user->id)
                ->where('purpose', $purpose->value)
                ->lockForUpdate()
                ->first();

            if (! $record || $record->consumed_at !== null) {
                return VerificationResult::NotFound;
            }

            if ($record->isExpired()) {
                $record->delete();

                return VerificationResult::Expired;
            }

            if ($record->attempts >= self::maxAttempts()) {
                $record->delete();

                return VerificationResult::TooManyAttempts;
            }

            // Perbandingan constant-time terhadap hash.
            if (! Hash::check($code, $record->code_hash)) {
                $record->increment('attempts');

                if ($record->attempts >= self::maxAttempts()) {
                    $record->delete();

                    return VerificationResult::TooManyAttempts;
                }

                return VerificationResult::Mismatch;
            }

            // Sekali pakai: tandai terpakai, lalu hapus agar tidak tersisa.
            $record->forceFill(['consumed_at' => now()])->save();
            $record->delete();

            return VerificationResult::Valid;
        });

        return $result;
    }

    /**
     * Sisa detik cooldown sebelum pengguna boleh meminta kode baru.
     */
    public function secondsUntilResend(User $user, VerificationPurposeEnum $purpose): int
    {
        $record = VerificationCode::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose->value)
            ->first();

        return $record?->secondsUntilResend(self::resendCooldownSeconds()) ?? 0;
    }

    /**
     * Hapus seluruh kode milik pengguna untuk sebuah tujuan.
     */
    public function clear(User $user, VerificationPurposeEnum $purpose): void
    {
        VerificationCode::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose->value)
            ->delete();
    }

    /**
     * Kode numerik acak dengan panjang tetap, berasal dari CSPRNG.
     *
     * `random_int` tidak bisa diprediksi dan tidak berulang seperti rand/mt_rand.
     */
    private function generateCode(): string
    {
        $max = (10 ** self::CODE_LENGTH) - 1;

        return str_pad((string) random_int(0, $max), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }
}
