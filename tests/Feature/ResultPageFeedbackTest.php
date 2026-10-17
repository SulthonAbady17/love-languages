<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\LoveLanguage;
use App\Models\Result;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultPageFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_result_page_renders_the_feedback_form_as_the_last_block_when_no_feedback_given(): void
    {
        $this->seed();
        $user = $this->completedParticipant('Budi Santoso');

        $response = $this->get('/results/'.$user->token);

        $response->assertOk();
        $response->assertSee('Feedback');
        $response->assertSee('Sudah coba aplikasi ini? Ceritakan pengembang apa yang kamu pikirkan.');
        $response->assertSee('KIRIM FEEDBACK');
        $response->assertSee('<textarea', false);
        $response->assertSee(route('results.feedback', $user->token), false);

        $html = $response->getContent();
        $formPosition = strpos($html, 'id="feedback"');
        $asidePosition = strpos($html, 'id="permanent-link"');
        $this->assertIsInt($formPosition);
        $this->assertIsInt($asidePosition);
        $this->assertTrue($asidePosition < $formPosition);
    }

    public function test_posting_feedback_persists_one_row_and_revisiting_shows_the_thank_you_instead_of_the_form(): void
    {
        $this->seed();
        $user = $this->completedParticipant('Budi Santoso');

        $response = $this->post('/results/'.$user->token.'/feedback', [
            'message' => 'Aplikasinya sangat membantu saya memahami pasangan.',
        ]);

        $response->assertRedirect('/results/'.$user->token);
        $this->assertDatabaseCount('feedback', 1);
        $this->assertDatabaseHas('feedback', [
            'user_id' => $user->id,
            'message' => 'Aplikasinya sangat membantu saya memahami pasangan.',
        ]);

        $page = $this->get('/results/'.$user->token);
        $page->assertOk();
        $page->assertSee('Terima kasih sudah mencoba aplikasi ini dan sudah memberikan feedback!');
        $page->assertDontSee('<textarea', false);
        $page->assertDontSee('KIRIM FEEDBACK');
        $page->assertDontSee('Aplikasinya sangat membantu saya memahami pasangan.');
    }

    public function test_a_second_post_creates_no_second_row_and_redirects_back(): void
    {
        $this->seed();
        $user = $this->completedParticipant('Budi Santoso');
        Feedback::factory()->for($user)->create(['message' => 'Sudah terkirim.']);

        $response = $this->post('/results/'.$user->token.'/feedback', [
            'message' => 'Pesan kedua yang seharusnya diabaikan.',
        ]);

        $response->assertRedirect('/results/'.$user->token);
        $this->assertDatabaseCount('feedback', 1);
        $this->assertDatabaseHas('feedback', [
            'user_id' => $user->id,
            'message' => 'Sudah terkirim.',
        ]);
    }

    public function test_an_empty_message_is_rejected_with_an_indonesian_error_and_no_row_is_created(): void
    {
        $this->seed();
        $user = $this->completedParticipant('Budi Santoso');

        $response = $this->from('/results/'.$user->token)
            ->post('/results/'.$user->token.'/feedback', ['message' => '']);

        $response->assertRedirect('/results/'.$user->token);
        $response->assertSessionHasErrors(['message' => 'Pesan wajib diisi.']);
        $this->assertDatabaseCount('feedback', 0);
    }

    public function test_a_message_over_1000_characters_is_rejected_and_the_typed_message_is_preserved(): void
    {
        $this->seed();
        $user = $this->completedParticipant('Budi Santoso');
        $longMessage = str_repeat('a', 1001);

        $response = $this->from('/results/'.$user->token)
            ->followingRedirects()
            ->post('/results/'.$user->token.'/feedback', ['message' => $longMessage]);

        $response->assertOk();
        $this->assertDatabaseCount('feedback', 0);
        $response->assertSee($longMessage, false);
        $response->assertSee('Pesan tidak boleh lebih dari 1000 karakter.');
    }

    public function test_the_rate_limit_engages_after_five_submissions_per_minute(): void
    {
        $this->seed();
        $user = $this->completedParticipant('Budi Santoso');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/results/'.$user->token.'/feedback', ['message' => 'Pesan nomor '.$i])
                ->assertRedirect('/results/'.$user->token);
        }

        $this->post('/results/'.$user->token.'/feedback', ['message' => 'Pesan keenam'])
            ->assertStatus(429);

        $this->assertDatabaseCount('feedback', 1);
    }

    public function test_posting_feedback_with_an_unknown_token_returns_404(): void
    {
        $this->seed();

        $this->post('/results/not-a-real-token/feedback', ['message' => 'Halo'])->assertNotFound();

        $this->assertDatabaseCount('feedback', 0);
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
