<?php

namespace Tests\Feature;

use App\Models\JobKeyword;
use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_for_guests(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_job_listing_renders_and_shows_open_vacancies(): void
    {
        JobPosting::factory()->create(['title' => 'Guru Bahasa Arab']);

        $this->get('/lowongan')
            ->assertOk()
            ->assertSee('Guru Bahasa Arab');
    }

    public function test_job_listing_can_be_searched_by_keyword(): void
    {
        JobPosting::factory()->create(['title' => 'Guru Bahasa Arab']);
        JobPosting::factory()->create(['title' => 'Staf Administrasi']);

        $this->get('/lowongan?q=Bahasa')
            ->assertOk()
            ->assertSee('Guru Bahasa Arab')
            ->assertDontSee('Staf Administrasi');
    }

    public function test_vacancy_detail_renders_with_its_keyword_weights(): void
    {
        $job = JobPosting::factory()->create();

        $job->keywords()->create([
            'keyword' => 'fikih',
            'category' => 'hard_skill',
            'weight' => 10,
            'is_required' => true,
        ]);

        $this->get('/lowongan/'.$job->id)
            ->assertOk()
            ->assertSee('fikih')
            ->assertSee('Bobot Kata Kunci');
    }

    public function test_unknown_vacancy_returns_a_not_found_response(): void
    {
        $this->get('/lowongan/999')->assertNotFound();
    }

    public function test_closed_vacancies_are_not_open_for_application(): void
    {
        $job = JobPosting::factory()->closed()->create();

        $this->assertFalse($job->isOpen());
    }

    public function test_keyword_weight_is_persisted_for_the_scoring_pipeline(): void
    {
        $job = JobPosting::factory()->create();

        $job->keywords()->create([
            'keyword' => 'microsoft excel',
            'category' => 'hard_skill',
            'weight' => 12.5,
        ]);

        $this->assertDatabaseHas('job_keywords', [
            'job_id' => $job->id,
            'keyword' => 'microsoft excel',
            'weight' => 12.5,
        ]);

        $this->assertSame(1, $job->keywords()->count());
        $this->assertInstanceOf(JobKeyword::class, $job->keywords()->first());
    }
}
