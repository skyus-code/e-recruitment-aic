<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke test: setiap halaman yang dirender kontroler harus bisa ditampilkan
 * tanpa error variabel Blade ataupun error komponen.
 */
class PageRenderingTest extends TestCase
{
    use RefreshDatabase;

    private function actAsRole(string $role): User
    {
        return User::factory()->verified()->create(['role' => RoleEnum::from($role)]);
    }

    private function scoredApplication(): Application
    {
        $application = Application::factory()->create(['score' => 85.00]);

        $application->job->keywords()->create([
            'keyword' => 'fikih',
            'category' => 'hard_skill',
            'weight' => 100,
        ]);

        return $application;
    }

    public function test_pelamar_pages_render(): void
    {
        $pelamar = $this->actAsRole('pelamar');
        $job = JobPosting::factory()->create();
        $application = $this->scoredApplication();
        $application->update(['user_id' => $pelamar->id]);

        $this->actingAs($pelamar)->get('/pelamar/profile')->assertOk();
        $this->actingAs($pelamar)->get('/pelamar/cv-files')->assertOk();
        $this->actingAs($pelamar)->get('/pelamar/interviews')->assertOk();
        $this->actingAs($pelamar)->get('/pelamar/jobs')->assertOk();
        $this->actingAs($pelamar)->get('/pelamar/apply/'.$job->id)->assertOk();
        $this->actingAs($pelamar)->get('/pelamar/applications')->assertOk();
        $this->actingAs($pelamar)->get('/pelamar/applications/'.$application->id)->assertOk();
        $this->actingAs($pelamar)->get('/pelamar/analysis/'.$application->id)->assertOk();
        $this->actingAs($pelamar)->get('/pelamar/dashboard')->assertOk();
    }

    public function test_admin_pages_render(): void
    {
        $admin = $this->actAsRole('admin');
        $job = JobPosting::factory()->create();
        $application = $this->scoredApplication();

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/admin/jobs')->assertOk();
        $this->actingAs($admin)->get('/admin/jobs/'.$job->id)->assertOk();
        $this->actingAs($admin)->get('/admin/jobs/create')->assertOk();
        $this->actingAs($admin)->get('/admin/jobs/'.$job->id.'/edit')->assertOk();
        $this->actingAs($admin)->get('/admin/screening')->assertOk();
        $this->actingAs($admin)->get('/admin/screening/'.$application->id)->assertOk();
        $this->actingAs($admin)->get('/admin/schedules')->assertOk();
        $this->actingAs($admin)->get('/admin/statistics')->assertOk();
        $this->actingAs($admin)->get('/admin/settings')->assertOk();
        $this->actingAs($admin)->get('/admin/keywords')->assertOk();
        $this->actingAs($admin)->get('/admin/keywords/create')->assertOk();
    }

    public function test_manajer_pages_render(): void
    {
        $manajer = $this->actAsRole('manajer');
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::Interview,
            'score' => 90.00,
        ]);

        $application->interviewSchedule()->create([
            'interview_date' => now()->addDay()->toDateString(),
            'interview_time' => '09:00',
            'interview_type' => 'online',
            'interview_location' => 'https://meet.example.test/arr-rahman',
            'interviewer_name' => 'Drs. Abdul Qadir',
            'status' => 'scheduled',
        ]);

        $this->actingAs($manajer)->get('/manajer/dashboard')->assertOk();
        $this->actingAs($manajer)->get('/manajer/dossier')->assertOk();
        $this->actingAs($manajer)->get('/manajer/dossier/'.$application->id)->assertOk();
        $this->actingAs($manajer)->get('/manajer/interviews')->assertOk();
        $this->actingAs($manajer)->get('/manajer/interviews/'.$application->id)->assertOk();
        $this->actingAs($manajer)->get('/manajer/decisions')->assertOk();
    }

    public function test_public_pages_render_for_guests(): void
    {
        $job = JobPosting::factory()->create();
        JobPosting::factory()->closed()->create();

        $this->get('/')->assertOk();
        $this->get('/lowongan')->assertOk();
        $this->get('/lowongan/'.$job->id)->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }
}
