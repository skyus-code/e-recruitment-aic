<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Komponen form harus meneruskan atribut HTML tambahan (step, min, max,
 * autofocus, dst.) ke elemen kontrolnya, bukan ke pembungkusnya.
 */
class FormComponentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // x-input-error membutuhkan bag error yang dibagikan middleware HTTP.
        $this->withViewErrors([]);
    }

    public function test_input_forwards_extra_attributes_to_the_input_element(): void
    {
        $this->blade('<x-input type="number" name="gpa" label="IPK" step="0.01" min="0" max="4" autofocus />')
            ->assertSee('step="0.01"', false)
            ->assertSee('min="0"', false)
            ->assertSee('max="4"', false)
            ->assertSee('autofocus="autofocus"', false)
            ->assertDontSee('<div class="w-full" step=', false);
    }

    public function test_input_still_renders_its_declared_props_as_html(): void
    {
        $this->blade('<x-input type="file" name="cv_file" label="CV" required />')
            ->assertSee('type="file"', false)
            ->assertSee('name="cv_file"', false)
            ->assertSee('id="cv_file"', false)
            ->assertSee('required', false)
            ->assertSee('class="input', false);
    }

    public function test_textarea_forwards_extra_attributes_to_the_textarea_element(): void
    {
        $this->blade('<x-textarea name="notes" rows="6" maxlength="500" />')
            ->assertSee('rows="6"', false)
            ->assertSee('maxlength="500"', false)
            ->assertDontSee('<div class="w-full" maxlength=', false);
    }

    public function test_select_forwards_extra_attributes_to_the_select_element(): void
    {
        $this->blade('<x-select name="status" :options="[\'a\' => \'A\']" data-track="screening" />')
            ->assertSee('data-track="screening"', false)
            ->assertDontSee('<div class="w-full" data-track=', false);
    }

    public function test_input_keeps_the_file_input_styling_variant(): void
    {
        $this->blade('<x-input type="file" name="ijazah_file" />')
            ->assertSee('file:mr-3', false);

        $this->blade('<x-input type="text" name="full_name" />')
            ->assertDontSee('file:mr-3', false);
    }
}
