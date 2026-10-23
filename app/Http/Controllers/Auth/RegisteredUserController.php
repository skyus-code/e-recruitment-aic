<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleEnum;
use App\Enums\VerificationPurposeEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\VerificationCodeService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly VerificationCodeService $codes,
    ) {}

    /**
     * Show the registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan gunakan email lain.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        // Akun dibuat tanpa email_verified_at; aktivasi lewat kode 6 digit.
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'password_set_at' => now(),
            'role' => RoleEnum::Pelamar,
        ]);

        // Kirim kode verifikasi ke email pelamar.
        $this->codes->send($user, VerificationPurposeEnum::EmailVerification);

        event(new Registered($user));

        Auth::login($user);

        RateLimiter::clear('register');

        return redirect()
            ->route('verification.notice')
            ->with('status', 'Kami telah mengirim kode verifikasi ke '.$user->email.'.');
    }
}
