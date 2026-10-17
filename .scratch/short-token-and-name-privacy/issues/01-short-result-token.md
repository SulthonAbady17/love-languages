# 01: Short Result Token end-to-end

**What to build:** Completing an Assessment yields a short permanent link. The Result Token becomes a 16-character lowercase alphanumeric string generated from a cryptographically secure source through one shared helper used by both the submission path and the user factory. The submission transaction retries up to 5 times on the astronomically-unlikely unique-index collision and rethrows on the final miss. The database token column is retyped from a native UUID to a bounded string (length 32) keeping its unique index, the results route rejects anything that is not exactly `^[a-z0-9]{16}$` at the router, and the submission test's shape assertion matches the new format. The glossary's Result Token entry drops "(UUID)" and describes the 16-character lowercase alphanumeric form; the ER diagram shows the new column type. Old UUID-shaped links are not preserved (no data exists anywhere).

**Blocked by:** None (can start immediately)

**Status:** done

- [x] Submitting a real assessment persists a token matching `^[a-z0-9]{16}$` and redirects to the short Result Page URL, which renders the Participant greeting normally
- [x] The token generator exists in exactly one place, consumed by both the submission path and the user factory
- [x] Generation uses a cryptographically secure uniform source over `a-z0-9`
- [x] The submission creation path retries at most 5 times on unique-constraint violation and rethrows on the last attempt
- [x] The token column is a bounded string with its unique index intact after migration (schema test passes unchanged)
- [x] A malformed token 404s at the router without reaching a database query (existing unknown-token test passes unchanged)
- [x] The submission test's token-shape assertion is the new format
- [x] Glossary Result Token entry updated (vocabulary only, no implementation detail) and ER diagram token column updated
- [x] `vendor/bin/pint --dirty --format agent` clean; full suite green via `php artisan test --compact`

## Comments

### 2026-10-18 — Implemented on branch `test-shorter-token` (agent, commit `d4a868a`)

- `app/Support/ResultToken.php` — the single shared helper: 16 characters from `a-z0-9`, each drawn with `random_int`. Consumed by `AssessmentController::store` (via the new `persistAssessment()`) and `UserFactory`.
- `persistAssessment()` retries the **whole transaction** (not just the insert) up to 5 times on `UniqueConstraintViolationException`, rethrowing the last miss. Retrying outside the transaction is deliberate: Postgres aborts the surrounding transaction on a failed statement, so an inner retry loop would be dead code on the production engine (`DB_CONNECTION=pgsql`).
- Migration `2026_10_17_090000_retype_users_token_to_bounded_string.php` — `token` becomes `string(32)` via `change()`. On SQLite the table rebuild recreates the existing unique index automatically (declaring `->unique()` on the blueprint makes it fail with "index already exists"); verified on **both** SQLite `:memory:` (suite) and local Postgres (`varchar(32)`, `users_token_unique` intact after `php artisan migrate`).
- `routes/web.php` — `whereUuid('token')` replaced with `where('token', '^[a-z0-9]{16}$')`; malformed tokens 404 at the router. The route regex and the test's new assertion are deliberately the same literal, per the spec.
- Retry loop and generator-in-isolation are deliberately untested (spec's Testing Decisions); the seam stays the existing HTTP feature tests.
