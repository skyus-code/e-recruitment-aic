<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private function user(RoleEnum $role = RoleEnum::Pelamar): User
    {
        return User::factory()->create([
            'role' => $role,
            'password' => 'password-lama',
            'password_set_at' => now(),
        ]);
    }

    public function test_account_page_renders_for_every_role(): void
    {
        foreach ([RoleEnum::Pelamar, RoleEnum::Admin, RoleEnum::Manajer] as $role) {
            $this->actingAs($this->user($role))
                ->get(route('password.edit'))
                ->assertOk()
                ->assertSee('Ganti Kata Sandi');
        }
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('password.edit'))->assertRedirect(route('login'));
    }

    public function test_password_can_be_changed_with_the_correct_current_password(): void
    {
        $user = $this->user(RoleEnum::Admin);

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'password-lama',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHas('success');

        $this->assertTrue(password_verify('password-baru', $user->fresh()->password));
        $this->assertNotNull($user->fresh()->password_set_at);
    }

    public function test_password_change_fails_when_current_password_is_wrong(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->from(route('password.edit'))
            ->put(route('password.update'), [
                'current_password' => 'salah',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(password_verify('password-lama', $user->fresh()->password));
    }

    public function test_password_change_requires_a_matching_confirmation(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->from(route('password.edit'))
            ->put(route('password.update'), [
                'current_password' => 'password-lama',
                'password' => 'password-baru',
                'password_confirmation' => 'lain-lagi',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_new_password_must_differ_from_the_current_one(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->from(route('password.edit'))
            ->put(route('password.update'), [
                'current_password' => 'password-lama',
                'password' => 'password-lama',
                'password_confirmation' => 'password-lama',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_oauth_account_without_own_password_can_create_one(): void
    {
        $user = User::factory()->create([
            'role' => RoleEnum::Pelamar,
            'password' => 'kata-sandi-acak-tidak-diketahui',
            'password_set_at' => null,
            'google_id' => 'google-123',
        ]);

        $this->actingAs($user)
            ->get(route('password.edit'))
            ->assertOk()
            ->assertSee('Buat Kata Sandi');

        $this->actingAs($user)
            ->put(route('password.update'), [
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHas('success');

        $fresh = $user->fresh();
        $this->assertTrue(password_verify('password-baru', $fresh->password));
        $this->assertNotNull($fresh->password_set_at);
    }

    public function test_password_form_is_only_available_in_account_settings(): void
    {
        $this->actingAs($this->user(RoleEnum::Pelamar))
            ->get(route('pelamar.profile.edit'))
            ->assertOk()
            ->assertDontSee('Ganti Kata Sandi')
            ->assertDontSee('Status Email');

        $this->actingAs($this->user(RoleEnum::Admin))
            ->get(route('password.edit'))
            ->assertOk()
            ->assertSee('Ganti Kata Sandi')
            ->assertSee(route('password.update'), false);

        $this->actingAs($this->user(RoleEnum::Admin))
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertDontSee('Ganti Kata Sandi')
            ->assertDontSee('Status Email')
            ->assertSee('Ringkasan Akun')
            ->assertSee('NLP Node');
    }

    public function test_update_redirects_back_to_the_requested_page(): void
    {
        $user = $this->user(RoleEnum::Pelamar);

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'password-lama',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
                'redirect_to' => 'pelamar.profile.edit',
            ])
            ->assertRedirect(route('pelamar.profile.edit'));
    }

    public function test_update_falls_back_when_the_redirect_target_is_not_a_route(): void
    {
        $user = $this->user(RoleEnum::Pelamar);

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'password-lama',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
                'redirect_to' => 'route.yang.tidak.ada',
            ])
            ->assertRedirect(route('password.edit'));
    }

    public function test_update_falls_back_when_the_redirect_target_is_outside_the_user_portals(): void
    {
        $user = $this->user(RoleEnum::Pelamar);

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'password-lama',
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
                'redirect_to' => 'home',
            ])
            ->assertRedirect(route('password.edit'));
    }
}
