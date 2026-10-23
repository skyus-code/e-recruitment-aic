<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\VerificationPurposeEnum;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_renders_for_guests(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_registration_screen_renders_for_guests(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_new_user_registration_requires_email_verification(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'name' => 'Pelamar Baru',
            'email' => 'baru@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'baru@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(RoleEnum::Pelamar, $user->role);
        $this->assertNull($user->email_verified_at);
        $this->assertDatabaseHas('verification_codes', [
            'user_id' => $user->id,
            'purpose' => VerificationPurposeEnum::EmailVerification->value,
        ]);

        Mail::assertSent(VerificationCodeMail::class, fn (VerificationCodeMail $mail): bool => $mail->hasTo('baru@example.test'));

        $response->assertRedirect('/email/verify');
    }

    public function test_verification_notice_is_shown_to_unverified_users(): void
    {
        $user = User::factory()->unverified()->create(['role' => RoleEnum::Pelamar]);

        $this->actingAs($user)
            ->get('/email/verify')
            ->assertOk()
            ->assertSee($user->email);
    }

    public function test_unverified_user_can_still_open_the_account_settings_page(): void
    {
        $user = User::factory()->unverified()->create(['role' => RoleEnum::Pelamar]);

        $this->actingAs($user)
            ->get('/account/password')
            ->assertOk()
            ->assertSee('Belum Terverifikasi');
    }

    public function test_user_can_verify_email_with_a_valid_code(): void
    {
        $user = User::factory()->unverified()->create(['role' => RoleEnum::Pelamar]);

        $code = $this->issueVerificationCode($user);

        $this->actingAs($user)
            ->post('/email/verify', ['code' => $code])
            ->assertRedirect('/pelamar/dashboard');

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseMissing('verification_codes', ['user_id' => $user->id]);
    }

    public function test_a_verification_code_can_only_be_used_once(): void
    {
        $user = User::factory()->unverified()->create(['role' => RoleEnum::Pelamar]);

        $code = $this->issueVerificationCode($user);

        $this->actingAs($user)
            ->post('/email/verify', ['code' => $code])
            ->assertRedirect('/pelamar/dashboard');

        // Percobaan kedua dengan kode yang sama harus ditolak.
        $this->actingAs($user->fresh())
            ->post('/email/verify', ['code' => $code])
            ->assertSessionHasErrors('code');
    }

    public function test_an_invalid_verification_code_is_rejected(): void
    {
        $user = User::factory()->unverified()->create(['role' => RoleEnum::Pelamar]);

        $this->issueVerificationCode($user);

        $this->actingAs($user)
            ->post('/email/verify', ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_an_expired_verification_code_is_rejected(): void
    {
        $user = User::factory()->unverified()->create(['role' => RoleEnum::Pelamar]);

        $code = $this->issueVerificationCode($user);

        $user->verificationCodes()->update(['expires_at' => now()->subMinute()]);

        $this->actingAs($user)
            ->post('/email/verify', ['code' => $code])
            ->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_user_can_request_a_new_verification_code(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create(['role' => RoleEnum::Pelamar]);

        $this->actingAs($user)
            ->post('/email/verification-notification')
            ->assertRedirect();

        $this->assertDatabaseHas('verification_codes', [
            'user_id' => $user->id,
            'purpose' => VerificationPurposeEnum::EmailVerification->value,
        ]);

        Mail::assertSent(VerificationCodeMail::class);
    }

    public function test_resending_a_verification_code_respects_the_cooldown(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create(['role' => RoleEnum::Pelamar]);

        $this->actingAs($user)->post('/email/verification-notification');

        Mail::assertSentCount(1);

        // Permintaan kedua dalam masa cooldown ditolak.
        $this->actingAs($user)
            ->post('/email/verification-notification')
            ->assertSessionHasErrors('code');

        Mail::assertSentCount(1);
    }

    public function test_registration_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'dupe@example.test']);

        $this->post('/register', [
            'name' => 'Pengulang',
            'email' => 'dupe@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('email');
    }

    public function test_registration_requires_a_confirmed_password(): void
    {
        $this->post('/register', [
            'name' => 'Tanpa Konfirmasi',
            'email' => 'konfirmasi@example.test',
            'password' => 'password123',
            'password_confirmation' => 'tidaksama',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'pelamar@example.test',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => 'pelamar@example.test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/pelamar/dashboard');
        $this->assertAuthenticated();
    }

    public function test_admin_and_manajer_are_sent_to_their_own_dashboards(): void
    {
        User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => RoleEnum::Admin,
        ]);

        $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'password',
        ])->assertRedirect('/admin/dashboard');
    }

    public function test_users_cannot_authenticate_with_a_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'pelamar@example.test',
            'password' => 'password',
        ]);

        $this->post('/login', [
            'email' => 'pelamar@example.test',
            'password' => 'salah-sandi',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_users_are_redirected_away_from_the_login_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/login')
            ->assertRedirect('/');
    }

    public function test_logout_invalidates_the_session_and_returns_home(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
