<?php

namespace Database\Seeders;

use App\Models\LoveLanguage;
use App\Models\Statement;
use Illuminate\Database\Seeder;

class StatementSeeder extends Seeder
{
    use ReadsStatementsFile;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->statementEntries() as $entry) {
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
}
