# 04: Seeder wiring

**What to build:** `DatabaseSeeder` reads `statements.json` and populates the `love_languages` and `statements` tables. By the end of this ticket `php artisan db:seed` runs cleanly and the database contains 5 Love Language rows and 25 Statement rows.

Specifics:
- `LoveLanguageSeeder` reads `statements.json`, creates one `LoveLanguage` record per entry (name + description)
- `StatementSeeder` creates one `Statement` record per statement body, linked to the correct `LoveLanguage` by name
- `DatabaseSeeder` calls both in the correct order
- Seeding is idempotent: running it twice does not duplicate rows (use `firstOrCreate` or truncate + re-seed)
- Eloquent models `LoveLanguage` and `Statement` are created with correct relationships (`LoveLanguage hasMany Statement`, `Statement belongsTo LoveLanguage`)

**Blocked by:** 01 (schema must exist), 03 (statements.json must be reviewed and approved by author)

**Status:** completed

- [x] `LoveLanguage` Eloquent model exists with `hasMany(Statement::class)` relationship
- [x] `Statement` Eloquent model exists with `belongsTo(LoveLanguage::class)` relationship
- [x] `php artisan db:seed` runs without error
- [x] Database contains exactly 5 `love_languages` rows after seeding
- [x] Database contains exactly 25 `statements` rows after seeding
- [x] Each statement's `love_language_id` points to the correct Love Language
- [x] Running the seeder twice does not produce duplicate rows
