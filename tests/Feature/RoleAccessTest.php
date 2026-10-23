<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_every_protected_area(): void
    {
        $this->get('/pelamar/dashboard')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/manajer/dashboard')->assertRedirect('/login');
        $this->get('/admin/screening')->assertRedirect('/login');
    }

    public function test_pelamar_can_open_their_own_dashboard_but_not_the_staff_areas(): void
    {
        $pelamar = User::factory()->create(['role' => RoleEnum::Pelamar]);

        $this->actingAs($pelamar)->get('/pelamar/dashboard')->assertOk();

        $this->actingAs($pelamar)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($pelamar)->get('/admin/screening')->assertForbidden();
        $this->actingAs($pelamar)->get('/manajer/dashboard')->assertForbidden();
        $this->actingAs($pelamar)->get('/manajer/decisions')->assertForbidden();
    }

    public function test_admin_can_open_admin_area_but_not_pelamar_or_manajer_area(): void
    {
        $admin = User::factory()->create(['role' => RoleEnum::Admin]);

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();

        $this->actingAs($admin)->get('/pelamar/dashboard')->assertForbidden();
        $this->actingAs($admin)->get('/manajer/dashboard')->assertForbidden();
    }

    public function test_manajer_can_open_manajer_area_but_not_admin_area(): void
    {
        $manajer = User::factory()->create(['role' => RoleEnum::Manajer]);

        $this->actingAs($manajer)->get('/manajer/dashboard')->assertOk();

        $this->actingAs($manajer)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($manajer)->get('/admin/screening')->assertForbidden();
        $this->actingAs($manajer)->get('/pelamar/dashboard')->assertForbidden();
    }

    public function test_authenticated_users_can_still_reach_the_public_pages(): void
    {
        $pelamar = User::factory()->create(['role' => RoleEnum::Pelamar]);

        $this->actingAs($pelamar)->get('/')->assertOk();
        $this->actingAs($pelamar)->get('/lowongan')->assertOk();
    }
}
