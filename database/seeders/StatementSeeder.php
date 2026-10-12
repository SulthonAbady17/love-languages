<?php

namespace Database\Seeders;

use App\Models\LoveLanguage;
use App\Models\Statement;
use Illuminate\Database\Seeder;

class StatementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->statementsFile() as $entry) {
            $loveLanguage = LoveLanguage::query()
                ->where('name', $entry['love_language'])
                ->firstOrFail();

            foreach ($entry['statements'] as $body) {
                Statement::query()->firstOrCreate([
                    'love_language_id' => $loveLanguage->id,
                    'body' => $body,
                ]);
            }
        }
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
