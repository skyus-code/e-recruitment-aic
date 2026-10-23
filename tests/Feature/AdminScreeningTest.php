<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatusEnum;
use App\Enums\RoleEnum;
use App\Mail\ApplicationStatusMail;
use App\Models\Application;
use App\Models\CvFile;
use App\Models\User;
use App\Services\CvParserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminScreeningTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => RoleEnum::Admin]);
    }

    private function screenedApplication(): Application
    {
        $application = Application::factory()->create();

        $application->job->keywords()->create([
            'keyword' => 'fikih',
            'category' => 'hard_skill',
            'weight' => 60,
            'is_required' => true,
        ]);

        $application->job->keywords()->create([
            'keyword' => 'balaghah',
            'category' => 'hard_skill',
            'weight' => 40,
        ]);

        CvFile::create([
            'application_id' => $application->id,
            'file_name' => 'cv.pdf',
            'file_path' => 'cv/dummy.pdf',
            'file_size' => 128,
            'mime_type' => 'application/pdf',
            'file_hash' => str_repeat('c', 64),
        ]);

        return $application;
    }

    public function test_screening_queue_renders_for_admin(): void
    {
        $this->screenedApplication();

        $this->actingAs($this->admin())
            ->get('/admin/screening')
            ->assertOk();
    }

    public function test_admin_can_run_the_nlp_pipeline_from_the_screening_queue(): void
    {
        $application = $this->screenedApplication();

        $this->mock(CvParserService::class, function ($mock): void {
            $mock->shouldReceive('parse')->once()->andReturn([
                'raw_text' => "KEAHLIAN\nFikih\n",
                'total_words' => 2,
                'parsing_status' => 'success',
                'message' => null,
            ]);
        });

        $response = $this->actingAs($this->admin())
            ->post('/admin/screening/'.$application->id);

        $response->assertRedirect('/admin/screening/'.$application->id);
        $response->assertSessionHas('success');
        $response->assertSessionHas('message');

        $application->refresh();
        $this->assertSame(60.0, (float) $application->score);
        $this->assertSame(ApplicationStatusEnum::Screening, $application->status);
    }

    public function test_failed_parsing_surfaces_an_error_message_to_the_admin(): void
    {
        $application = $this->screenedApplication();

        $this->mock(CvParserService::class, function ($mock): void {
            $mock->shouldReceive('parse')->once()->andReturn([
                'raw_text' => '',
                'total_words' => 0,
                'parsing_status' => 'failed',
                'message' => 'Berkas rusak.',
            ]);
        });

        $response = $this->actingAs($this->admin())
            ->post('/admin/screening/'.$application->id);

        $response->assertSessionHas('error');
        $response->assertSessionHas('message', 'Berkas rusak.');
        $this->assertSame(0.0, (float) $application->fresh()->score);
    }

    public function test_screening_detail_page_renders_with_the_score_breakdown(): void
    {
        $application = $this->screenedApplication();

        $this->mock(CvParserService::class, function ($mock): void {
            $mock->shouldReceive('parse')->once()->andReturn([
                'raw_text' => "KEAHLIAN\nFikih\n",
                'total_words' => 2,
                'parsing_status' => 'success',
                'message' => null,
            ]);
        });

        $this->actingAs($this->admin())->post('/admin/screening/'.$application->id);

        $this->actingAs($this->admin())
            ->get('/admin/screening/'.$application->id)
            ->assertOk()
            ->assertSee('fikih');
    }

    public function test_cv_preview_is_served_inline_without_forcing_a_download(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('cv/dummy.pdf', '%PDF-1.4 test');

        $application = $this->screenedApplication();

        $this->actingAs($this->admin())
            ->get('/files/cv/'.$application->id.'/preview')
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename=cv.pdf');
    }

    public function test_admin_can_pass_a_candidate_to_the_next_stage(): void
    {
        $application = $this->screenedApplication();

        $response = $this->actingAs($this->admin())->post('/admin/decide/'.$application->id, [
            'decision' => 'passed',
            'decision_note' => 'Skor memadai, lanjut wawancara.',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertSame(ApplicationStatusEnum::Passed, $application->fresh()->status);
    }

    public function test_admin_can_reject_a_candidate(): void
    {
        $application = $this->screenedApplication();

        $this->actingAs($this->admin())->post('/admin/decide/'.$application->id, [
            'decision' => 'rejected',
        ])->assertStatus(302);

        $this->assertSame(ApplicationStatusEnum::Rejected, $application->fresh()->status);
    }

    public function test_decision_panel_shows_lolos_once_the_candidate_is_passed(): void
    {
        $application = $this->screenedApplication();

        $this->actingAs($this->admin())->post('/admin/decide/'.$application->id, ['decision' => 'passed']);

        $this->actingAs($this->admin())
            ->get('/admin/screening/'.$application->id)
            ->assertOk()
            ->assertSee('id="decision-outcome"', false)
            ->assertDontSee('LOLOS KE WAWANCARA');
    }

    public function test_decision_panel_shows_tertolak_once_the_candidate_is_rejected(): void
    {
        $application = $this->screenedApplication();

        $this->actingAs($this->admin())->post('/admin/decide/'.$application->id, ['decision' => 'rejected']);

        $this->actingAs($this->admin())
            ->get('/admin/screening/'.$application->id)
            ->assertOk()
            ->assertSee('id="decision-outcome"', false)
            ->assertSee('Tertolak')
            ->assertDontSee('✖ TOLAK');
    }

    public function test_an_unknown_decision_is_rejected(): void
    {
        $application = $this->screenedApplication();

        $this->actingAs($this->admin())
            ->post('/admin/decide/'.$application->id, ['decision' => 'maybe'])
            ->assertSessionHasErrors('decision');

        $this->assertSame(ApplicationStatusEnum::Applied, $application->fresh()->status);
    }

    public function test_pelamar_cannot_trigger_the_screening_pipeline(): void
    {
        $application = $this->screenedApplication();
        $pelamar = User::factory()->create(['role' => RoleEnum::Pelamar]);

        $this->actingAs($pelamar)
            ->post('/admin/screening/'.$application->id)
            ->assertForbidden();

        $this->assertNull($application->fresh()->score);
    }

    public function test_passing_a_candidate_emails_the_status_notification(): void
    {
        Mail::fake();

        $application = $this->screenedApplication();

        $this->actingAs($this->admin())->post('/admin/decide/'.$application->id, [
            'decision' => 'passed',
        ]);

        Mail::assertSent(ApplicationStatusMail::class, function (ApplicationStatusMail $mail) use ($application): bool {
            return $mail->hasTo($application->user->email)
                && $mail->userName === $application->user->name
                && $mail->status === 'passed';
        });
    }

    public function test_rejecting_a_candidate_emails_the_status_notification(): void
    {
        Mail::fake();

        $application = $this->screenedApplication();

        $this->actingAs($this->admin())->post('/admin/decide/'.$application->id, [
            'decision' => 'rejected',
        ]);

        Mail::assertSent(ApplicationStatusMail::class, fn (ApplicationStatusMail $mail): bool => $mail->status === 'rejected');
    }
}
