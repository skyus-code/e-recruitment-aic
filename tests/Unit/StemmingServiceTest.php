<?php

namespace Tests\Unit;

use App\Services\StemmingService;
use PHPUnit\Framework\TestCase;

class StemmingServiceTest extends TestCase
{
    private StemmingService $stemmer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stemmer = new StemmingService;
    }

    public function test_stem_strips_prefixes_from_an_indonesian_verb(): void
    {
        $this->assertSame('ajar', $this->stemmer->stem('mengajar'));
    }

    public function test_stem_keeps_a_word_that_is_already_a_known_root_word(): void
    {
        $this->assertSame('pendidikan', $this->stemmer->stem('pendidikan'));
    }

    public function test_stem_lowercases_the_input(): void
    {
        $this->assertSame('guru', $this->stemmer->stem(' Guru '));
    }

    public function test_stem_leaves_words_shorter_than_four_characters_untouched(): void
    {
        $this->assertSame('ai', $this->stemmer->stem('AI'));
    }

    public function test_stem_delegates_english_words_to_the_snowball_stemmer(): void
    {
        $this->assertSame('teach', $this->stemmer->stem('teaching'));
    }

    public function test_variants_include_both_the_original_and_the_root_form(): void
    {
        $variants = $this->stemmer->variants('mengajar');

        $this->assertContains('mengajar', $variants);
        $this->assertContains('ajar', $variants);
    }

    public function test_variants_return_nothing_for_an_empty_string(): void
    {
        $this->assertSame([], $this->stemmer->variants(''));
    }

    public function test_stem_many_stems_every_word_in_the_batch(): void
    {
        $this->assertSame(['ajar', 'guru'], $this->stemmer->stemMany(['mengajar', 'Guru']));
    }
}
