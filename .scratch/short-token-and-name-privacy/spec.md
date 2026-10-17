Status: ready-for-agent

# Spec: Short Result Token and Name Privacy

## Problem Statement

A Participant who completes the Assessment receives a permanent Result Page link, but the Result Token in that link is a 36-character UUID — long, clunky, and awkward to share by hand or read aloud. The Participant wants a short, clean link they can paste into a chat or read to a friend without a wall of hyphenated hex.

Separately, the Participant types only a self-reported name and has no account, yet the application stores that name in plain text. The operator/developer wants to look at the database — a dump, the hosting dashboard, a backup — and be unable to read Participants' names. Their stated goal: "I, as a developer, cannot know their data."

## Solution

The Result Token becomes a 16-character lowercase alphanumeric string, making the permanent link short and clean while remaining astronomically unguessable. The Participant's experience is unchanged: submit the Assessment, land on the Result Page, copy the permanent link — it is simply shorter now.

The Participant's name is encrypted at rest: the database stores ciphertext, session payloads are encrypted at rest, and the Result Page still greets the Participant by name exactly as before. Anyone reading the database alone sees only opaque blobs; recovering the name requires the application's key.

## User Stories

1. As a Participant, I want my Result Token to be a short 16-character string, so that my permanent link is easy to share, read aloud, and recognise as a link rather than a machine identifier.
2. As a Participant, I want my Result Page URL to stay in the form `/results/{token}`, so that nothing about how I retrieve my results changes.
3. As a Participant, I want the characters in my token to be plain lowercase letters and digits, so that the URL needs no escaping and survives messaging apps that mangle long or punctuated strings.
4. As a Participant, I want my token to be practically unguessable, so that no stranger can enumerate links and read my results.
5. As a Participant, I want my Result Page to keep greeting me by name, so that the privacy change is invisible to me.
6. As a Participant, I want my name shown on the Result Page exactly as I typed it, so that encryption never mangles or truncates my display name.
7. As a Participant with a long or non-Latin name, I want it stored safely without being cut off by a column length limit, so that my greeting never breaks.
8. As a Participant in mid-assessment, I want the name I typed in the welcome step protected in session storage too, so that a database-side reader cannot pick my name out of session rows either.
9. As a Participant, I want my Likert Responses, Results, and Advice stored as they are today, so that only data that actually identifies me receives privacy treatment.
10. As a Participant, I want my name never sent to the AI provider, so that the minimum necessary data leaves the application.
11. As a Returning Participant, I want my short link to keep working for as long as the Result Page exists, so that the promise of a permanent link holds.
12. As an operator, I want existing rows untouched — there is no data to migrate in local or production — so that the schema change is a simple column alteration, not a data conversion project.
13. As an operator, I want the token column to enforce uniqueness in the database, so that collisions are impossible to introduce silently.
14. As an operator, I want session payload encryption switched on via one environment key, so that the whole session store is covered without code changes.
15. As a developer, I want one shared token-generator used by both the submission path and the test factory, so that the token definition exists in exactly one place.
16. As a developer, I want the generator to draw from a cryptographically secure source, so that shortness does not mean predictability.
17. As a developer, I want the submission transaction to retry a few times on the astronomically-unlikely unique-index collision, so that a collision is a transient blip instead of a 500 page the Participant sees.
18. As a developer, I want the results route to reject anything that is not exactly 16 lowercase alphanumeric characters at the router, so that junk requests never reach a database query.
19. As a developer, I want the name encrypted through a standard framework cast rather than hand-rolled crypto, so that the mechanism is auditable, maintained, and works transparently on read.
20. As a developer, I want `$user->name` to still return the plaintext name inside the application, so that the Result Page, tests, and any future display code need no decryption logic.
21. As a developer, I want the database to hold only ciphertext for the name, so that raw queries, dumps, and the hosting dashboard reveal nothing.
22. As a developer, I want a test that reads the raw database row and asserts the plaintext name is absent, so that the privacy guarantee is enforced by the suite, not by convention.
23. As a developer, I want the token-shape assertion updated to the new format, so that a regression to UUIDs fails loudly.
24. As a developer, I want malformed-token requests to still return 404 through the existing unknown-token test, so that the route-constraint change is verified at the same seam.
25. As a maintainer, I want the glossary's Result Token definition to describe the new format without implementation detail, so that the documented vocabulary matches the code.
26. As a maintainer, I want the ER diagram to show the new column types, so that schema documentation does not lie.
27. As a maintainer, I want no ADR for either decision, so that the docs folder keeps carrying only decisions that are hard to reverse and genuinely surprising.

## Implementation Decisions

- **Token format**: 16 characters drawn uniformly from `a–z0–9` (base36, ~83 bits of entropy), generated with `random_int` (cryptographically secure, uniform per character). Chosen over 12 chars (less margin) and 22-char base64url (barely shorter than a UUID).
- **Generator location**: one new shared static helper (`generate(): string`) consumed by the assessment submission path and the user factory. The generator does not handle collisions.
- **Collision handling**: the submission transaction wraps user creation in a retry loop of at most 5 attempts, catching the framework's unique-constraint-violation exception and rethrowing on the final miss. Collision probability is ~2⁻⁸³ per attempt; the loop is insurance, not a hot path.
- **Route constraint**: the results route drops its UUID-only constraint in favour of an exact `^[a-z0-9]{16}$` character-class constraint. Malformed tokens 404 at the router without a database query. Old UUID-shaped links (if any data ever existed) would 404 at the router — accepted, since local and production databases hold no rows.
- **Schema — token**: the `users.token` column changes from a native `uuid` type to a bounded string (length 32; the value needs 16). The existing unique index must survive the change. One new migration performs this alteration.
- **Schema — name**: the `users.name` column widens from string to text **in the same migration**. Required because the encrypted payload is base64 of IV + ciphertext + MAC and a 100-character multi-byte name validated by the existing `max:100` rule could otherwise overflow a 255-byte varchar on Postgres.
- **Name privacy**: an Eloquent `encrypted` cast on the name attribute (Laravel's convention: a `casts()` method on the model; no attribute-style cast exists for this). Reads and writes are transparent to all display code; the database only ever sees ciphertext. Deterministic-by-key, opaque IV per write — irrelevant, since nothing queries by name (verified: no name predicates against the users table).
- **Session privacy**: `SESSION_ENCRYPT` flips from `false` to `true` in both the local environment file and the environment example file. The name typed during the welcome step sits in the database session store until the session expires; this closes that plaintext hole with one key.
- **Scope of encryption**: only the Participant's name. Likert Responses (1–5 integers), Results (averages), and Advice (rankings prose that contains no name) carry no PII and are left untouched.
- **No re-tokenization / no data migration**: no rows exist anywhere; the migration alters types only and never rewrites values.
- **Glossary**: the Result Token entry drops "(UUID)" and describes a 16-character lowercase alphanumeric string — stated as vocabulary, not implementation. The glossary stays free of casts, encryptions, and generator mechanics; those live in code.
- **ER diagram**: token column type updated to the bounded string, name column type to text.
- **No ADR**: both decisions fail the ADR test — trivially reversible (one cast, one column, one generator, one env key) and not surprising without context.
- **Domain vocabulary**: existing glossary terms (Participant, Assessment, Result Token, Result Page, Advice) used unchanged.

## Testing Decisions

- **What makes a good test**: assert external behaviour only — the HTTP response, what is persisted (including raw, un-modelled database state), and never the internals of the generator or the cast. No test may instantiate or call the helper directly; its observable effect is the shape of the token that lands in the database after a real submission.
- **Single seam**: the existing HTTP feature-test layer. No new test files, no new seams — every new behaviour is verified inside the three feature test files that already own submission, results, and schema behaviour.
- **Scenarios covered at that seam**:
  1. Submission persists a token matching `^[a-z0-9]{16}$` and redirects to the short result URL (updates the existing UUID-shape assertion).
  2. After a real POST, the raw users row's name value does not contain the plaintext submitted name (reads persisted state the same way the existing tests read `assertDatabaseCount`).
  3. The short token resolves the Result Page with the participant greeting intact (existing happy-path tests keep passing unchanged — the cast is transparent to reads).
  4. A malformed token returns 404 through the unchanged unknown-token test (verifies the route-constraint swap at the same seam).
  5. Schema assertions still find the token column, its unique index, and the name column after the migration (existing schema test is the safety net for `change()` preserving the index).
- **Deliberately untested**: the collision retry loop (triggering it honestly would require poisoning the unique index) and the generator in isolation (covered behaviourally by scenario 1).
- **Prior art**: the submission feature test (persisted-state assertions after a POST, `RefreshDatabase`, seeded statements), the results feature test (HTML assertions and the unknown-token 404), and the schema feature test (column/index introspection). Follow their naming and structure.
- Tests run offline: `php artisan test --compact`, SQLite `:memory:`; formatting via the project's Pint run on changed files.

## Out of Scope

- Rate limiting or attempt caps on the results endpoint (the token is a capability by design; enumeration is already infeasible)
- Hashing the name (rejected: it would make the Result Page greeting impossible)
- Encrypting or otherwise changing Likert Responses, Results, or Advice (no PII)
- Re-tokenizing existing rows or keeping old UUID links working (no data exists)
- Changing the name's transient plaintext journey during a sitting (typed → session → rendered HTML — unavoidable while the name is displayed)
- Collapsing or shortening the session lifetime
- Any UI change; the Result Page and forms are visually untouched beyond the URL being shorter
- Rotating or externally managing the encryption key (uses the standard application key)
- Admin UI, dashboards, or analytics over Participants
- ADRs for either decision (both cheap to reverse)

## Further Notes

- The single risk worth watching: `change()` on a column that carries a unique index occasionally needs the index re-asserted on some engines. The existing schema test fails immediately if the index is lost, so the suite is the detector — run it right after the migration.
- Encryption is keyed by the application key. It protects against database-only access (dumps, hosting dashboards, backups) — it does not and cannot protect against a reader who also holds the environment file. That limit was chosen consciously: the alternative (hash) breaks the product.
- `SESSION_ENCRYPT=true` means every session payload is encrypted at rest, not just the name — a strictly broader privacy win at zero code cost.
- The old `whereUuid` constraint and the test's old UUID regex disagreed in strictness; the new `^[a-z0-9]{16}$` constraint and the new test regex are deliberately identical, so router and suite can no longer drift apart.
- Both environment files (local and example) carry the session-encrypt key today with the value `false`; both must flip, or a fresh clone silently regresses.
