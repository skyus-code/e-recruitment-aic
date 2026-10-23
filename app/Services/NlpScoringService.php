<?php

namespace App\Services;

use App\Enums\ApplicationStatusEnum;
use App\Models\Application;
use App\Models\CvParsedData;
use App\Models\DataStemmer;
use App\Models\JobKeyword;
use App\Models\ScoreDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Otak pipeline NLP: mem-parse CV, mem-preprocess, men-stem, mencocokkan
 * kata kunci lowongan, lalu menghitung skor akhir.
 *
 * Final Score = (total bobot keyword cocok / total bobot semua keyword) * 100
 */
class NlpScoringService
{
    public function __construct(
        private readonly CvParserService $parser,
        private readonly TextPreprocessor $preprocessor,
        private readonly StemmingService $stemmer,
    ) {}

    /**
     * Menjalankan screening penuh untuk satu lamaran.
     *
     * @return array{
     *     score: float,
     *     matched_count: int,
     *     total_keywords: int,
     *     matched_weight: float,
     *     total_weight: float,
     *     total_words: int,
     *     unique_terms: int,
     *     parsing_status: string,
     *     parsing_message: ?string,
     *     details: array<int, array<string, mixed>>,
     * }
     */
    public function screen(Application $application): array
    {
        $application->loadMissing(['cvFile', 'job.keywords']);

        // 1. PARSING -------------------------------------------------------
        $rawText = '';
        $totalWords = 0;
        $parsingStatus = 'failed';
        $parsingMessage = null;

        if ($application->cvFile) {
            $absolutePath = Storage::disk('local')->path($application->cvFile->file_path);
            $parsed = $this->parser->parse($absolutePath);

            $rawText = $parsed['raw_text'];
            $totalWords = $parsed['total_words'];
            $parsingStatus = $parsed['parsing_status'];
            $parsingMessage = $parsed['message'];

            CvParsedData::updateOrCreate(
                ['cv_file_id' => $application->cvFile->id],
                [
                    'raw_text' => $rawText,
                    'file_name' => $application->cvFile->file_name,
                    'file_size' => $application->cvFile->file_size,
                    'total_words' => $totalWords,
                    'total_characters' => mb_strlen($rawText, 'UTF-8'),
                    'parsing_status' => $parsingStatus,
                    'error_message' => $parsingStatus === 'failed' ? $parsingMessage : null,
                    'parser_version' => CvParserService::PARSER_VERSION,
                    'parsed_at' => now(),
                ],
            );
        } else {
            $parsingMessage = 'Lamaran ini belum memiliki berkas CV.';
        }

        // 2. PREPROCESSING -------------------------------------------------
        $tokens = $this->preprocessor->process($rawText);
        $sections = $this->preprocessor->sections($rawText);

        // 3. STEMMING ------------------------------------------------------
        $keywords = $application->job?->keywords ?? collect();
        $keywordCategories = $this->keywordCategoryMap($keywords);

        $groups = [];
        foreach ($tokens as $token) {
            $stemmed = $this->stemmer->stem($token);

            $key = $token.'|'.$stemmed;
            $groups[$key] ??= [
                'original_word' => $token,
                'stemmed_word' => $stemmed,
                'frequency' => 0,
            ];
            $groups[$key]['frequency']++;
        }

        $this->persistStemmerData($application, $groups, $keywordCategories);

        // 4. MATCHING & SCORING --------------------------------------------
        $documentTerms = $this->buildTermIndex($tokens);
        $details = [];

        $totalWeight = 0.0;
        $matchedWeight = 0.0;
        $matchedCount = 0;

        foreach ($keywords as $keyword) {
            $totalWeight += (float) $keyword->weight;

            $isMatched = $this->matches($keyword->keyword, $documentTerms);
            $weight = $isMatched ? (float) $keyword->weight : 0.0;

            if ($isMatched) {
                $matchedCount++;
                $matchedWeight += $weight;
            }

            $details[] = [
                'job_keyword_id' => $keyword->id,
                'keyword' => $keyword->keyword,
                'stemmed_keyword' => $this->stemmedForm($keyword->keyword),
                'is_matched' => $isMatched,
                'matched_score' => round($weight, 2),
                'found_in_section' => $isMatched
                    ? $this->preprocessor->locateSection($keyword->keyword, $sections)
                    : null,
            ];
        }

        $score = $totalWeight > 0
            ? round(($matchedWeight / $totalWeight) * 100, 2)
            : 0.0;

        // 5. PERSIST ---------------------------------------------------------
        DB::transaction(function () use ($application, $details, $score, $rawText): void {
            ScoreDetail::where('application_id', $application->id)->delete();

            foreach (array_chunk($details, 100) as $chunk) {
                ScoreDetail::insert(array_map(
                    fn (array $detail): array => array_merge($detail, [
                        'application_id' => $application->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]),
                    $chunk,
                ));
            }

            $matchedKeywords = collect($details)
                ->where('is_matched', true)
                ->pluck('keyword')
                ->values()
                ->all();

            $application->update([
                'score' => $score,
                'cv_path' => $application->cvFile?->file_path,
                'cv_text' => $rawText,
                'matched_keywords' => $matchedKeywords !== [] ? $matchedKeywords : null,
            ]);
        });

        if ($application->status === ApplicationStatusEnum::Applied) {
            $application->update(['status' => ApplicationStatusEnum::Screening]);
        }

        return [
            'score' => $score,
            'matched_count' => $matchedCount,
            'total_keywords' => $keywords->count(),
            'matched_weight' => round($matchedWeight, 2),
            'total_weight' => round($totalWeight, 2),
            'total_words' => $totalWords,
            'unique_terms' => count($groups),
            'parsing_status' => $parsingStatus,
            'parsing_message' => $parsingMessage,
            'details' => $details,
        ];
    }

    /**
     * Indeks seluruh bentuk normalisasi dari teks CV untuk pencocokan cepat.
     *
     * @param  array<int, string>  $tokens
     * @return array<string, true>
     */
    private function buildTermIndex(array $tokens): array
    {
        $index = [];

        foreach ($tokens as $token) {
            foreach ($this->stemmer->variants($token) as $variant) {
                $index[$variant] = true;
            }

            $index[$token] = true;
        }

        return $index;
    }

    /**
     * Mencocokkan sebuah kata kunci (bisa terdiri dari beberapa kata) terhadap indeks.
     *
     * @param  array<string, true>  $documentTerms
     */
    private function matches(string $keyword, array $documentTerms): bool
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($keyword, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($words === []) {
            return false;
        }

        foreach ($words as $word) {
            $matched = false;

            foreach ($this->stemmer->variants($word) as $variant) {
                if (isset($documentTerms[$variant])) {
                    $matched = true;
                    break;
                }
            }

            if (! $matched) {
                return false;
            }
        }

        return true;
    }

    /**
     * Bentuk ter-stem dari sebuah kata kunci (multi-kata digabung spasi),
     * disimpan pada kolom `stemmed_keyword` tabel score_details.
     */
    private function stemmedForm(string $keyword): string
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($keyword, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return collect($words)
            ->map(fn (string $word): string => $this->stemmer->stem($word))
            ->implode(' ');
    }

    /**
     * @param  Collection<int, JobKeyword>  $keywords
     * @return array<string, string>
     */
    private function keywordCategoryMap($keywords): array
    {
        $map = [];

        foreach ($keywords as $keyword) {
            foreach ($this->stemmer->variants($keyword->keyword) as $variant) {
                $map[$variant] = $keyword->category->value;
            }
        }

        return $map;
    }

    /**
     * Menyimpan hasil stemming ke tabel `data_stemmer`.
     *
     * @param  array<string, array{original_word: string, stemmed_word: string, frequency: int}>  $groups
     * @param  array<string, string>  $keywordCategories
     */
    private function persistStemmerData(Application $application, array $groups, array $keywordCategories): void
    {
        DataStemmer::where('application_id', $application->id)->delete();

        if ($groups === []) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($groups as $group) {
            $category = $keywordCategories[$group['stemmed_word']]
                ?? $keywordCategories[$group['original_word']]
                ?? 'general';

            $rows[] = [
                'application_id' => $application->id,
                'job_id' => $application->job_id,
                'original_word' => mb_substr($group['original_word'], 0, 100, 'UTF-8'),
                'stemmed_word' => mb_substr($group['stemmed_word'], 0, 100, 'UTF-8'),
                'word_category' => $category,
                'is_matched_with_job' => $category !== 'general',
                'frequency_in_cv' => $group['frequency'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DataStemmer::insert($chunk);
        }
    }
}
