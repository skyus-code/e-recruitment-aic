<?php

use App\Http\Controllers\Auth\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Autentikasi (scaffolding mandiri + OAuth Google, LinkedIn, Facebook)
// ---------------------------------------------------------------------------

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:register');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

    // -----------------------------------------------------------------------
    // Lupa password (berbasis kode OTP, tanpa tautan)
    // -----------------------------------------------------------------------

    Route::get('/lupa-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetController::class, 'email'])
        ->middleware('throttle:password-reset')
        ->name('password.email');

    Route::get('/reset-password', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password/verify', [PasswordResetController::class, 'verify'])
        ->middleware('throttle:6,1')
        ->name('password.code.verify');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'form'])->name('password.reset.form');
    Route::post('/reset-password/{token}', [PasswordResetController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.reset.store');

    // Login sosial. Route didaftarkan per provider agar namanya eksplisit
    // (auth.google.redirect, auth.linkedin.callback, dst). Nilai provider
    // disuntik lewat defaults() sehingga tidak bisa dipalsukan dari URL.
    Route::prefix('auth')->name('auth.')->group(function (): void {
        foreach (SocialiteController::supportedProviders() as $provider) {
            Route::get("/{$provider}/redirect", [SocialiteController::class, 'redirect'])
                ->defaults('provider', $provider)
                ->middleware('throttle:20,1')
                ->name("{$provider}.redirect");

            Route::get("/{$provider}/callback", [SocialiteController::class, 'callback'])
                ->defaults('provider', $provider)
                ->middleware('throttle:20,1')
                ->name("{$provider}.callback");
        }
    });
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // -----------------------------------------------------------------------
    // Pengaturan akun: status email, verifikasi email, dan ganti kata sandi
    // untuk semua peran (pelamar, admin, manajer).
    //
    // Rute ini SENGAJA berada di luar middleware "verified" agar akun yang
    // belum terverifikasi tetap bisa masuk dan memverifikasi email-nya
    // (menghindari deadlock).
    // -----------------------------------------------------------------------

    Route::get('/account/password', [AccountController::class, 'edit'])->name('password.edit');
    Route::put('/account/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');
    Route::put('/account/username', [AccountController::class, 'updateUsername'])
        ->middleware('throttle:6,1')
        ->name('account.username.update');
    Route::put('/account/email', [AccountController::class, 'updateEmail'])
        ->middleware('throttle:6,1')
        ->name('account.email.update');

    // -----------------------------------------------------------------------
    // Verifikasi email berbasis kode 6 digit
    // -----------------------------------------------------------------------

    // Halaman input kode. Nama "verification.notice" dipakai oleh middleware
    // "verified" untuk mengarahkan pengguna yang belum terverifikasi.
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verify', [EmailVerificationController::class, 'verify'])
        ->middleware('throttle:6,1')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:verification-code')
        ->name('verification.send');
});
