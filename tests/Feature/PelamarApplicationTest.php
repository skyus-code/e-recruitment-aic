<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatusEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\EnsureVerifiedEmailForApplication;
use App\Models\Application;
use App\Models\CvFile;
use App\Models\DegreeCertificationFile;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PelamarApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function pelamar(): User
    {
        return User::factory()->verified()->create(['role' => RoleEnum::Pelamar]);
    }

    public function test_applicant_can_open_their_vacancy_list(): void
    {
        JobPosting::factory()->create(['title' => 'Guru Tahfidz']);

        $this->actingAs($this->pelamar())
            ->get('/pelamar/jobs')
            ->assertOk()
            ->assertSee('Guru Tahfidz');
    }

    public function test_unverified_applicant_cannot_apply_from_page_or_direct_request(): void
    {
        $user = User::factory()->unverified()->create(['role' => RoleEnum::Pelamar]);
        $job = JobPosting::factory()->create();

        $this->actingAs($user)->get('/pelamar/jobs')
            ->assertOk()
            ->assertSee('Verifikasi Email Sekarang');

        $this->actingAs($user)->get('/pelamar/apply/'.$job->id)
            ->assertRedirect()
            ->assertSessionHas('error', EnsureVerifiedEmailForApplication::MESSAGE);

        $this->actingAs($user)->post('/pelamar/apply/'.$job->id, [])
            ->assertRedirect()
            ->assertSessionHas('error', EnsureVerifiedEmailForApplication::MESSAGE);

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_applicant_can_submit_an_application_with_pdf_attachments(): void
    {
        $user = $this->pelamar();
        $job = JobPosting::factory()->create();

        $response = $this->actingAs($user)->post('/pelamar/apply/'.$job->id, [
            'cv_file' => UploadedFile::fake()->create('cv.pdf', 64, 'application/pdf'),
            'ijazah_file' => UploadedFile::fake()->create('ijazah.pdf', 64, 'application/pdf'),
            'agreement' => '1',
        ]);

        $application = Application::where('user_id', $user->id)->firstOrFail();

        $response->assertRedirect('/pelamar/applications/'.$application->id);
        $response->assertSessionHas('success');

        $this->assertSame(ApplicationStatusEnum::Applied, $application->status);
        $this->assertNotNull($application->applied_at);
        $this->assertDatabaseHas('cv_files', ['application_id' => $application->id]);
        $this->assertDatabaseHas('degree_certification_files', ['application_id' => $application->id]);
        $this->assertFileExists(storage_path('app/private/'.$application->cvFile->file_path));
    }

    public function test_application_requires_both_files_and_the_pernyataan_checkbox(): void
    {
        $job = JobPosting::factory()->create();

        $this->actingAs($this->pelamar())
            ->post('/pelamar/apply/'.$job->id, [])
            ->assertSessionHasErrors(['cv_file', 'ijazah_file', 'agreement']);

        $this->assertSame(0, Application::count());
    }

    public function test_applicant_can_submit_using_the_default_cv_without_reuploading(): void
    {
        $user = $this->pelamar();
        $job = JobPosting::factory()->create();

        Storage::disk('local')->put('cv/'.$user->id.'/default-cv.pdf', '%PDF-1.4 default cv');

        CvFile::create([
            'file_name' => 'default-cv.pdf',
            'file_path' => 'cv/'.$user->id.'/default-cv.pdf',
            'file_size' => 20,
            'mime_type' => 'application/pdf',
            'file_hash' => str_repeat('b', 64),
            'uploaded_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/pelamar/apply/'.$job->id, [
            'ijazah_file' => UploadedFile::fake()->create('ijazah.pdf', 64, 'application/pdf'),
            'agreement' => '1',
        ]);

        $application = Application::where('user_id', $user->id)->firstOrFail();

        $response->assertRedirect('/pelamar/applications/'.$application->id);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cv_files', ['application_id' => $application->id]);
        $this->assertFileExists(storage_path('app/private/'.$application->cvFile->file_path));
    }

    public function test_apply_page_renders_when_a_default_cv_is_available(): void
    {
        $user = $this->pelamar();
        $job = JobPosting::factory()->create();

        Storage::disk('local')->put('cv/'.$user->id.'/default-cv.pdf', '%PDF-1.4 default cv');

        $cvFile = CvFile::create([
            'file_name' => 'default-cv.pdf',
            'file_path' => 'cv/'.$user->id.'/default-cv.pdf',
            'file_size' => 20,
            'mime_type' => 'application/pdf',
            'file_hash' => str_repeat('b', 64),
            'uploaded_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/pelamar/apply/'.$job->id)
            ->assertOk()
            ->assertSee('CV default terpilih otomatis')
            ->assertSee('default-cv.pdf')
            ->assertSee('Lihat CV saat ini')
            ->assertSee('/pelamar/cv-files/'.$cvFile->id.'/preview', false)
            ->assertSee('data-preview-target="cv-current-preview"', false)
            ->assertSee('data-preview-fallback=', false);
    }

    public function test_apply_page_reuses_existing_degree_and_submission_copies_it(): void
    {
        Storage::fake('local');
        $user = $this->pelamar();
        $previousApplication = Application::factory()->create(['user_id' => $user->id]);
        $sourcePath = 'ijazah/'.$user->id.'/degree.pdf';
        Storage::disk('local')->put($sourcePath, '%PDF-1.4 degree');
        DegreeCertificationFile::create([
            'application_id' => $previousApplication->id,
            'file_name' => 'degree.pdf',
            'file_path' => $sourcePath,
            'file_size' => 17,
            'mime_type' => 'application/pdf',
            'file_hash' => str_repeat('e', 64),
            'uploaded_at' => now(),
        ]);
        $job = JobPosting::factory()->create();

        $page = $this->actingAs($user)->get('/pelamar/apply/'.$job->id);
        $page->assertOk()
            ->assertSee('degree.pdf')->assertDontSee('Diambil dari Kelola Berkas Lamaran')
            ->assertSee('/files/ijazah/'.$previousApplication->id.'/preview', false)
            ->assertSee('name="ijazah_file"', false)
            ->assertSee('x-text="ijazahName"', false)
            ->assertSee('Lihat Ijazah saat ini')
            ->assertSee('data-preview-target="ijazah-current-preview"', false)
            ->assertDontSee('Ijazah tersimpan');
        self::assertSame(2, substr_count($page->getContent(), 'class="flex h-60 cursor-pointer'));

        $response = $this->actingAs($user)->post('/pelamar/apply/'.$job->id, [
            'cv_file' => UploadedFile::fake()->create('cv.pdf', 64, 'application/pdf'),
            'agreement' => '1',
        ]);
        $newApplication = Application::where('user_id', $user->id)->where('job_id', $job->id)->firstOrFail();
        $response->assertRedirect('/pelamar/applications/'.$newApplication->id)->assertSessionHasNoErrors();
        self::assertNotSame($sourcePath, $newApplication->degreeCertificationFile->file_path);
        self::assertSame('degree.pdf', $newApplication->degreeCertificationFile->file_name);
        Storage::disk('local')->assertExists($sourcePath);
        Storage::disk('local')->assertExists($newApplication->degreeCertificationFile->file_path);
    }

    public function test_applying_with_the_same_saved_files_does_not_duplicate_the_file_list(): void
    {
        Storage::fake('local');
        $user = $this->pelamar();
        $sourceApplication = Application::factory()->create(['user_id' => $user->id]);
        $cvContents = '%PDF-1.4 cv yang sama';
        $degreeContents = '%PDF-1.4 ijazah yang sama';
        $cvPath = 'cv/'.$user->id.'/cv-yang-sama.pdf';
        $degreePath = 'ijazah/'.$user->id.'/ijazah-yang-sama.pdf';
        Storage::disk('local')->put($cvPath, $cvContents);
        Storage::disk('local')->put($degreePath, $degreeContents);

        CvFile::create([
            'file_name' => 'cv-yang-sama.pdf',
            'file_path' => $cvPath,
            'file_size' => strlen($cvContents),
            'mime_type' => 'application/pdf',
            'file_hash' => hash('sha256', $cvContents),
            'uploaded_at' => now(),
        ]);
        DegreeCertificationFile::create([
            'application_id' => $sourceApplication->id,
            'file_name' => 'ijazah-yang-sama.pdf',
            'file_path' => $degreePath,
            'file_size' => strlen($degreeContents),
            'mime_type' => 'application/pdf',
            'file_hash' => hash('sha256', $degreeContents),
            'uploaded_at' => now(),
        ]);

        $job = JobPosting::factory()->create();
        $response = $this->actingAs($user)->post('/pelamar/apply/'.$job->id, ['agreement' => '1']);
        $application = Application::where('user_id', $user->id)->where('job_id', $job->id)->firstOrFail();
        $response->assertRedirect('/pelamar/applications/'.$application->id)->assertSessionHasNoErrors();

        $this->assertDatabaseCount('cv_files', 2);
        $this->assertDatabaseCount('degree_certification_files', 2);
        $listing = $this->actingAs($user)->get('/pelamar/cv-files')->assertOk();
        self::assertSame(1, substr_count($listing->getContent(), 'cv-yang-sama.pdf'));
        self::assertSame(1, substr_count($listing->getContent(), 'ijazah-yang-sama.pdf'));
    }

    public function test_applicant_cannot_apply_twice_to_the_same_vacancy(): void
    {
        $user = $this->pelamar();
        $job = JobPosting::factory()->create();

        Application::factory()->create(['user_id' => $user->id, 'job_id' => $job->id]);

        $response = $this->actingAs($user)->post('/pelamar/apply/'.$job->id, [
            'cv_file' => UploadedFile::fake()->create('cv.pdf', 64, 'application/pdf'),
            'ijazah_file' => UploadedFile::fake()->create('ijazah.pdf', 64, 'application/pdf'),
            'agreement' => '1',
        ]);

        $response->assertRedirect('/pelamar/applications');
        $response->assertSessionHas('error');
        $this->assertSame(1, Application::count());
    }

    public function test_closed_vacancies_cannot_be_applied_to(): void
    {
        $job = JobPosting::factory()->closed()->create();

        $this->actingAs($this->pelamar())
            ->get('/pelamar/apply/'.$job->id)
            ->assertNotFound();
    }

    public function test_applicant_sees_only_their_own_application_list(): void
    {
        $mine = $this->pelamar();
        Application::factory()->create(); // milik pelamar lain

        $response = $this->actingAs($mine)->get('/pelamar/applications');

        $response->assertOk();
        $response->assertDontSee(Application::first()->user->name);
    }

    public function test_applicant_cannot_open_another_applicants_application_detail(): void
    {
        $foreign = Application::factory()->create();

        $this->actingAs($this->pelamar())
            ->get('/pelamar/applications/'.$foreign->id)
            ->assertForbidden();
    }

    public function test_applicant_cannot_open_another_applicants_nlp_analysis(): void
    {
        $foreign = Application::factory()->create();

        $this->actingAs($this->pelamar())
            ->get('/pelamar/analysis/'.$foreign->id)
            ->assertForbidden();
    }

    public function test_applicant_can_open_their_own_application_detail_before_screening(): void
    {
        $user = $this->pelamar();
        $application = Application::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get('/pelamar/applications/'.$application->id)
            ->assertOk();
    }

    public function test_applicant_cannot_read_a_foreigners_cv_file(): void
    {
        $foreign = Application::factory()->create();

        $this->actingAs($this->pelamar())
            ->get('/files/cv/'.$foreign->id)
            ->assertForbidden();
    }

    public function test_applicant_can_read_their_own_cv_file(): void
    {
        $user = $this->pelamar();
        $application = Application::factory()->create(['user_id' => $user->id]);

        Storage::disk('local')->put('cv/own-cv-test.pdf', '%PDF-1.4 fake cv');

        $application->cvFile()->create([
            'file_name' => 'cv.pdf',
            'file_path' => 'cv/own-cv-test.pdf',
            'file_size' => 10,
            'mime_type' => 'application/pdf',
            'file_hash' => str_repeat('a', 64),
        ]);

        $this->actingAs($user)
            ->get('/files/cv/'.$application->id)
            ->assertOk();
    }

    public function test_a_missing_cv_file_returns_a_not_found_response(): void
    {
        $user = $this->pelamar();
        $application = Application::factory()->create(['user_id' => $user->id]);

        $application->cvFile()->create([
            'file_name' => 'cv.pdf',
            'file_path' => 'cv/missing-on-purpose.pdf',
            'file_size' => 10,
            'mime_type' => 'application/pdf',
            'file_hash' => str_repeat('a', 64),
        ]);

        $this->actingAs($user)
            ->get('/files/cv/'.$application->id)
            ->assertNotFound();
    }
}
