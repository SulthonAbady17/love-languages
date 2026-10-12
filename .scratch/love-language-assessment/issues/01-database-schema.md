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

- [ ] Migration for `love_languages` table exists and is correct
- [ ] Migration for `users` table replaces the default (no email, no password, no remember_token; includes `token` uuid unique)
- [ ] Migration for `statements` table exists with FK to `love_languages`
- [ ] Migration for `answers` table exists with FKs to `users` and `statements`; `value` is constrained to 1–5
- [ ] Migration for `results` table exists with FKs to `users` and `love_languages`; `value` is float
- [ ] `php artisan migrate:fresh` runs without error
