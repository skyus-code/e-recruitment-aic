<?php

namespace Tests\Unit;

use App\Services\TextPreprocessor;
use PHPUnit\Framework\TestCase;

class TextPreprocessorTest extends TestCase
{
    private TextPreprocessor $preprocessor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->preprocessor = new TextPreprocessor;
    }

    public function test_case_folding_lowercases_and_trims_the_input(): void
    {
        $this->assertSame(
            'guru pendidikan agama islam',
            $this->preprocessor->caseFolding('  Guru Pendidikan Agama Islam  '),
        );
    }

    public function test_tokenize_splits_on_non_alphanumeric_characters_and_drops_single_characters(): void
    {
        $this->assertSame(
            ['guru', 'mengajar'],
            $this->preprocessor->tokenize('A guru; mengajar!'),
        );
    }

    public function test_tokenize_splits_hyphenated_words_and_keeps_digits(): void
    {
        $this->assertSame(
            ['al', 'qur', 'an', '2024'],
            $this->preprocessor->tokenize("Al-Qur'an 2024"),
        );
    }

    public function test_stopword_removal_discards_indonesian_and_english_filler_words(): void
    {
        $this->assertSame(
            ['guru', 'excel'],
            $this->preprocessor->removeStopwords(['yang', 'guru', 'dan', 'excel', 'untuk', 'the']),
        );
    }

    public function test_process_chains_case_folding_tokenizing_and_stopword_removal(): void
    {
        $this->assertSame(
            ['guru', 'excel'],
            $this->preprocessor->process('Guru yang dan Excel'),
        );
    }

    public function test_frequencies_counts_words_and_sorts_them_descending(): void
    {
        $this->assertSame(
            ['guru' => 2, 'excel' => 1],
            $this->preprocessor->frequencies(['guru', 'excel', 'guru']),
        );
    }

    public function test_sections_split_a_cv_into_its_common_headings(): void
    {
        $sections = $this->preprocessor->sections($this->sampleCv());

        $this->assertSame(['AHMAD FADIL'], [trim($sections['lainnya'])]);
        $this->assertSame('Guru Pendidikan Agama Islam', $sections['tentang']);
        $this->assertSame('UIN Jakarta 2018', $sections['pendidikan']);
        $this->assertSame('SMP Al-Hikmah 2019', $sections['pengalaman']);
        $this->assertSame('Fikih Hadis', $sections['keahlian']);
    }

    public function test_sections_drop_empty_headings_from_the_result(): void
    {
        $sections = $this->preprocessor->sections("PENDIDIKAN\nUIN Jakarta 2018");

        $this->assertArrayNotHasKey('lainnya', $sections);
        $this->assertArrayNotHasKey('pengalaman', $sections);
    }

    public function test_locate_section_reports_the_heading_a_phrase_belongs_to(): void
    {
        $sections = $this->preprocessor->sections($this->sampleCv());

        $this->assertSame('pendidikan', $this->preprocessor->locateSection('UIN Jakarta', $sections));
        $this->assertSame('pengalaman', $this->preprocessor->locateSection('SMP Al-Hikmah', $sections));
        $this->assertSame('lainnya', $this->preprocessor->locateSection('AHMAD FADIL', $sections));
    }

    public function test_locate_section_returns_null_for_missing_needles_and_empty_sections(): void
    {
        $sections = $this->preprocessor->sections($this->sampleCv());

        $this->assertNull($this->preprocessor->locateSection(null, $sections));
        $this->assertNull($this->preprocessor->locateSection('', $sections));
        $this->assertNull($this->preprocessor->locateSection('microsoft excel', $sections));
        $this->assertNull($this->preprocessor->locateSection('UIN Jakarta', []));
    }

    private function sampleCv(): string
    {
        return implode("\n", [
            'AHMAD FADIL',
            'PROFIL',
            'Guru Pendidikan Agama Islam',
            '',
            'PENDIDIKAN',
            'UIN Jakarta 2018',
            '',
            'PENGALAMAN KERJA',
            'SMP Al-Hikmah 2019',
            '',
            'KEAHLIAN',
            'Fikih Hadis',
        ]);
    }
}
