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

        $this->assertSame('text', Schema::getColumnType('users', 'name'));

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
        // Postgres aborts the whole transaction on a failed statement, so each
        // probe runs in a savepoint that keeps the test's transaction usable.
        $insertAnswer = function (int $value) use ($userId, $loveLanguageId): bool {
            return DB::transaction(function () use ($userId, $loveLanguageId, $value): bool {
                $statementId = $this->createStatement($loveLanguageId);

                return DB::table('answers')->insert([
                    'user_id' => $userId,
                    'statement_id' => $statementId,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
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

        $this->assertContains(Schema::getColumnType('results', 'value'), ['float', 'double', 'real', 'float8']);

        $this->assertTrue(collect(Schema::getIndexes('results'))->contains(
            fn (array $index) => $index['unique'] && $index['columns'] === ['user_id', 'love_language_id'],
        ));

        $this->assertHasForeignKey('results', 'user_id', 'users');
        $this->assertHasForeignKey('results', 'love_language_id', 'love_languages');
    }

    public function test_feedback_table_references_a_user_with_a_unique_constraint(): void
    {
        $this->assertTrue(Schema::hasTable('feedback'));
        $this->assertTrue(Schema::hasColumns('feedback', ['id', 'user_id', 'message', 'created_at', 'updated_at']));

        $this->assertSame('text', Schema::getColumnType('feedback', 'message'));

        $this->assertTrue(collect(Schema::getIndexes('feedback'))->contains(
            fn (array $index) => $index['unique'] && $index['columns'] === ['user_id'],
        ));

        $this->assertHasForeignKey('feedback', 'user_id', 'users');
    }

    public function test_a_second_feedback_for_the_same_user_is_rejected_by_the_database(): void
    {
        $userId = User::factory()->create()->id;

        $insertFeedback = function () use ($userId): bool {
            // Postgres aborts the transaction on a failed statement, so the
            // probe runs in a savepoint that keeps the test's transaction usable.
            return DB::transaction(function () use ($userId): bool {
                return DB::table('feedback')->insert([
                    'user_id' => $userId,
                    'message' => 'Terima kasih atas hasil asesmen saya.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        };

        $this->assertTrue($insertFeedback());

        try {
            $insertFeedback();
            $this->fail('A second Feedback for the same Participant was accepted; expected the unique constraint to reject it.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('user_id', $exception->getMessage());
            $this->assertMatchesRegularExpression('/UNIQUE constraint failed.*feedback|duplicate key value/', $exception->getMessage());
        }
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
