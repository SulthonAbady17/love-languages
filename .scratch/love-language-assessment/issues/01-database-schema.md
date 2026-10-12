# 01: Database schema

**What to build:** All database migrations for the application. Drop the default Laravel auth columns that are not needed. By the end of this ticket `php artisan migrate` runs cleanly and produces the correct tables.

Tables to create:
- `love_languages`: id, name (varchar), description (text), timestamps
- `users`: id, name (varchar), token (uuid, unique), timestamps — no email, no password, no remember_token
- `statements`: id, love_language_id (FK → love_languages), body (text), timestamps
- `answers`: id, user_id (FK → users), statement_id (FK → statements), value (tinyint 1–5), timestamps
- `results`: id, user_id (FK → users), love_language_id (FK → love_languages), value (float — average Likert score), timestamps

The default Laravel users migration must be replaced (not extended) so it does not include auth columns. Cache and jobs tables from the default scaffold can stay as-is.

**Blocked by:** None (can start immediately)

**Status:** ready-for-agent

- [x] Migration for `love_languages` table exists and is correct
- [x] Migration for `users` table replaces the default (no email, no password, no remember_token; includes `token` uuid unique)
- [x] Migration for `statements` table exists with FK to `love_languages`
- [x] Migration for `answers` table exists with FKs to `users` and `statements`; `value` is constrained to 1–5
- [x] Migration for `results` table exists with FKs to `users` and `love_languages`; `value` is float
- [x] `php artisan migrate:fresh` runs without error

## Comments

- Implemented on branch `test-opencode` with tests in `tests/Feature/DatabaseSchemaTest.php` (schema introspection + behavioural check that `answers.value` rejects 0 and 6 and accepts 1 and 5).
- The 1–5 range is a database CHECK constraint (`answers_value_check`) added with raw SQL, since Laravel's schema builder has no check-constraint API. Verified working on both SQLite (tests) and MySQL 8 (dev).
- `sessions` table kept (SESSION_DRIVER=database); `password_reset_tokens` dropped (no email auth).
- Consistency follow-through: `UserFactory` now emits `name` + `token`, `User` model no longer references email/password, `DatabaseSeeder` no longer seeds a scaffold user.
- `ERD.dbml` updated to match the implemented schema (it still used the pre-glossary `categories` naming and lacked `users.token`); `GLOSSARY.md` and `README.md` already agreed.
- Follow-up: `results` has a composite unique index on (`user_id`, `love_language_id`) so a user gets exactly one score-summary row per Love Language.
