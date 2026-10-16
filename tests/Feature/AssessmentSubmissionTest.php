<?php

namespace Tests\Feature;

use App\Ai\Agents\AdviceAgent;
use App\Models\Answer;
use App\Models\LoveLanguage;
use App\Models\Result;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Prompts\AgentPrompt;
use RuntimeException;
use Tests\TestCase;

class AssessmentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        AdviceAgent::fake();
    }

    /**
     * One answer set per seeded Love Language (ordered by id), with the
     * hand-computed average each must produce in `results.value`.
     *
     * @var array<int, array{values: array<int, int>, expected: float}>
     */
    private const VALUE_SETS = [
        ['values' => [1, 1, 1, 1, 1], 'expected' => 1.0],
        ['values' => [2, 2, 3, 2, 2], 'expected' => 2.2],
        ['values' => [1, 2, 3, 4, 5], 'expected' => 3.0],
        ['values' => [3, 4, 5, 5, 5], 'expected' => 4.4],
        ['values' => [4, 5, 4, 5, 5], 'expected' => 4.6],
    ];

    public function test_valid_submission_creates_user_answers_and_results_then_redirects_to_result_page(): void
    {
        $this->seed();

        $response = $this->withSession(['name' => 'Budi Santoso'])
            ->post('/assessment', ['answers' => $this->knownAnswers()]);

        $user = User::sole();
        $this->assertSame('Budi Santoso', $user->name);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $user->token,
        );
        $response->assertRedirect('/results/'.$user->token);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('answers', 25);
        $this->assertDatabaseCount('results', 5);

        foreach ($this->knownAnswers() as $statementId => $value) {
            $this->assertDatabaseHas('answers', [
                'user_id' => $user->id,
                'statement_id' => $statementId,
                'value' => $value,
            ]);
        }

        $this->assertExpectedResultAverages($user);
    }

    public function test_successful_submission_generates_advice_for_the_top_love_language_before_redirecting(): void
    {
        $this->seed();
        AdviceAgent::fake(['Saran cinta untukmu hari ini.']);

        $response = $this->withSession(['name' => 'Budi Santoso'])
            ->post('/assessment', ['answers' => $this->knownAnswers()]);

        $user = User::sole();
        $response->assertRedirect('/results/'.$user->token);
        $this->assertSame('Saran cinta untukmu hari ini.', $user->advice);

        // knownAnswers gives the last-seeded language VALUE_SETS[4], the
        // highest-scoring set, so it tops the ranking.
        $topName = LoveLanguage::query()->orderBy('id')->pluck('name')->last();
        AdviceAgent::assertPrompted(fn (AgentPrompt $prompt) => $prompt->contains('Top Love Language: '.$topName));
        AdviceAgent::assertNotPrompted(fn (AgentPrompt $prompt) => $prompt->contains('Budi Santoso'));

        $this->get('/results/'.$user->token)
            ->assertOk()
            ->assertSee('id="advice"', false)
            ->assertSee('Saran cinta untukmu hari ini.');
    }

    public function test_submission_still_succeeds_and_persists_answers_when_advice_generation_fails(): void
    {
        $this->seed();
        AdviceAgent::fake(fn () => throw new RuntimeException('Groq unavailable.'));

        $response = $this->withSession(['name' => 'Budi Santoso'])
            ->post('/assessment', ['answers' => $this->knownAnswers()]);

        $user = User::sole();
        $response->assertRedirect('/results/'.$user->token);
        $this->assertNull($user->advice);

        $this->assertDatabaseCount('answers', 25);
        $this->assertDatabaseCount('results', 5);
        $this->assertExpectedResultAverages($user);

        $this->get('/results/'.$user->token)
            ->assertOk()
            ->assertDontSee('id="advice"', false);
    }

    public function test_five_equal_scores_prompt_the_love_language_the_result_page_highlights(): void
    {
        $this->seed();

        $answers = [];
        foreach (Statement::pluck('id') as $statementId) {
            $answers[$statementId] = 3;
        }

        $this->withSession(['name' => 'Budi Santoso'])
            ->post('/assessment', ['answers' => $answers]);

        $user = User::sole();
        $resultValues = Result::where('user_id', $user->id)->pluck('value');
        $this->assertCount(1, $resultValues->unique(), 'Expected all five scores to be tied.');

        $page = $this->get('/results/'.$user->token)->assertOk();
        $matched = preg_match('/<section id="top-love-language".*?<h2[^>]*>\s*(.*?)\s*<\/h2>/s', $page->getContent(), $matches);
        $this->assertSame(1, $matched, 'Expected the Result Page to highlight a top Love Language.');
        $highlighted = trim($matches[1]);

        AdviceAgent::assertPrompted(fn (AgentPrompt $prompt) => $prompt->contains('Top Love Language: '.$highlighted));
    }

    public function test_submission_with_a_missing_answer_is_rejected_and_creates_no_rows(): void
    {
        $this->seed();

        $answers = $this->knownAnswers();
        unset($answers[array_key_first($answers)]);

        $response = $this->withSession(['name' => 'Budi Santoso'])
            ->from('/assessment')
            ->post('/assessment', ['answers' => $answers]);

        $response->assertRedirect('/assessment');
        $response->assertSessionHasErrors('answers');
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('answers', 0);
        $this->assertDatabaseCount('results', 0);
    }

    public function test_submission_with_an_out_of_range_answer_is_rejected_and_creates_no_rows(): void
    {
        $this->seed();

        $answers = $this->knownAnswers();
        $statementId = array_key_first($answers);
        $answers[$statementId] = 6;

        $response = $this->withSession(['name' => 'Budi Santoso'])
            ->from('/assessment')
            ->post('/assessment', ['answers' => $answers]);

        $response->assertRedirect('/assessment');
        $response->assertSessionHasErrors('answers.'.$statementId);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('answers', 0);
        $this->assertDatabaseCount('results', 0);
    }

    public function test_submission_with_a_non_integer_answer_is_rejected_and_creates_no_rows(): void
    {
        $this->seed();

        $answers = $this->knownAnswers();
        $statementId = array_key_first($answers);
        $answers[$statementId] = 'bukan-angka';

        $response = $this->withSession(['name' => 'Budi Santoso'])
            ->from('/assessment')
            ->post('/assessment', ['answers' => $answers]);

        $response->assertRedirect('/assessment');
        $response->assertSessionHasErrors('answers.'.$statementId);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('answers', 0);
        $this->assertDatabaseCount('results', 0);
    }

    public function test_submission_without_a_name_in_session_redirects_home_and_creates_no_rows(): void
    {
        $this->seed();

        $response = $this->post('/assessment', ['answers' => $this->knownAnswers()]);

        $response->assertRedirect('/');
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('answers', 0);
        $this->assertDatabaseCount('results', 0);
    }

    /**
     * The 25 answers keyed by statement id: each Love Language (ordered by id)
     * receives its fixed value set.
     *
     * @return array<int, int>
     */
    private function knownAnswers(): array
    {
        $answers = [];
        $languages = LoveLanguage::with(['statements' => fn ($query) => $query->orderBy('id')])
            ->orderBy('id')
            ->get();

        foreach ($languages as $index => $loveLanguage) {
            foreach ($loveLanguage->statements as $position => $statement) {
                $answers[$statement->id] = self::VALUE_SETS[$index]['values'][$position];
            }
        }

        return $answers;
    }

    private function assertExpectedResultAverages(User $user): void
    {
        $results = Result::where('user_id', $user->id)->get();

        $this->assertCount(5, $results);

        $loveLanguageIds = LoveLanguage::query()->orderBy('id')->pluck('id');

        foreach ($results as $result) {
            $position = $loveLanguageIds->search($result->love_language_id, strict: true);
            $this->assertNotFalse($position, 'Result for unexpected love language id '.$result->love_language_id.'.');

            $expected = self::VALUE_SETS[$position]['expected'];
            $this->assertEqualsWithDelta(
                $expected,
                $result->value,
                0.0001,
                'Result for love_language_id '.$result->love_language_id.' should average to '.$expected,
            );
            $this->assertSame($user->id, $result->user_id);
        }

        $this->assertCount(5, $results->pluck('value')->unique());
    }

    public function test_every_answer_row_is_linked_to_the_new_user(): void
    {
        $this->seed();

        $this->withSession(['name' => 'Budi Santoso'])
            ->post('/assessment', ['answers' => $this->knownAnswers()]);

        $user = User::sole();
        $statementIds = Statement::orderBy('id')->pluck('id');

        $this->assertEqualsCanonicalizing(
            $statementIds->all(),
            Answer::where('user_id', $user->id)->pluck('statement_id')->all(),
        );
    }
}
