<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\VerificationPurposeEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use SocialiteProviders\LinkedIn\Provider as LinkedInProvider;
use Tests\TestCase;

class SocialiteLoginTest extends TestCase
{
    use RefreshDatabase;

    private function configureProvider(string $provider = 'google'): void
    {
        config([
            "services.{$provider}.client_id" => 'test-client-id',
            "services.{$provider}.client_secret" => 'test-client-secret',
        ]);
    }

    public function test_linkedin_driver_is_registered_with_socialite(): void
    {
        $this->configureProvider('linkedin');

        $this->assertInstanceOf(LinkedInProvider::class, Socialite::driver('linkedin'));
    }

    public function test_login_page_shows_social_buttons_for_all_providers(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Lanjutkan dengan Google')
            ->assertSee('Lanjutkan dengan LinkedIn')
            ->assertSee('Lanjutkan dengan Facebook')
            ->assertSee(route('auth.google.redirect'), false)
            ->assertSee(route('auth.linkedin.redirect'), false)
            ->assertSee(route('auth.facebook.redirect'), false);
    }

    public function test_social_redirect_sends_the_guest_to_the_provider(): void
    {
        $this->configureProvider('google');
        Socialite::fake('google');

        $this->get('/auth/google/redirect')
            ->assertRedirect('https://socialite.fake/google/authorize');
    }

    public function test_first_time_social_login_creates_an_unverified_pelamar(): void
    {
        $this->configureProvider('facebook');
        Socialite::fake('facebook', SocialiteUser::fake([
            'id' => 'fb-123',
            'name' => 'Budi Pelamar',
            'email' => 'budi@example.test',
            'avatar' => 'https://example.test/budi.jpg',
        ]));

        // Akun OAuth baru tetap wajib verifikasi email lewat kode OTP.
        $this->get('/auth/facebook/callback')->assertRedirect('/email/verify');

        $user = User::where('email', 'budi@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(RoleEnum::Pelamar, $user->role);
        $this->assertSame('fb-123', $user->facebook_id);
        $this->assertSame('https://example.test/budi.jpg', $user->avatar);
        $this->assertNull($user->email_verified_at);
        $this->assertDatabaseHas('verification_codes', [
            'user_id' => $user->id,
            'purpose' => VerificationPurposeEnum::EmailVerification->value,
        ]);
    }

    public function test_social_login_links_an_existing_account_by_email(): void
    {
        $this->configureProvider('google');

        $user = User::factory()->unverified()->create([
            'email' => 'pelamar@example.test',
            'role' => RoleEnum::Pelamar,
            'google_id' => null,
        ]);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-999',
            'name' => 'Pelamar Lama',
            'email' => 'pelamar@example.test',
            'avatar' => 'https://example.test/avatar.png',
        ]));

        $this->get('/auth/google/callback')->assertRedirect('/email/verify');

        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-999', $user->fresh()->google_id);
        $this->assertSame('https://example.test/avatar.png', $user->fresh()->avatar);
        $this->assertSame(1, User::where('email', 'pelamar@example.test')->count());
    }

    public function test_social_login_does_not_bypass_the_email_verification(): void
    {
        $this->configureProvider('google');

        $user = User::factory()->unverified()->create([
            'email' => 'pelamar@example.test',
            'role' => RoleEnum::Pelamar,
            'google_id' => null,
        ]);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-999',
            'email' => 'pelamar@example.test',
        ]));

        $this->get('/auth/google/callback');

        // Verifikasi tidak boleh otomatis terjadi lewat login sosial.
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_existing_admin_is_sent_to_the_admin_dashboard(): void
    {
        $this->configureProvider('google');

        $admin = User::factory()->verified()->create([
            'email' => 'admin@example.test',
            'role' => RoleEnum::Admin,
        ]);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-admin',
            'email' => 'admin@example.test',
        ]));

        $this->get('/auth/google/callback')->assertRedirect('/admin/dashboard');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_cancelled_authorization_redirects_back_to_login(): void
    {
        $this->configureProvider('google');

        Socialite::fake('google', function (): void {
            throw new \RuntimeException('User denied access');
        });

        $this->get('/auth/google/callback')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_unconfigured_provider_returns_login_with_an_error(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $this->get('/auth/google/redirect')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
    }

    public function test_callback_without_email_does_not_create_an_account(): void
    {
        $this->configureProvider('facebook');

        Socialite::fake('facebook', SocialiteUser::fake([
            'id' => 'fb-no-email',
            'email' => null,
        ]));

        $this->get('/auth/facebook/callback')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }
}
