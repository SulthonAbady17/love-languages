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
        $loveLanguage = LoveLanguage::query()->create([
            'name' => 'Quality Time',
            'description' => 'Waktu berkualitas.',
        ]);

        Statement::query()->create([
            'love_language_id' => $loveLanguage->id,
            'body' => 'Saya senang menghabiskan waktu berdua.',
        ]);

        $this->assertCount(1, $loveLanguage->statements);
        $this->assertSame('Saya senang menghabiskan waktu berdua.', $loveLanguage->statements->first()->body);
    }

    public function test_a_statement_belongs_to_a_love_language(): void
    {
        $loveLanguage = LoveLanguage::query()->create([
            'name' => 'Physical Touch',
            'description' => 'Sentuhan fisik.',
        ]);

        $statement = Statement::query()->create([
            'love_language_id' => $loveLanguage->id,
            'body' => 'Saya senang saat pasangan memegang tangan saya.',
        ]);

        $this->assertTrue($statement->loveLanguage->is($loveLanguage));
    }
}
