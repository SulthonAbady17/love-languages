<?php

namespace Tests\Feature;

use App\Models\LoveLanguage;
use App\Models\Result;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompareControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_result_page_renders_the_compare_form_between_the_permanent_link_and_feedback(): void
    {
        $this->seed();
        $user = $this->completedParticipant('Budi Santoso');

        $response = $this->get('/results/'.$user->token);

        $response->assertOk();
        $response->assertSee('Bandingkan dengan Pasangan');
        $response->assertSee('Tautan Hasil Pasangan');
        $response->assertDontSee('Result Token');
        $response->assertSee('name="tokenB"', false);
        $response->assertSee(route('compare.start', $user->token), false);

        $compare = $this->compareSection($response->getContent());
        $this->assertStringContainsString('method="GET"', $compare);
        $this->assertStringContainsString('name="tokenB"', $compare);
        $this->assertStringContainsString('BANDINGKAN', $compare);
        $this->assertStringContainsString('aria-label="Bandingkan"', $compare);

        $html = $response->getContent();
        $asidePosition = strpos($html, 'id="permanent-link"');
        $comparePosition = strpos($html, 'id="compare"');
        $feedbackPosition = strpos($html, 'id="feedback"');
        $this->assertIsInt($asidePosition);
        $this->assertIsInt($comparePosition);
        $this->assertIsInt($feedbackPosition);
        $this->assertTrue($asidePosition < $comparePosition && $comparePosition < $feedbackPosition);
    }

    public function test_submitting_a_partner_token_redirects_to_the_comparison_url(): void
    {
        $this->seed();
        $initiator = $this->completedParticipant('Budi Santoso');
        $partner = $this->completedParticipant('Siti Aminah');

        $response = $this->get(route('compare.start', [
            'tokenA' => $initiator->token,
            'tokenB' => $partner->token,
        ]));

        $response->assertRedirect('/compare/'.$initiator->token.'/'.$partner->token);
    }

    public function test_submitting_a_partner_permalink_redirects_to_the_comparison_url(): void
    {
        $this->seed();
        $initiator = $this->completedParticipant('Budi Santoso');
        $partner = $this->completedParticipant('Siti Aminah');

        $response = $this->get(route('compare.start', [
            'tokenA' => $initiator->token,
            'tokenB' => 'https://example.com/results/'.$partner->token.'/?utm_source=share',
        ]));

        $response->assertRedirect('/compare/'.$initiator->token.'/'.$partner->token);
    }

    public function test_malformed_partner_permalink_redirects_back_to_the_result_page_with_an_inline_error(): void
    {
        $this->seed();
        $initiator = $this->completedParticipant('Budi Santoso');

        $response = $this->get(route('compare.start', [
            'tokenA' => $initiator->token,
            'tokenB' => 'https://example.com/bukan-hasil',
        ]));

        $response->assertRedirect('/results/'.$initiator->token);
        $response->assertSessionHas('error', 'Tautan hasil pasangan tidak ditemukan. Periksa kembali tautannya.');
    }

    public function test_unknown_partner_token_redirects_back_to_the_result_page_with_an_inline_error(): void
    {
        $this->seed();
        $initiator = $this->completedParticipant('Budi Santoso');
        $unknownToken = str_repeat('z', 16);

        $response = $this->get(route('compare.start', [
            'tokenA' => $initiator->token,
            'tokenB' => $unknownToken,
        ]));

        $response->assertRedirect('/results/'.$initiator->token);
        $response->assertSessionHas('error', 'Tautan hasil pasangan tidak ditemukan. Periksa kembali tautannya.');

        $page = $this->get('/results/'.$initiator->token);
        $page->assertOk();
        $this->assertStringContainsString(
            'Tautan hasil pasangan tidak ditemukan. Periksa kembali tautannya.',
            $this->compareSection($page->getContent()),
        );
    }

    public function test_a_non_string_partner_token_is_treated_as_unknown(): void
    {
        $this->seed();
        $initiator = $this->completedParticipant('Budi Santoso');

        $response = $this->get('/compare/'.$initiator->token.'?tokenB[]=x');

        $response->assertRedirect('/results/'.$initiator->token);
        $response->assertSessionHas('error', 'Tautan hasil pasangan tidak ditemukan. Periksa kembali tautannya.');
    }

    public function test_submitting_the_pages_own_token_redirects_back_with_an_inline_error(): void
    {
        $this->seed();
        $initiator = $this->completedParticipant('Budi Santoso');

        $response = $this->get(route('compare.start', [
            'tokenA' => $initiator->token,
            'tokenB' => $initiator->token,
        ]));

        $response->assertRedirect('/results/'.$initiator->token);
        $response->assertSessionHas('error', 'Kamu tidak bisa membandingkan dengan dirimu sendiri.');

        $page = $this->get('/results/'.$initiator->token);
        $page->assertOk();
        $page->assertSee('Kamu tidak bisa membandingkan dengan dirimu sendiri.');
    }

    public function test_comparison_page_renders_a_card_for_each_participant_with_the_initiator_first(): void
    {
        $this->seed();
        $initiator = $this->completedParticipant('Budi Santoso');
        $partner = $this->completedParticipant('Siti Aminah');

        $response = $this->get('/compare/'.$initiator->token.'/'.$partner->token);

        $response->assertOk();
        $response->assertViewIs('compare');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Siti Aminah');
        $response->assertSee('md:grid-cols-2', false);
        $response->assertSee('grid-cols-1', false);

        $html = $response->getContent();
        $this->assertLessThan(
            strpos($html, 'Siti Aminah'),
            strpos($html, 'Budi Santoso'),
            'The Initiating Participant card should come first.',
        );
        $this->assertStringContainsString('Budi Santoso', $this->card($html, $initiator->token));
        $this->assertStringContainsString('Siti Aminah', $this->card($html, $partner->token));
    }

    public function test_each_card_lists_its_participants_love_languages_in_their_own_score_order(): void
    {
        $this->seed();
        $initiatorValues = [1.0, 2.2, 3.0, 4.4, 4.6];
        $partnerValues = [2.6, 5.0, 1.2, 3.8, 4.0];
        $initiator = $this->completedParticipant('Budi Santoso', $initiatorValues);
        $partner = $this->completedParticipant('Siti Aminah', $partnerValues);

        $response = $this->get('/compare/'.$initiator->token.'/'.$partner->token);
        $response->assertOk();

        $html = $response->getContent();
        $initiatorCard = $this->card($html, $initiator->token);
        $partnerCard = $this->card($html, $partner->token);

        $this->assertNotEmpty($initiatorCard);
        $this->assertNotEmpty($partnerCard);
        $this->assertNamesAppearInOrder($initiatorCard, [
            'Physical Touch',
            'Acts of Service',
            'Receiving Gifts',
            'Quality Time',
            'Words of Affirmation',
        ]);
        $this->assertNamesAppearInOrder($partnerCard, [
            'Quality Time',
            'Physical Touch',
            'Acts of Service',
            'Words of Affirmation',
            'Receiving Gifts',
        ]);
    }

    public function test_each_row_shows_the_rank_score_and_a_progress_bar_scaled_to_five(): void
    {
        $this->seed();
        $initiator = $this->completedParticipant('Budi Santoso', [1.0, 2.2, 3.0, 4.4, 4.6]);
        $partner = $this->completedParticipant('Siti Aminah', [2.6, 5.0, 1.2, 3.8, 4.0]);

        $response = $this->get('/compare/'.$initiator->token.'/'.$partner->token);
        $response->assertOk();

        $html = $response->getContent();
        $initiatorCard = $this->card($html, $initiator->token);
        $partnerCard = $this->card($html, $partner->token);

        foreach (['1.0/5.0', '2.2/5.0', '3.0/5.0', '4.4/5.0', '4.6/5.0'] as $score) {
            $this->assertStringContainsString($score, $initiatorCard);
        }
        foreach (['2.6/5.0', '5.0/5.0', '1.2/5.0', '3.8/5.0', '4.0/5.0'] as $score) {
            $this->assertStringContainsString($score, $partnerCard);
        }

        $this->assertStringContainsString('style="width: 20%"', $initiatorCard);
        $this->assertStringContainsString('style="width: 92%"', $initiatorCard);
        $this->assertStringContainsString('style="width: 100%"', $partnerCard);
        $this->assertStringContainsString('style="width: 24%"', $partnerCard);

        foreach ([$initiatorCard, $partnerCard] as $card) {
            foreach ([1, 2, 3, 4, 5] as $rank) {
                $this->assertMatchesRegularExpression('/<span[^>]*>\s*'.$rank.'\s*<\/span>/', $card);
            }
        }
    }

    public function test_comparison_page_has_a_back_link_to_the_initiators_result_page(): void
    {
        $this->seed();
        $initiator = $this->completedParticipant('Budi Santoso');
        $partner = $this->completedParticipant('Siti Aminah');

        $response = $this->get('/compare/'.$initiator->token.'/'.$partner->token);

        $response->assertOk();
        $response->assertSee('href="'.route('results.show', $initiator->token).'"', false);
        $response->assertSee('Kembali ke Hasil');
    }

    public function test_comparison_page_has_a_copyable_permalink_to_revisit_it(): void
    {
        $this->seed();
        $initiator = $this->completedParticipant('Budi Santoso');
        $partner = $this->completedParticipant('Siti Aminah');

        $response = $this->get('/compare/'.$initiator->token.'/'.$partner->token);

        $response->assertOk();
        $response->assertSee('id="comparison-permalink"', false);
        $response->assertSee('Simpan tautan ini untuk melihat perbandingan kalian kembali');
        $response->assertSee(route('compare.show', [$initiator->token, $partner->token]), false);
        $response->assertSee('aria-label="Salin Tautan"', false);
    }

    public function test_an_unknown_token_in_the_comparison_url_returns_404(): void
    {
        $this->seed();
        $initiator = $this->completedParticipant('Budi Santoso');
        $unknownToken = str_repeat('z', 16);

        $this->get('/compare/'.$unknownToken.'/'.$initiator->token)->assertNotFound();
        $this->get('/compare/'.$initiator->token.'/'.$unknownToken)->assertRedirect('/results/'.$initiator->token);
    }

    public function test_malformed_tokens_in_the_comparison_url_return_404_before_reaching_the_controller(): void
    {
        $this->seed();
        $initiator = $this->completedParticipant('Budi Santoso');
        $partner = $this->completedParticipant('Siti Aminah');

        $this->get('/compare/NOPE/'.$partner->token)->assertNotFound();
        $this->get('/compare/'.$initiator->token.'/NOPE')->assertNotFound();
        $this->get('/compare/NOPE?token='.$partner->token)->assertNotFound();
    }

    public function test_comparing_a_participant_with_themselves_redirects_back_with_an_inline_error(): void
    {
        $this->seed();
        $initiator = $this->completedParticipant('Budi Santoso');

        $response = $this->get('/compare/'.$initiator->token.'/'.$initiator->token);

        $response->assertRedirect('/results/'.$initiator->token);
        $response->assertSessionHas('error', 'Kamu tidak bisa membandingkan dengan dirimu sendiri.');
    }

    /**
     * The "Compare with Partner" section on the Result Page.
     */
    private function compareSection(string $html): string
    {
        preg_match('/<section id="compare".*?<\/section>/s', $html, $matches);

        return $matches[0] ?? '';
    }

    /**
     * The card markup for one Participant on the Comparison Page.
     */
    private function card(string $html, string $token): string
    {
        preg_match('/<article[^>]*data-participant="'.preg_quote($token, '/').'".*?<\/article>/s', $html, $matches);

        return $matches[0] ?? '';
    }

    /**
     * @param  list<string>  $names
     */
    private function assertNamesAppearInOrder(string $html, array $names): void
    {
        $offset = 0;

        foreach ($names as $name) {
            $position = strpos($html, e($name), $offset);
            $this->assertNotFalse($position, "Expected to find {$name} in order.");
            $offset = $position + strlen($name);
        }
    }

    /**
     * A Participant with one Result per seeded Love Language (ordered by id),
     * each carrying the score at its position in $values.
     *
     * @param  list<float>  $values
     */
    private function completedParticipant(string $name, array $values = [1.0, 2.2, 3.0, 4.4, 4.6]): User
    {
        $user = User::factory()->create(['name' => $name]);

        $loveLanguageIds = LoveLanguage::query()->orderBy('id')->pluck('id');

        foreach ($values as $index => $value) {
            Result::factory()->create([
                'user_id' => $user->id,
                'love_language_id' => $loveLanguageIds[$index],
                'value' => $value,
            ]);
        }

        return $user;
    }
}
