<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\LoveLanguage;
use App\Models\Result;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnswerResultModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_answer_belongs_to_a_user_and_a_statement(): void
    {
        $user = User::factory()->create();
        $answer = Answer::factory()->for($user)->create();

        $this->assertTrue($answer->user->is($user));
        $this->assertNotNull($answer->statement);
        $this->assertGreaterThanOrEqual(1, $answer->value);
        $this->assertLessThanOrEqual(5, $answer->value);
    }

    public function test_a_result_belongs_to_a_user_and_a_love_language(): void
    {
        $user = User::factory()->create();
        $loveLanguage = LoveLanguage::factory()->create();
        $result = Result::factory()->for($user)->for($loveLanguage)->create();

        $this->assertTrue($result->user->is($user));
        $this->assertTrue($result->loveLanguage->is($loveLanguage));
    }
}
