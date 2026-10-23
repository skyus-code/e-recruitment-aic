<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    /**
     * Setiap pengguna yang sudah masuk boleh mengganti kata sandinya sendiri.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Akun OAuth yang belum pernah menetapkan kata sandi sendiri cukup mengisi
     * kata sandi baru. Akun biasa tetap wajib mengonfirmasi kata sandi lama.
     */
    public function hasOwnPassword(): bool
    {
        $user = $this->user();

        if ($user === null || $user->password_set_at !== null) {
            return true;
        }

        return ! $user->hasLinkedSocialAccount();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = [
            'password' => ['required', 'confirmed', Password::defaults()],
        ];

        if ($this->hasOwnPassword()) {
            $rules['current_password'] = ['required', 'string', 'current_password'];
            $rules['password'][] = 'different:current_password';
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.different' => 'Kata sandi baru tidak boleh sama dengan kata sandi saat ini.',
            'password.min' => 'Kata sandi baru minimal :min karakter.',
        ];
    }
}
