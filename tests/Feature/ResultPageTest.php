<?php

namespace Tests\Feature;

use App\Models\LoveLanguage;
use App\Models\Result;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_token_returns_ok_with_participant_name_heading_and_permanent_link_note(): void
    {
        $this->seed();
        $user = $this->completedParticipant('Budi Santoso');

        $response = $this->get('/results/'.$user->token);

        $response->assertOk();
        $response->assertViewIs('results');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Hasil Penilaian Bahasa Cinta Anda');
        $response->assertSee('Simpan tautan ini untuk melihat hasilmu kembali');
    }

    public function test_all_five_love_languages_are_listed_in_score_order_with_one_decimal_scores_and_descriptions(): void
    {
        $this->seed();
        $user = $this->completedParticipant('Budi Santoso', [1 => 1.0, 2 => 2.2, 3 => 3.14159, 4 => 4.4, 5 => 4.6]);

        $response = $this->get('/results/'.$user->token);
        $response->assertOk();

        $namesByScore = LoveLanguage::orderBy('id')->pluck('name')->reverse()->values();
        $response->assertSeeInOrder($namesByScore->all());
        $this->assertCount(5, $namesByScore);
        $this->assertSame(5, substr_count($response->getContent(), '<article'));

        foreach ([1.0, 2.2, 3.1, 4.4, 4.6] as $score) {
            $response->assertSee(number_format($score, 1));
        }
        $response->assertDontSee('3.14');

        foreach (LoveLanguage::pluck('description') as $description) {
            $response->assertSee($description);
        }
    }

    public function test_top_scoring_love_language_is_shown_in_an_inverted_black_block(): void
    {
        $this->seed();
        $values = [1 => 1.0, 2 => 2.2, 3 => 3.0, 4 => 4.4, 5 => 4.6];
        $user = $this->completedParticipant('Budi Santoso', $values);

        $response = $this->get('/results/'.$user->token);
        $response->assertOk();

        $highlight = $this->highlightBlock($response->getContent());
        $topId = (int) array_search(max($values), $values, true);
        $topName = LoveLanguage::find($topId)->name;

        $this->assertStringContainsString($topName, $highlight);
        $this->assertStringContainsString('bg-black', $highlight);
        $this->assertStringContainsString('text-white', $highlight);
        $this->assertStringContainsString('font-headline', $highlight);
    }

    public function test_stored_advice_renders_a_bordered_block_directly_under_the_top_love_language(): void
    {
        $this->seed();
        $user = $this->completedParticipant('Budi Santoso');
        $user->update(['advice' => 'Luangkan waktu berkualitas setiap hari. Sapa pasanganmu dengan pujian tulus di pagi hari.']);

        $response = $this->get('/results/'.$user->token);
        $response->assertOk();

        $html = $response->getContent();
        $block = $this->adviceBlock($html);

        $this->assertNotSame('', $block);
        $this->assertStringContainsString('Luangkan waktu berkualitas setiap hari.', $block);
        $this->assertStringContainsString('border-[3px] border-black', $block);
        $this->assertStringContainsString('font-mono', $block);
        $this->assertGreaterThan(
            strpos($html, 'id="top-love-language"'),
            strpos($html, 'id="advice"'),
        );
    }

    public function test_participant_without_advice_renders_no_advice_block(): void
    {
        $this->seed();
        $user = $this->completedParticipant('Budi Santoso');

        $response = $this->get('/results/'.$user->token);
        $response->assertOk();

        $this->assertSame('', $this->adviceBlock($response->getContent()));
    }

    public function test_unknown_token_returns_404(): void
    {
        $this->seed();

        $this->get('/results/not-a-real-token')->assertNotFound();
    }

    public function test_page_is_accessible_without_an_active_session(): void
    {
        $this->seed();
        $user = $this->completedParticipant('Budi Santoso');

        $response = $this->get('/results/'.$user->token);
        $response->assertOk();
        $response->assertSessionMissing('name');

        $response->assertSee('Budi Santoso');
    }

    /**
     * The inverted block that highlights the top Love Language.
     */
    private function highlightBlock(string $html): string
    {
        preg_match('/<section id="top-love-language".*?<\/section>/s', $html, $matches);

        return $matches[0] ?? '';
    }

    /**
     * The Advice block shown under the highlighted top Love Language.
     */
    private function adviceBlock(string $html): string
    {
        preg_match('/<section id="advice".*?<\/section>/s', $html, $matches);

        return $matches[0] ?? '';
    }

    /**
     * A Participant with one Result per seeded Love Language (keyed by
     * love_language_id), in the given score order.
     *
     * @param  array<int, float>  $values
     */
    private function completedParticipant(string $name, array $values = [1 => 1.0, 2 => 2.2, 3 => 3.0, 4 => 4.4, 5 => 4.6]): User
    {
        $user = User::factory()->create(['name' => $name]);

        foreach ($values as $loveLanguageId => $value) {
            Result::factory()->create([
                'user_id' => $user->id,
                'love_language_id' => $loveLanguageId,
                'value' => $value,
            ]);
        }

        return $user;
    }
}
