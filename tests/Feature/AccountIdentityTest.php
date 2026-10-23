<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\VerificationPurposeEnum;
use App\Http\Middleware\EnsureVerifiedEmailForApplication;
use App\Mail\VerificationCodeMail;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccountIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_username_can_be_changed_to_a_display_name_and_must_be_unique(): void
    {
        $user = User::factory()->create(['role' => RoleEnum::Pelamar]);
        $other = User::factory()->create(['role' => RoleEnum::Pelamar, 'username' => 'taken-name']);
        $originalName = $user->name;

        $this->actingAs($user)->put(route('account.username.update'), ['username' => 'Gilbert Lincoln'])
            ->assertRedirect(route('password.edit'));
        $this->assertSame('Gilbert Lincoln', $user->fresh()->username);
        $this->assertSame('GL', $user->fresh()->initials());
        $this->get(route('password.edit'))->assertOk()
            ->assertSee('Ringkasan Akun')
            ->assertSee('Gilbert Lincoln')
            ->assertDontSee($originalName);

        $this->put(route('account.username.update'), ['username' => $other->username])
            ->assertSessionHasErrors('username');
    }

    public function test_email_change_requires_password_and_valid_unique_email(): void
    {
        $user = User::factory()->verified()->create();
        User::factory()->create(['email' => 'already@example.test']);
        $this->actingAs($user);

        $this->put(route('account.email.update'), ['email' => 'new@example.test'])
            ->assertSessionHasErrors('current_password');
        $this->put(route('account.email.update'), ['email' => 'new@example.test', 'current_password' => 'wrong-password'])
            ->assertSessionHasErrors('current_password');
        $this->put(route('account.email.update'), ['email' => 'already@example.test', 'current_password' => 'password'])
            ->assertSessionHasErrors('email');
    }

    public function test_email_change_resets_verification_clears_old_codes_and_can_verify_new_address(): void
    {
        Mail::fake();
        $user = User::factory()->verified()->create();
        $oldCode = $this->issueVerificationCode($user, VerificationPurposeEnum::EmailVerification);

        $this->actingAs($user)->put(route('account.email.update'), [
            'email' => 'NEW@EXAMPLE.TEST',
            'current_password' => 'password',
        ])->assertRedirect(route('password.edit'))->assertSessionHas('email_changed', true);

        $user->refresh();
        $this->assertSame('new@example.test', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('verification_codes', ['user_id' => $user->id]);
        $this->get(route('password.edit'))->assertOk()
            ->assertSee('Verifikasi Sekarang')
            ->assertSee('Nanti Saja');

        $this->post(route('verification.verify'), ['code' => $oldCode])->assertSessionHasErrors('code');
        $this->post(route('verification.send'));
        $newCode = $this->captureVerificationCode();
        Mail::assertSent(VerificationCodeMail::class, fn (VerificationCodeMail $mail): bool => $mail->hasTo('new@example.test'));

        $this->post(route('verification.verify'), ['code' => $newCode])->assertRedirect();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_email_can_be_left_unverified_and_applicant_cannot_apply_after_change(): void
    {
        $user = User::factory()->verified()->create(['role' => RoleEnum::Pelamar]);
        $job = JobPosting::factory()->create();

        $this->actingAs($user)->put(route('account.email.update'), [
            'email' => 'later@example.test',
            'current_password' => 'password',
        ])->assertRedirect(route('password.edit'));

        $this->get(route('password.edit'))->assertOk()->assertSee('Belum Terverifikasi');
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertAuthenticatedAs($user);

        $this->get(route('pelamar.apply.create', $job))->assertRedirect()
            ->assertSessionHas('error', EnsureVerifiedEmailForApplication::MESSAGE);
        $this->assertDatabaseCount('applications', 0);
    }

    public function test_all_roles_can_open_account_identity_settings(): void
    {
        foreach (RoleEnum::cases() as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('password.edit'))->assertOk()
                ->assertSee('Username & Email')
                ->assertSee('Simpan Email');
        }
    }

    public function test_admin_master_settings_keeps_only_overview_and_nlp_account_sections(): void
    {
        $admin = User::factory()->create(['role' => RoleEnum::Admin]);

        $this->actingAs($admin)->get(route('admin.settings.index'))->assertOk()
            ->assertSee('Ringkasan Akun')
            ->assertSee('NLP Node')
            ->assertDontSee('Status Email')
            ->assertDontSee('Ganti Kata Sandi');
    }
}
