<?php

namespace Database\Seeders;

use App\Models\LoveLanguage;
use Illuminate\Database\Seeder;

class LoveLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->statementsFile() as $entry) {
            LoveLanguage::query()->firstOrCreate(
                ['name' => $entry['love_language']],
                ['description' => $entry['description']],
            );
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
