<?php

namespace App\Models;

use App\Enums\RoleEnum;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'password_set_at', 'phone', 'role', 'google_id', 'linkedin_id', 'facebook_id', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_set_at' => 'datetime',
            'password' => 'hashed',
            'role' => RoleEnum::class,
        ];
    }

    public function person(): HasOne
    {
        return $this->hasOne(Person::class);
    }

    public function detailPerson(): HasOneThrough
    {
        return $this->hasOneThrough(DetailPerson::class, Person::class);
    }

    public function educations(): HasManyThrough
    {
        return $this->hasManyThrough(PersonEducation::class, Person::class);
    }

    public function experiences(): HasManyThrough
    {
        return $this->hasManyThrough(PersonExperience::class, Person::class);
    }

    public function skills(): HasManyThrough
    {
        return $this->hasManyThrough(PersonSkill::class, Person::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Kode OTP sekali pakai (verifikasi email / atur ulang kata sandi).
     */
    public function verificationCodes(): HasMany
    {
        return $this->hasMany(VerificationCode::class);
    }

    /**
     * Portal ini memakai kode verifikasi 6 digit, bukan tautan bertanda tangan
     * bawaan Laravel. Pengiriman dilakukan oleh VerificationCodeService.
     */
    public function sendEmailVerificationNotification(): void
    {
        // Sengaja dikosongkan: kode dikirim manual oleh VerificationCodeService.
    }

    public function hasRole(RoleEnum $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Akun yang tertaut ke minimal satu provider OAuth (Google/LinkedIn/Facebook).
     */
    public function hasLinkedSocialAccount(): bool
    {
        return collect(['google_id', 'linkedin_id', 'facebook_id'])
            ->contains(fn (string $column): bool => ! is_null($this->{$column}));
    }

    /**
     * Nama provider OAuth yang tertaut, untuk ditampilkan di halaman akun.
     *
     * @return list<string>
     */
    public function linkedSocialProviders(): array
    {
        $providers = [
            'google_id' => 'Google',
            'linkedin_id' => 'LinkedIn',
            'facebook_id' => 'Facebook',
        ];

        return collect($providers)
            ->filter(fn (string $label, string $column): bool => ! is_null($this->{$column}))
            ->values()
            ->all();
    }

    public function initials(): string
    {
        return collect(explode(' ', trim($this->username ?: $this->name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
