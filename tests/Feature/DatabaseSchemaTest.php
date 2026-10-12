<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_love_languages_table_has_the_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('love_languages'));
        $this->assertTrue(Schema::hasColumns('love_languages', ['id', 'name', 'description', 'created_at', 'updated_at']));

        $this->assertSame('varchar', Schema::getColumnType('love_languages', 'name'));
        $this->assertSame('text', Schema::getColumnType('love_languages', 'description'));
    }

    public function test_users_table_replaces_the_default_auth_columns_with_a_result_token(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumns('users', ['id', 'name', 'token', 'created_at', 'updated_at']));

        $this->assertFalse(Schema::hasColumn('users', 'email'));
        $this->assertFalse(Schema::hasColumn('users', 'password'));
        $this->assertFalse(Schema::hasColumn('users', 'remember_token'));

        $this->assertTrue(collect(Schema::getIndexes('users'))->contains(
            fn (array $index) => $index['unique'] && $index['columns'] === ['token'],
        ));
    }

    public function test_statements_table_references_a_love_language(): void
    {
        $this->assertTrue(Schema::hasTable('statements'));
        $this->assertTrue(Schema::hasColumns('statements', ['id', 'love_language_id', 'body', 'created_at', 'updated_at']));

        $this->assertSame('text', Schema::getColumnType('statements', 'body'));

        $this->assertHasForeignKey('statements', 'love_language_id', 'love_languages');
    }

    public function test_answers_table_references_a_user_and_a_statement_with_a_likert_value(): void
    {
        $this->assertTrue(Schema::hasTable('answers'));
        $this->assertTrue(Schema::hasColumns('answers', ['id', 'user_id', 'statement_id', 'value', 'created_at', 'updated_at']));

        $this->assertHasForeignKey('answers', 'user_id', 'users');
        $this->assertHasForeignKey('answers', 'statement_id', 'statements');

        $this->assertTrue(collect(Schema::getIndexes('answers'))->contains(
            fn (array $index) => $index['unique'] && $index['columns'] === ['user_id', 'statement_id'],
        ));
    }

    public function test_answers_value_is_constrained_to_the_likert_range(): void
    {
        [$userId, $loveLanguageId] = $this->seedUserAndLoveLanguage();
        $insertAnswer = function (int $value) use ($userId, $loveLanguageId) {
            $statementId = $this->createStatement($loveLanguageId);

            return DB::table('answers')->insert([
                'user_id' => $userId,
                'statement_id' => $statementId,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };

        foreach ([1, 5] as $value) {
            $insertAnswer($value);
        }

        foreach ([0, 6] as $value) {
            try {
                $insertAnswer($value);
                $this->fail("Answer value {$value} was accepted; expected the 1-5 constraint to reject it.");
            } catch (QueryException $exception) {
                $this->assertStringContainsString('answers_value_check', $exception->getMessage());
            }
        }
    }

    public function test_results_table_references_a_user_and_a_love_language_with_a_float_average(): void
    {
        $this->assertTrue(Schema::hasTable('results'));
        $this->assertTrue(Schema::hasColumns('results', ['id', 'user_id', 'love_language_id', 'value', 'created_at', 'updated_at']));

        $this->assertContains(Schema::getColumnType('results', 'value'), ['float', 'double', 'real']);

        $this->assertTrue(collect(Schema::getIndexes('results'))->contains(
            fn (array $index) => $index['unique'] && $index['columns'] === ['user_id', 'love_language_id'],
        ));

        $this->assertHasForeignKey('results', 'user_id', 'users');
        $this->assertHasForeignKey('results', 'love_language_id', 'love_languages');
    }

    private function assertHasForeignKey(string $table, string $column, string $foreignTable): void
    {
        $this->assertTrue(
            collect(Schema::getForeignKeys($table))->contains(
                fn (array $foreignKey) => $foreignKey['columns'] === [$column]
                    && $foreignKey['foreign_table'] === $foreignTable,
            ),
            "Failed asserting that [{$table}.{$column}] is a foreign key referencing [{$foreignTable}].",
        );
    }

    /**
     * @return array{0: int, 1: int} The seeded user and love language ids.
     */
    private function seedUserAndLoveLanguage(): array
    {
        $userId = User::factory()->create()->id;

        $loveLanguageId = DB::table('love_languages')->insertGetId([
            'name' => 'Kata-kata Penegasan',
            'description' => 'Kata-kata penegasan.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$userId, $loveLanguageId];
    }

    private function createStatement(int $loveLanguageId): int
    {
        return DB::table('statements')->insertGetId([
            'love_language_id' => $loveLanguageId,
            'body' => 'Saya senang menerima pujian.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
