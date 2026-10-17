# 01: Short Result Token end-to-end

**What to build:** Completing an Assessment yields a short permanent link. The Result Token becomes a 16-character lowercase alphanumeric string generated from a cryptographically secure source through one shared helper used by both the submission path and the user factory. The submission transaction retries up to 5 times on the astronomically-unlikely unique-index collision and rethrows on the final miss. The database token column is retyped from a native UUID to a bounded string (length 32) keeping its unique index, the results route rejects anything that is not exactly `^[a-z0-9]{16}$` at the router, and the submission test's shape assertion matches the new format. The glossary's Result Token entry drops "(UUID)" and describes the 16-character lowercase alphanumeric form; the ER diagram shows the new column type. Old UUID-shaped links are not preserved (no data exists anywhere).

**Blocked by:** None (can start immediately)

**Status:** ready-for-agent

- [ ] Submitting a real assessment persists a token matching `^[a-z0-9]{16}$` and redirects to the short Result Page URL, which renders the Participant greeting normally
- [ ] The token generator exists in exactly one place, consumed by both the submission path and the user factory
- [ ] Generation uses a cryptographically secure uniform source over `a-z0-9`
- [ ] The submission creation path retries at most 5 times on unique-constraint violation and rethrows on the last attempt
- [ ] The token column is a bounded string with its unique index intact after migration (schema test passes unchanged)
- [ ] A malformed token 404s at the router without reaching a database query (existing unknown-token test passes unchanged)
- [ ] The submission test's token-shape assertion is the new format
- [ ] Glossary Result Token entry updated (vocabulary only, no implementation detail) and ER diagram token column updated
- [ ] `vendor/bin/pint --dirty --format agent` clean; full suite green via `php artisan test --compact`
