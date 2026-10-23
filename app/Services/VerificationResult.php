<?php

namespace App\Services;

/**
 * Hasil pemeriksaan kode OTP.
 */
enum VerificationResult
{
    /** Kode benar dan sudah dikonsumsi. */
    case Valid;

    /** Kode salah, masih ada sisa percobaan. */
    case Mismatch;

    /** Tidak ada kode aktif untuk tujuan tersebut. */
    case NotFound;

    /** Kode sudah melewati masa berlaku. */
    case Expired;

    /** Percobaan salah sudah mencapai batas; kode hangus. */
    case TooManyAttempts;

    /**
     * Apakah kode lolos verifikasi.
     */
    public function isValid(): bool
    {
        return $this === self::Valid;
    }
}
