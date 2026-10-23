<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatusEnum;
use App\Models\Application;
use App\Models\CvFile;
use App\Services\CvParserService;
use App\Services\NlpScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NlpScoringTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $parsed
     */
    private function mockParser(array $parsed): void
    {
        $this->mock(CvParserService::class, function ($mock) use ($parsed): void {
            $mock->shouldReceive('parse')->atLeast()->once()->andReturn($parsed);
        });
    }

    private function applicationWithCv(): Application
    {
        $application = Application::factory()->create();

        CvFile::create([
            'application_id' => $application->id,
            'file_name' => 'cv.pdf',
            'file_path' => 'cv/dummy.pdf',
            'file_size' => 128,
            'mime_type' => 'application/pdf',
            'file_hash' => str_repeat('b', 64),
        ]);

        return $application;
    }

    private function addKeyword(Application $application, string $keyword, float $weight, string $category = 'hard_skill'): void
    {
        $application->job->keywords()->create([
            'keyword' => $keyword,
            'category' => $category,
            'weight' => $weight,
            'is_required' => false,
        ]);
    }

    public function test_score_is_the_matched_weight_share_of_the_total_weight(): void
    {
        $application = $this->applicationWithCv();

        $this->addKeyword($application, 'fikih', 10);
        $this->addKeyword($application, 'hadis', 20);
        $this->addKeyword($application, 'balaghah', 30);
        $this->addKeyword($application, 'komunikasi', 40, 'soft_skill');

        $this->mockParser([
            'raw_text' => "PENDIDIKAN\nFikih dan Hadis\n",
            'total_words' => 4,
            'parsing_status' => 'success',
            'message' => null,
        ]);

        $result = app(NlpScoringService::class)->screen($application->fresh());

        $this->assertSame(30.0, $result['score']);
        $this->assertSame(2, $result['matched_count']);
        $this->assertSame(4, $result['total_keywords']);
        $this->assertSame(30.0, $result['matched_weight']);
        $this->assertSame(100.0, $result['total_weight']);
        $this->assertSame(4, $result['total_words']);
        $this->assertSame('success', $result['parsing_status']);

        $application->refresh();
        $this->assertSame(30.0, (float) $application->score);
        $this->assertSame(ApplicationStatusEnum::Screening, $application->status);
    }

    public function test_scoring_persists_parsed_text_stems_and_keyword_details(): void
    {
        $application = $this->applicationWithCv();

        $this->addKeyword($application, 'fikih', 50);
        $this->addKeyword($application, 'balaghah', 50);

        $this->mockParser([
            'raw_text' => "KEAHLIAN\nFikih Fikih Fikih\n",
            'total_words' => 4,
            'parsing_status' => 'success',
            'message' => null,
        ]);

        app(NlpScoringService::class)->screen($application->fresh());

        $parsed = $application->parsedData()->first();
        $this->assertSame('success', $parsed->parsing_status);
        $this->assertSame(4, $parsed->total_words);

        $this->assertSame(2, $application->scoreDetails()->count());

        $matched = $application->scoreDetails()->where('keyword', 'fikih')->first();
        $this->assertTrue((bool) $matched->is_matched);
        $this->assertSame(50.0, (float) $matched->matched_score);
        $this->assertSame('keahlian', $matched->found_in_section);

        $unmatched = $application->scoreDetails()->where('keyword', 'balaghah')->first();
        $this->assertFalse((bool) $unmatched->is_matched);
        $this->assertSame(0.0, (float) $unmatched->matched_score);
        $this->assertNull($unmatched->found_in_section);

        $stem = $application->stemmerData()->where('original_word', 'fikih')->first();
        $this->assertNotNull($stem);
        $this->assertSame(3, (int) $stem->frequency_in_cv);
        $this->assertSame('hard_skill', $stem->word_category);
        $this->assertTrue((bool) $stem->is_matched_with_job);
        $this->assertSame($application->job_id, $stem->job_id);
    }

    public function test_re_running_the_pipeline_replaces_previous_score_details(): void
    {
        $application = $this->applicationWithCv();
        $this->addKeyword($application, 'fikih', 100);

        $this->mockParser([
            'raw_text' => 'Fikih',
            'total_words' => 1,
            'parsing_status' => 'success',
            'message' => null,
        ]);

        $scoring = app(NlpScoringService::class);

        $scoring->screen($application->fresh());
        $scoring->screen($application->fresh());

        $this->assertSame(1, $application->scoreDetails()->count());
        $this->assertSame(100.0, (float) $application->fresh()->score);
    }

    public function test_failed_parsing_scores_zero_and_records_the_failure(): void
    {
        $application = $this->applicationWithCv();
        $this->addKeyword($application, 'fikih', 100);

        $this->mockParser([
            'raw_text' => '',
            'total_words' => 0,
            'parsing_status' => 'failed',
            'message' => 'Berkas bukan PDF yang valid.',
        ]);

        $result = app(NlpScoringService::class)->screen($application->fresh());

        $this->assertSame('failed', $result['parsing_status']);
        $this->assertSame(0.0, $result['score']);
        $this->assertSame('Berkas bukan PDF yang valid.', $result['parsing_message']);

        $this->assertSame('failed', $application->parsedData()->first()->parsing_status);
        $this->assertSame(0.0, (float) $application->fresh()->score);
    }

    public function test_application_without_a_cv_file_is_reported_as_failed_parsing(): void
    {
        $application = Application::factory()->create();
        $this->addKeyword($application, 'fikih', 100);

        $this->mock(CvParserService::class, function ($mock): void {
            $mock->shouldNotReceive('parse');
        });

        $result = app(NlpScoringService::class)->screen($application->fresh());

        $this->assertSame('failed', $result['parsing_status']);
        $this->assertNotNull($result['parsing_message']);
        $this->assertSame(0.0, $result['score']);
    }

    public function test_vacancies_without_keywords_receive_a_zero_score(): void
    {
        $application = $this->applicationWithCv();

        $this->mockParser([
            'raw_text' => 'Fikih dan Hadis',
            'total_words' => 3,
            'parsing_status' => 'success',
            'message' => null,
        ]);

        $result = app(NlpScoringService::class)->screen($application->fresh());

        $this->assertSame(0.0, $result['score']);
        $this->assertSame(0, $result['total_keywords']);
        $this->assertSame(0, $result['matched_count']);
    }

    public function test_multi_word_keywords_require_every_word_to_match(): void
    {
        $application = $this->applicationWithCv();

        $this->addKeyword($application, 'bimbingan konseling', 100);

        $this->mockParser([
            'raw_text' => 'Bimbingan dan konseling siswa',
            'total_words' => 4,
            'parsing_status' => 'success',
            'message' => null,
        ]);

        $result = app(NlpScoringService::class)->screen($application->fresh());
        $this->assertSame(100.0, $result['score']);

        $this->mockParser([
            'raw_text' => 'Bimbingan siswa saja',
            'total_words' => 3,
            'parsing_status' => 'success',
            'message' => null,
        ]);

        $result = app(NlpScoringService::class)->screen($application->fresh());
        $this->assertSame(0.0, $result['score']);
    }
}
