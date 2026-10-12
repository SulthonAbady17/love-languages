<?php

namespace Database\Seeders;

trait ReadsStatementsFile
{
    /**
     * Decode statements.json, the source of truth for love languages and statements.
     *
     * @return array<int, array{love_language: string, description: string, statements: array<int, string>}>
     */
    protected function statementEntries(): array
    {
        return json_decode(
            file_get_contents(base_path('statements.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
