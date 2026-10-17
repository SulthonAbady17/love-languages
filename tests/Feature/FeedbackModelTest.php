<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_participant_has_one_feedback(): void
    {
        $user = User::factory()->create();
        $feedback = Feedback::factory()->for($user)->create();

        $this->assertTrue($user->feedback->is($feedback));
        $this->assertTrue($feedback->user->is($user));
    }
}
