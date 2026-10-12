<?php

namespace Tests\Feature;

use App\Models\LoveLanguage;
use App\Models\Statement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoveLanguageModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_love_language_has_many_statements(): void
    {
        $loveLanguage = LoveLanguage::factory()->create();
        $statement = Statement::factory()->for($loveLanguage)->create();

        $this->assertCount(1, $loveLanguage->statements);
        $this->assertTrue($loveLanguage->statements->first()->is($statement));
    }

    public function test_a_statement_belongs_to_a_love_language(): void
    {
        $loveLanguage = LoveLanguage::factory()->create();
        $statement = Statement::factory()->for($loveLanguage)->create();

        $this->assertTrue($statement->loveLanguage->is($loveLanguage));
    }
}
