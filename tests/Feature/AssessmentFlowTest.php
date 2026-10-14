<?php

namespace Tests\Feature;

use App\Models\LoveLanguage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_page_redirects_to_home_when_name_is_missing_from_session(): void
    {
        $response = $this->get('/assessment');

        $response->assertRedirect('/');
    }

    public function test_assessment_page_returns_ok_and_passes_love_languages_with_statements_when_name_in_session(): void
    {
        $this->seed();

        $response = $this->withSession(['name' => 'Budi Santoso'])->get('/assessment');

        $response->assertOk();
        $response->assertViewIs('assessment');
        $response->assertViewHas('loveLanguages');

        $loveLanguages = $response->viewData('loveLanguages');
        $this->assertCount(5, $loveLanguages);
        foreach ($loveLanguages as $loveLanguage) {
            $this->assertCount(5, $loveLanguage->statements);
        }
    }

    public function test_assessment_page_renders_all_five_love_languages_and_twenty_five_statements(): void
    {
        $this->seed();

        $response = $this->withSession(['name' => 'Budi Santoso'])->get('/assessment');

        $response->assertOk();
        $response->assertSee('<form', false);
        $response->assertSee('action="'.url('/assessment').'"', false);
        $response->assertSee('name="_token"', false);

        /** @var Collection<LoveLanguage> $loveLanguages */
        $loveLanguages = $response->viewData('loveLanguages');

        foreach ($loveLanguages as $loveLanguage) {
            $response->assertSee($loveLanguage->name);
            foreach ($loveLanguage->statements as $statement) {
                $response->assertSee($statement->body);
                $response->assertSee('name="answers['.$statement->id.']"', false);
            }
        }

        $response->assertSee('Sangat Tidak Setuju');
        $response->assertSee('Tidak Setuju');
        $response->assertSee('Netral');
        $response->assertSee('Setuju');
        $response->assertSee('Sangat Setuju');
    }

    public function test_assessment_page_includes_multi_step_alpine_flow_structure_and_controls(): void
    {
        $this->seed();

        $response = $this->withSession(['name' => 'Budi Santoso'])->get('/assessment');

        $response->assertOk();
        $response->assertSee('x-data', false);
        $response->assertSee('currentStep', false);
        $response->assertSee('x-show="currentStep ===', false);
        $response->assertSee('Langkah', false);
        $response->assertSee('dari 5', false);
        $response->assertSee('Kembali');
        $response->assertSee('x-show="currentStep > 1"', false);
        $response->assertSee('Lanjut');
        $response->assertSee('Kirim');
        $response->assertSee('type="submit"', false);
    }

    public function test_assessment_page_uses_rawblock_styling_and_circular_radio_buttons(): void
    {
        $this->seed();

        $response = $this->withSession(['name' => 'Budi Santoso'])->get('/assessment');

        $response->assertOk();
        $response->assertSee('radio-circle', false);
        $response->assertSee('font-headline', false);
        $response->assertSee('font-body', false);
    }
}
