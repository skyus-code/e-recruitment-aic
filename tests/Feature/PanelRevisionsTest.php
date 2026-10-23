<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Application;
use App\Models\CvFile;
use App\Models\DegreeCertificationFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PanelRevisionsTest extends TestCase
{
    use RefreshDatabase;

    private function user(RoleEnum $role, array $attributes = []): User
    {
        return User::factory()->create(['role' => $role, ...$attributes]);
    }

    public function test_profile_skills_are_synchronized_and_case_insensitive(): void
    {
        $applicant = $this->user(RoleEnum::Pelamar);
        $person = $applicant->person()->create();
        $person->skills()->create(['skill_name' => 'Backend', 'level' => 'Ahli']);
        $person->skills()->create(['skill_name' => 'Legacy', 'level' => 'Pemula']);

        $this->actingAs($applicant)->post('/pelamar/profile/skills', [
            'skills' => ['Backend', 'Node.js', 'Next.js', 'PHP', 'MongoDB', 'MySQL', 'Postgre', 'JS', 'CSS', 'HTML', 'Laravel'],
        ])->assertRedirect('/pelamar/profile');

        $this->assertSame(['Backend', 'Node.js', 'Next.js', 'PHP', 'MongoDB', 'MySQL', 'Postgre', 'JS', 'CSS', 'HTML', 'Laravel'], $person->skills()->orderBy('id')->pluck('skill_name')->all());
        $this->assertSame('Ahli', $person->skills()->where('skill_name', 'Backend')->value('level'));
        $this->actingAs($applicant)->post('/pelamar/profile/skills', ['skills' => ['PHP', 'php']])->assertSessionHasErrors('skills');
        $this->actingAs($applicant)->post('/pelamar/profile/skills', ['skills' => []])->assertRedirect('/pelamar/profile');
        $this->assertDatabaseCount('person_skills', 0);
    }

    public function test_profile_contains_tag_editor_and_no_account_security_cards(): void
    {
        $this->actingAs($this->user(RoleEnum::Pelamar))->get('/pelamar/profile')
            ->assertOk()->assertSee('name="skills[]"', false)->assertSee('keydown.enter.prevent', false)
            ->assertSee('x-on:paste', false)->assertSee('text.split', false)
            ->assertDontSee('Status Email')->assertDontSee('Keamanan Akun');
    }

    public function test_degree_from_application_is_listed_once_and_can_be_previewed(): void
    {
        Storage::fake('local');
        $applicant = $this->user(RoleEnum::Pelamar);
        $application = Application::factory()->create(['user_id' => $applicant->id]);
        Storage::disk('local')->put('ijazah/degree.pdf', '%PDF-1.4');
        DegreeCertificationFile::create([
            'application_id' => $application->id,
            'file_name' => 'degree.pdf',
            'file_path' => 'ijazah/degree.pdf',
            'file_size' => 8,
            'mime_type' => 'application/pdf',
            'file_hash' => hash('sha256', 'degree'),
            'uploaded_at' => now(),
        ]);

        $this->actingAs($applicant)->get('/pelamar/cv-files')->assertOk()
            ->assertSee('Ijazah · degree.pdf')->assertSee('/files/ijazah/'.$application->id.'/preview', false)
            ->assertSee('/files/ijazah/'.$application->id, false);
        $this->assertSame(1, DegreeCertificationFile::where('application_id', $application->id)->count());
        $this->actingAs($applicant)->get('/files/ijazah/'.$application->id)->assertDownload();
        $this->actingAs($applicant)->get('/files/ijazah/'.$application->id.'/preview')->assertOk();
    }

    public function test_cv_file_preview_download_delete_and_ownership_are_enforced(): void
    {
        Storage::fake('local');
        $owner = $this->user(RoleEnum::Pelamar);
        $other = $this->user(RoleEnum::Pelamar);
        Storage::disk('local')->put('cv/'.$owner->id.'/personal.pdf', '%PDF-1.4 personal');
        $file = CvFile::create([
            'file_name' => 'personal.pdf', 'file_path' => 'cv/'.$owner->id.'/personal.pdf',
            'file_size' => 17, 'mime_type' => 'application/pdf', 'file_hash' => str_repeat('a', 64), 'uploaded_at' => now(),
        ]);

        $this->actingAs($owner)->get('/pelamar/cv-files/'.$file->id.'/preview')->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename=personal.pdf');
        $this->actingAs($owner)->get('/pelamar/cv-files/'.$file->id.'/download')->assertDownload('personal.pdf');
        $this->actingAs($other)->get('/pelamar/cv-files/'.$file->id.'/preview')->assertForbidden();
        $this->actingAs($other)->delete('/pelamar/cv-files/'.$file->id)->assertForbidden();
        $this->actingAs($owner)->delete('/pelamar/cv-files/'.$file->id)->assertRedirect('/pelamar/cv-files')->assertSessionHas('success');
        $this->assertDatabaseMissing('cv_files', ['id' => $file->id]);
        Storage::disk('local')->assertMissing('cv/'.$owner->id.'/personal.pdf');
    }

    public function test_application_cv_cannot_be_deleted_and_analysis_uses_inline_preview(): void
    {
        Storage::fake('local');
        $owner = $this->user(RoleEnum::Pelamar);
        $application = Application::factory()->create(['user_id' => $owner->id]);
        Storage::disk('local')->put('cv/'.$owner->id.'/submitted.pdf', '%PDF-1.4 submitted');
        $cv = $application->cvFile()->create([
            'file_name' => 'submitted.pdf', 'file_path' => 'cv/'.$owner->id.'/submitted.pdf',
            'file_size' => 20, 'mime_type' => 'application/pdf', 'file_hash' => str_repeat('b', 64),
        ]);

        $this->actingAs($owner)->delete('/pelamar/cv-files/'.$cv->id)->assertRedirect('/pelamar/cv-files')->assertSessionHas('error');
        $this->assertDatabaseHas('cv_files', ['id' => $cv->id]);
        Storage::disk('local')->assertExists('cv/'.$owner->id.'/submitted.pdf');
        $this->actingAs($owner)->get('/pelamar/analysis/'.$application->id)->assertOk()
            ->assertSee('/files/cv/'.$application->id.'/preview', false)
            ->assertSee('files/cv/'.$application->id.'"', false);
        $this->actingAs($owner)->get('/files/cv/'.$application->id.'/preview')->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename=submitted.pdf');
    }

    public function test_cv_file_paths_cannot_escape_storage_root(): void
    {
        $owner = $this->user(RoleEnum::Pelamar);
        $file = CvFile::create([
            'file_name' => 'malicious.pdf', 'file_path' => 'cv/'.$owner->id.'/../secret.pdf',
            'file_size' => 1, 'mime_type' => 'application/pdf', 'file_hash' => str_repeat('c', 64), 'uploaded_at' => now(),
        ]);

        $this->actingAs($owner)->get('/pelamar/cv-files/'.$file->id.'/preview')->assertNotFound();
        $this->actingAs($owner)->delete('/pelamar/cv-files/'.$file->id)->assertNotFound();
    }

    public function test_skill_chip_can_only_be_removed_using_its_button(): void
    {
        $html = $this->actingAs($this->user(RoleEnum::Pelamar))->get('/pelamar/profile')->assertOk()->getContent();
        self::assertStringNotContainsString('keydown.backspace', $html);
        self::assertStringContainsString('x-on:click="skills.splice(index, 1)"', $html);
    }

    public function test_application_detail_has_no_ijazah_upload_field(): void
    {
        $application = Application::factory()->create();
        $this->actingAs($application->user)->get('/pelamar/applications/'.$application->id)
            ->assertOk()->assertDontSee('name="ijazah_file"', false)->assertSee('Belum ada');
    }

    public function test_new_application_badge_is_admin_only_and_clears_when_detail_is_opened(): void
    {
        $application = Application::factory()->create();
        $admin = $this->user(RoleEnum::Admin);

        Application::factory()->create();
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertSee('<span>!</span>2</span>', false);
        $this->actingAs($this->user(RoleEnum::Manajer))->get('/manajer/dashboard')->assertOk()->assertDontSee('!1');
        $this->actingAs($admin)->get('/admin/screening/'.$application->id)->assertOk();
        $this->assertNotNull($application->fresh()->admin_seen_at);
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertSee('<span>!</span>1</span>', false);
    }

    public function test_schedule_interviewer_accepts_a_manager_or_manual_name(): void
    {
        $admin = $this->user(RoleEnum::Admin);
        $this->user(RoleEnum::Manajer, ['username' => 'Ayu Manager']);
        $this->user(RoleEnum::Manajer, ['username' => 'Zahra Manager']);
        $applicant = $this->user(RoleEnum::Pelamar);
        $application = Application::factory()->create(['user_id' => $applicant->id]);
        $screeningPage = $this->actingAs($admin)->get('/admin/screening/'.$application->id)->assertOk()
            ->assertSee('Ayu Manager')->assertSee('Nama Pewawancara Lain...');
        self::assertLessThan(
            strpos($screeningPage->getContent(), 'Zahra Manager'),
            strpos($screeningPage->getContent(), 'Ayu Manager'),
        );

        $payload = [
            'interview_date' => now()->addDay()->toDateString(), 'interview_time' => '10:00',
            'interview_type' => 'online', 'interview_location' => 'https://meet.example.test/interview',
            'interviewer_name' => '__other__',
        ];
        $this->actingAs($admin)->post('/admin/schedule/'.$application->id, $payload)->assertSessionHasErrors('interviewer_other');
        $this->actingAs($admin)->post('/admin/schedule/'.$application->id, [...$payload, 'interviewer_other' => 'Pewawancara Tamu'])->assertSessionHasNoErrors();
        $this->assertSame('Pewawancara Tamu', $application->fresh()->interviewSchedules()->latest('id')->value('interviewer_name'));
        $this->actingAs($admin)->get('/admin/screening/'.$application->id)->assertOk()
            ->assertSee('value="__other__" selected', false)->assertSee('value="Pewawancara Tamu"', false);
        $this->actingAs($admin)->post('/admin/schedule/'.$application->id, [...$payload, 'interviewer_name' => 'Ayu Manager'])
            ->assertSessionHasNoErrors();
        $this->assertSame(1, $application->fresh()->interviewSchedules()->count());
        $this->assertSame('Ayu Manager', $application->fresh()->interviewSchedules()->value('interviewer_name'));
        $this->actingAs($admin)->post('/admin/schedule/'.$application->id, [...$payload, 'interviewer_name' => 'Not A Manager'])
            ->assertSessionHasErrors('interviewer_name');
    }

    public function test_schedule_list_displays_total_and_all_paginated_records(): void
    {
        $admin = $this->user(RoleEnum::Admin);
        $applicant = $this->user(RoleEnum::Pelamar);
        $applications = Application::factory()->count(51)->create(['user_id' => $applicant->id]);

        foreach ($applications as $application) {
            $application->interviewSchedules()->create([
                'interview_date' => now()->addDay()->toDateString(), 'interview_time' => '10:00',
                'interview_type' => 'online', 'interview_location' => 'https://meet.example.test/interview',
                'interviewer_name' => 'Ayu Manager',
            ]);
        }

        $this->actingAs($admin)->get('/admin/schedules')->assertOk()->assertSee('dari 51 jadwal')->assertSee('Next');
        $this->actingAs($admin)->get('/admin/schedules?page=3')->assertOk()->assertSee('dari 51 jadwal');
    }

    public function test_saved_assessment_is_db_locked_and_only_manager_can_write(): void
    {
        $application = Application::factory()->create(['status' => ApplicationStatusEnum::Interview]);
        $application->interviewSchedules()->create([
            'interview_date' => now()->addDay()->toDateString(), 'interview_time' => '10:00',
            'interview_type' => 'online', 'interview_location' => 'https://meet.example.test/interview',
            'interviewer_name' => 'Ayu Manager',
        ]);
        $assessment = $application->assessment()->create([
            'assessor_name' => 'Ayu Manager', 'assessed_at' => now()->toDateString(),
            'final_score' => 80, 'decision' => 'lulus', 'conclusion' => 'Baik.',
        ]);

        $this->actingAs($this->user(RoleEnum::Manajer))->get('/manajer/interviews/'.$application->id)
            ->assertOk()->assertSee('Penilaian Berhasil Tersimpan.')->assertSee('Edit Penilaian')->assertSee('saved && !editing', false);
        $this->actingAs($this->user(RoleEnum::Admin))->post('/manajer/interviews/'.$application->id.'/assessment', ['conclusion' => 'Dilarang'])
            ->assertForbidden();
        $this->assertSame('Baik.', $assessment->fresh()->conclusion);
    }
}
