# 01: Pre-ship verification on Postgres

**What to build:** The owner can trust that the app is engine-safe and the deployed checkout is self-sufficient before anything ships: the full PHPUnit suite passes against a Postgres connection, and every runtime file the deploy needs is tracked in git.

**Blocked by:** None (can start immediately)

**Status:** ready-for-agent

- [x] Existing PHPUnit feature suite (assessment flow, submission scoring, result page, statements seeder, schema expectations) runs green against a Postgres connection — a Neon database or local Postgres — with zero new test files on the happy path
- [x] The statements file at the repo root and the advice skill directory are both verified tracked in git
- [x] `APP_KEY` and `GROQ_API_KEY` verified untracked and absent from git history
- [x] Composer PHP floor verified from the lockfile platform check — declared `platform.php` is `^8.3`, but the installed lock requires **PHP >= 8.4.1** (see comment below)

## Comments

### 2026-10-16 — verification run (agent)

**Suite on Postgres:** local `postgres` preset installed (image `postgis/postgis:16-3.5-alpine`), database `love_languages_pg_test` created; the project `.env` / `.lerd.yaml` were left on MySQL (nothing re-pointed, dev setup untouched). Full suite run with `DB_CONNECTION=pgsql`: **44 passed, 0 failed**. Same suite still green on the default sqlite connection: **44 passed**. Zero new test files.

The first Postgres run was **13 failed / 31 passed**. Four engine-specific defects, all fixed at the same public HTTP seam:

1. **Seeded ids drift (10 failures).** `RefreshDatabase` re-migrates per test only on the sqlite `:memory:` database; on Postgres each test rolls back but sequences keep advancing, so the seeded Love Languages no longer have ids 1–5. Tests that hardcoded ids were changed to derive them from the seeded order (`AssessmentSubmissionTest`, `ResultPageTest`).
2. **Malformed Result Token returned 500 (1 failure).** `users.token` is a `uuid` column, so Postgres rejects `'not-a-real-token'` with `SQLSTATE[22P02]` before the controller can 404 — a public-site defect, not just a test artifact. Fixed in `routes/web.php` with `->whereUuid('token')`; the existing `test_unknown_token_returns_404` now passes on Postgres.
3. **Expected check-constraint failure poisoned the test transaction (1 failure).** Postgres aborts the whole transaction on a failed statement, so the second invalid insert saw `current transaction is aborted` instead of `answers_value_check`. Each probe now runs in a savepoint (`DatabaseSchemaTest`).
4. **Type-name assertion (1 failure).** Postgres reports `float8` for the `results.value` column; added to the allowed list.

**Runtime files tracked:** `statements.json` and all seven files under `.agents/skills/stop-slop/` (the Advice agent's skill directory) are tracked, with nothing gitignored or untracked in either path — a fresh clone can seed and advise.

**Secrets:** `.env` has never been committed (0 commits touch it, gitignored). A scan of every commit in history for the live `APP_KEY` value and the `gsk_…` Groq key returns no hits; only the `GROQ_API_KEY` env *name* appears (in `config/ai.php` and the `.env.example` placeholder).

**Composer floor — finding for tickets 04/07:** `composer.lock` records `platform.php: ^8.3` (matching `composer.json`), but the generated `vendor/composer/platform_check.php` requires `PHP_VERSION_ID >= 80401`, i.e. **PHP >= 8.4.1**, because the locked Symfony components declare `>=8.4.1`. The deploy target must provide 8.4.1+, not 8.3: the pre-approved `php:8.4-apache` fallback image satisfies this, and Render's native PHP version must be checked against 8.4.1 rather than 8.3.
