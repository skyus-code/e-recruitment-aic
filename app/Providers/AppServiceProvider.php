<?php

namespace App\Providers;

use App\Enums\RoleEnum;
use App\Models\Application;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use SocialiteProviders\LinkedIn\LinkedInExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.layouts.app', function ($view): void {
            $user = auth()->user();
            $view->with('newApplicationCount', $user?->role === RoleEnum::Admin
                ? Application::whereNull('admin_seen_at')->count()
                : 0);
        });

        // Daftarkan driver "linkedin" dari package socialiteproviders ke Socialite.
        Event::listen(SocialiteWasCalled::class, LinkedInExtendSocialite::class.'@handle');

        Validator::extend('indonesian_mobile', function (string $attribute, mixed $value): bool {
            return preg_match('/^(?:\+62|62|0)8[1-9][0-9]{6,11}$/', (string) $value) === 1;
        }, 'Nomor HP tidak valid. Gunakan format 08xxxxxxxxxx.');

        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(
                Str::lower((string) $request->input('email')).'|'.$request->ip(),
            );
        });

        RateLimiter::for('register', function (Request $request): Limit {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('uploads', function (Request $request): Limit {
            return Limit::perMinute(12)->by($request->user()?->id ?: $request->ip());
        });

        // Pengiriman kode OTP: dibatasi per akun (email) dan per IP agar kode
        // tidak dipakai untuk flooded (mengeksploitasi kuota email).
        RateLimiter::for('verification-code', function (Request $request): Limit|array {
            $perHour = (int) config('verification.send_per_hour', 5);

            $email = $request->user()?->email ?? $request->input('email');

            return [
                Limit::perHour($perHour)->by('code-account|'.Str::lower((string) $email)),
                Limit::perHour($perHour)->by('code-ip|'.$request->ip()),
            ];
        });

        // Permintaan lupa password: 5 permintaan per jam per IP. Email yang
        // dikirim selalu sama untuk email terdaftar maupun tidak, sehingga
        // rate limit ini tidak membocorkan keberadaan akun.
        RateLimiter::for('password-reset', function (Request $request): Limit|array {
            $perHour = (int) config('verification.send_per_hour', 5);

            return [
                Limit::perHour($perHour)->by('password-reset-account|'.Str::lower((string) $request->input('email'))),
                Limit::perHour($perHour)->by('password-reset-ip|'.$request->ip()),
            ];
        });
    }
}
