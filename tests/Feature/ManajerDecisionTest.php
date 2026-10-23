<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatusEnum;
use App\Enums\RoleEnum;
use App\Mail\ApplicationStatusMail;
use App\Models\Application;
use App\Models\InterviewAssessment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ManajerDecisionTest extends TestCase
{
    use RefreshDatabase;

    private function manajer(): User
    {
        return User::factory()->create(['role' => RoleEnum::Manajer]);
    }

    private function interviewedApplication(bool $withSchedule = true): Application
    {
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::Interview,
            'score' => 88.00,
        ]);

        if ($withSchedule) {
            $application->interviewSchedule()->create([
                'interview_date' => now()->addDay()->toDateString(),
                'interview_time' => '09:00',
                'interview_type' => 'online',
                'interview_location' => 'https://meet.example.test/arr-rahman',
                'interviewer_name' => 'Drs. Abdul Qadir',
                'status' => 'scheduled',
            ]);
        }

        return $application;
    }

    public function test_decisions_page_renders_for_the_manajer(): void
    {
        $this->interviewedApplication();

        $this->actingAs($this->manajer())
            ->get('/manajer/decisions')
            ->assertOk();
    }

    public function test_interview_list_and_detail_render_for_the_manajer(): void
    {
        $application = $this->interviewedApplication();

        $this->actingAs($this->manajer())
            ->get('/manajer/interviews')
            ->assertOk();

        $this->actingAs($this->manajer())
            ->get('/manajer/interviews/'.$application->id)
            ->assertOk();
    }

    public function test_dossier_list_renders_for_the_manajer(): void
    {
        $this->interviewedApplication();

        $this->actingAs($this->manajer())
            ->get('/manajer/dossier')
            ->assertOk();
    }

    public function test_a_decision_requires_interview_notes(): void
    {
        $application = $this->interviewedApplication();

        $this->actingAs($this->manajer())
            ->post('/manajer/decide/'.$application->id, ['decision' => 'hired'])
            ->assertSessionHasErrors('notes');

        $this->assertSame(ApplicationStatusEnum::Interview, $application->fresh()->status);
    }

    public function test_a_decision_requires_a_known_decision_value(): void
    {
        $application = $this->interviewedApplication();

        $this->actingAs($this->manajer())
            ->post('/manajer/decide/'.$application->id, [
                'decision' => 'maybe',
                'notes' => 'Catatan.',
            ])
            ->assertSessionHasErrors('decision');

        $this->assertSame(ApplicationStatusEnum::Interview, $application->fresh()->status);
    }

    public function test_manajer_can_hire_a_candidate_and_store_the_notes(): void
    {
        $application = $this->interviewedApplication();

        $response = $this->actingAs($this->manajer())->post('/manajer/decide/'.$application->id, [
            'decision' => 'hired',
            'notes' => 'Sangat memahami materi dan cocok dengan budaya kerja.',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $response->assertSessionHas('message');

        $application->refresh();
        $this->assertSame(ApplicationStatusEnum::Hired, $application->status);

        $schedule = $application->interviewSchedule()->first();
        $this->assertSame('hired', $schedule->status);
        $this->assertSame('Sangat memahami materi dan cocok dengan budaya kerja.', $schedule->notes);
    }

    public function test_manajer_can_reject_a_candidate(): void
    {
        $application = $this->interviewedApplication();

        $this->actingAs($this->manajer())->post('/manajer/decide/'.$application->id, [
            'decision' => 'rejected',
            'notes' => 'Jawaban wawancara kurang memenuhi kebutuhan formasi.',
        ])->assertStatus(302);

        $this->assertSame(ApplicationStatusEnum::Rejected, $application->fresh()->status);
    }

    public function test_a_decision_does_not_explode_when_no_interview_was_scheduled(): void
    {
        $application = $this->interviewedApplication(withSchedule: false);

        $this->actingAs($this->manajer())->post('/manajer/decide/'.$application->id, [
            'decision' => 'hired',
            'notes' => 'Diputuskan berdasarkan rekam jejak.',
        ])->assertStatus(302);

        $this->assertSame(ApplicationStatusEnum::Hired, $application->fresh()->status);
        $this->assertDatabaseMissing('interview_schedules', ['application_id' => $application->id]);
    }

    public function test_admin_cannot_take_a_final_hiring_decision(): void
    {
        $application = $this->interviewedApplication();
        $admin = User::factory()->create(['role' => RoleEnum::Admin]);

        $this->actingAs($admin)
            ->post('/manajer/decide/'.$application->id, [
                'decision' => 'hired',
                'notes' => 'Tidak berwenang.',
            ])
            ->assertForbidden();

        $this->assertSame(ApplicationStatusEnum::Interview, $application->fresh()->status);
    }

    public function test_interview_decision_sheet_previews_the_cv_inline_instead_of_downloading(): void
    {
        $application = $this->interviewedApplication();

        $this->actingAs($this->manajer())
            ->get('/manajer/interviews/'.$application->id)
            ->assertOk()
            ->assertSee('src="'.route('files.cv.preview', $application).'"', false)
            ->assertDontSee('src="'.route('files.cv', $application).'"', false);
    }

    public function test_rekap_pelamar_is_downloaded_as_a_csv_attachment(): void
    {
        $application = $this->interviewedApplication();

        $response = $this->actingAs($this->manajer())
            ->get('/manajer/decisions/'.$application->id.'/rekap');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertDownload();

        $disposition = (string) $response->headers->get('content-disposition');
        $this->assertStringStartsWith('attachment;', $disposition);
        $this->assertStringContainsString('rekap-', $disposition);
    }

    public function test_pelamar_without_a_schedule_still_gets_a_rekap_download(): void
    {
        $application = $this->interviewedApplication(withSchedule: false);

        $this->actingAs($this->manajer())
            ->get('/manajer/decisions/'.$application->id.'/rekap')
            ->assertOk()
            ->assertDownload();
    }

    public function test_manajer_can_save_the_inline_interview_assessment(): void
    {
        $application = $this->interviewedApplication();

        $scores = [];
        foreach (InterviewAssessment::ASPECTS as $aspect) {
            $scores[$aspect['code']] = 5;
        }

        $response = $this->actingAs($this->manajer())
            ->post('/manajer/interviews/'.$application->id.'/assessment', [
                'assessor_name' => 'Drs. Abdul Qadir',
                'assessed_at' => now()->toDateString(),
                'scores' => $scores,
                'decision' => 'lulus',
                'conclusion' => 'Kandidat direkomendasikan.',
            ]);

        $response->assertRedirect('/manajer/interviews/'.$application->id);
        $response->assertSessionHas('success');

        $assessment = $application->fresh()->assessment;
        $this->assertNotNull($assessment);
        $this->assertSame(100.0, (float) $assessment->final_score);
        $this->assertSame('lulus', $assessment->decision);
        $this->assertCount(count(InterviewAssessment::ASPECTS), $assessment->items);
    }

    public function test_assessment_final_score_follows_the_weighted_formula(): void
    {
        $application = $this->interviewedApplication();

        $response = $this->actingAs($this->manajer())
            ->post('/manajer/interviews/'.$application->id.'/assessment', [
                'scores' => ['hard_skill' => 4],
            ]);

        $response->assertSessionHas('success');

        $assessment = $application->fresh()->assessment;
        $this->assertSame(20.0, (float) $assessment->final_score);
        $hardSkill = $assessment->items->firstWhere('aspect', 'hard_skill');
        $this->assertNotNull($hardSkill);
        $this->assertSame(20.0, (float) $hardSkill->value);
    }

    public function test_interview_sheet_renders_the_assessment_form(): void
    {
        $application = $this->interviewedApplication();

        $this->actingAs($this->manajer())
            ->get('/manajer/interviews/'.$application->id)
            ->assertOk()
            ->assertSee('Form Penilaian Aspek Pelamar');
    }

    public function test_assessment_rejects_score_out_of_range(): void
    {
        $application = $this->interviewedApplication();

        $this->actingAs($this->manajer())
            ->post('/manajer/interviews/'.$application->id.'/assessment', [
                'scores' => ['hard_skill' => 6],
            ])
            ->assertSessionHasErrors('scores.hard_skill');
    }

    public function test_hiring_a_candidate_emails_the_status_notification(): void
    {
        Mail::fake();

        $application = $this->interviewedApplication();

        $this->actingAs($this->manajer())->post('/manajer/decide/'.$application->id, [
            'decision' => 'hired',
            'notes' => 'Kandidat terbaik pada sesi ini.',
        ]);

        Mail::assertSent(ApplicationStatusMail::class, function (ApplicationStatusMail $mail) use ($application): bool {
            return $mail->hasTo($application->user->email)
                && $mail->userName === $application->user->name
                && $mail->status === 'hired';
        });
    }

    public function test_rejecting_a_candidate_emails_the_status_notification(): void
    {
        Mail::fake();

        $application = $this->interviewedApplication();

        $this->actingAs($this->manajer())->post('/manajer/decide/'.$application->id, [
            'decision' => 'rejected',
            'notes' => 'Belum sesuai dengan kebutuhan formasi.',
        ]);

        Mail::assertSent(ApplicationStatusMail::class, fn (ApplicationStatusMail $mail): bool => $mail->status === 'rejected');
    }
}
