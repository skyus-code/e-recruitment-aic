<?php

namespace App\Models;

use App\Enums\VerificationPurposeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kode OTP 6 digit sekali pakai untuk verifikasi email atau atur ulang
 * kata sandi. Kode selalu disimpan sebagai hash.
 */
class VerificationCode extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'purpose' => VerificationPurposeEnum::class,
            'expires_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Kode yang masih boleh dipakai: belum dikonsumsi dan belum kedaluwarsa.
     */
    public function isUsable(): bool
    {
        return $this->consumed_at === null && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Sisa detik cooldown kirim ulang (0 bila sudah boleh kirim lagi).
     */
    public function secondsUntilResend(int $cooldownSeconds): int
    {
        if ($this->last_sent_at === null) {
            return 0;
        }

        return max(0, $this->last_sent_at->timestamp + $cooldownSeconds - now()->timestamp);
    }

    public function scopeForPurpose(Builder $query, VerificationPurposeEnum $purpose): Builder
    {
        return $query->where('purpose', $purpose->value);
    }
}
