<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_completes_with_a_single_use_code(): void
    {
        Mail::fake();
        $user = User::factory()->unverified()->create(['role' => RoleEnum::Pelamar]);

        $this->post('/lupa-password', ['email' => $user->email])
            ->assertRedirect('/reset-password')
            ->assertSessionHas('status', 'Jika email terdaftar, kode telah dikirim. Silakan periksa kotak masuk Anda.');

        $this->get('/reset-password')->assertOk();

        $code = $this->captureVerificationCode();
        $response = $this->post('/reset-password/verify', ['code' => $code]);
        $response->assertRedirect();
        $token = basename(parse_url($response->headers->get('Location'), PHP_URL_PATH));

        $this->get('/reset-password/'.$token)->assertOk();
        $this->post('/reset-password/'.$token, [
            'password' => 'New-password-123',
            'password_confirmation' => 'New-password-123',
        ])->assertRedirect('/login')->assertSessionHas('success', 'Password berhasil diubah. Silakan login.');

        $this->assertTrue(Hash::check('New-password-123', $user->fresh()->password));
        $this->assertNull($user->fresh()->email_verified_at);
        $this->post('/reset-password/verify', ['code' => $code])->assertSessionHasErrors('code');
    }

    public function test_password_reset_email_response_does_not_reveal_account_existence(): void
    {
        Mail::fake();
        $message = 'Jika email terdaftar, kode telah dikirim. Silakan periksa kotak masuk Anda.';

        $this->post('/lupa-password', ['email' => 'missing@example.test'])
            ->assertRedirect('/reset-password')->assertSessionHas('status', $message);

        $user = User::factory()->create();
        $this->post('/lupa-password', ['email' => $user->email])
            ->assertRedirect('/reset-password')->assertSessionHas('status', $message);
    }

    public function test_password_reset_step_three_requires_a_valid_session_token(): void
    {
        $this->get('/reset-password/fabricated-token')->assertRedirect('/lupa-password');
    }
}
