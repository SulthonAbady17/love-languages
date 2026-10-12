<?php

namespace Database\Seeders;

use App\Models\LoveLanguage;
use Illuminate\Database\Seeder;

class LoveLanguageSeeder extends Seeder
{
    use ReadsStatementsFile;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->statementEntries() as $entry) {
            LoveLanguage::query()->firstOrCreate(
                ['name' => $entry['love_language']],
                ['description' => $entry['description']],
            );
        }
    }
}
