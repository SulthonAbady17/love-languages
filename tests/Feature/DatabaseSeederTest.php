<?php

namespace Tests\Feature;

use App\Models\LoveLanguage;
use App\Models\Statement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_creates_one_love_language_per_statements_json_entry(): void
    {
        $this->seed();

        $expected = collect($this->statementsFile())
            ->mapWithKeys(fn (array $entry) => [$entry['love_language'] => $entry['description']]);

        $this->assertCount(5, LoveLanguage::query()->get());

        $expected->each(function (string $description, string $name) {
            $this->assertSame(
                $description,
                LoveLanguage::query()->where('name', $name)->value('description'),
                "Love language [{$name}] was not seeded with its statements.json description.",
            );
        });
    }

    public function test_seeding_creates_every_statement_under_its_love_language(): void
    {
        $this->seed();

        $this->assertCount(25, Statement::query()->get());

        foreach ($this->statementsFile() as $entry) {
            $loveLanguageId = LoveLanguage::query()->where('name', $entry['love_language'])->value('id');

            $this->assertNotNull($loveLanguageId, "Love language [{$entry['love_language']}] was not seeded.");

            $this->assertSame(
                $entry['statements'],
                Statement::query()
                    ->where('love_language_id', $loveLanguageId)
                    ->orderBy('id')
                    ->pluck('body')
                    ->all(),
                "Statements for [{$entry['love_language']}] were not seeded in statements.json order.",
            );
        }
    }

    public function test_seeding_twice_does_not_duplicate_rows(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(5, LoveLanguage::query()->count());
        $this->assertSame(25, Statement::query()->count());
    }

    /**
     * @return array<int, array{love_language: string, description: string, statements: array<int, string>}>
     */
    private function statementsFile(): array
    {
        return json_decode(
            file_get_contents(base_path('statements.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
