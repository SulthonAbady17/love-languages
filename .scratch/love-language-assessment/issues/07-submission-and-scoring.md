# 07: Assessment submission & score calculation

**What to build:** The POST handler that receives the completed assessment, persists everything, computes scores, and redirects the Participant to their permanent Result Page. This is the core domain logic ticket.

Specifics:
- `POST /assessment` validates that all 25 statement IDs are present and each value is an integer between 1 and 5; rejects and redirects back on failure
- Creates one `User` row: name from session, token = UUID v4 (unique)
- Creates 25 `Answer` rows: one per statement, linked to the new User
- Computes 5 `Result` rows: for each Love Language, `value = average of Answer values for that Love Language's statements` (sum ÷ 5)
- Stores the 5 Result rows linked to the User and respective Love Language
- Redirects to `/results/{token}` after successful save
- If name is missing from session, redirects to `GET /`
- Feature test: submits a known set of 25 answers, asserts correct User/Answer/Result rows in the database, asserts redirect to the correct token URL, asserts score values are arithmetically correct

**Blocked by:** 06 (assessment form must exist to POST from)

**Status:** completed

- [x] `POST /assessment` with valid answers creates 1 User, 25 Answer, and 5 Result rows
- [x] The User row has a non-null UUID token
- [x] Each Result row's `value` is the correct average for that Love Language (verified with a known input)
- [x] Successful submission redirects to `/results/{token}`
- [x] Submitting with missing or out-of-range answer values returns a validation error and does not create any rows
- [x] Submitting without a name in session redirects to `/`
- [x] Feature test covers the happy path and asserts score correctness

## Comments

- Implemented in `AssessmentController::store` + `POST /assessment` route (`assessment.store`); new `Answer` and `Result` models.
- Validation builds rules from the `statements` table: `answers` must be an array of exactly 25 entries, each a required integer 1–5; failure redirects back with errors and writes nothing.
- Persistence runs in a `DB::transaction` (1 User with `Str::uuid()` v4 token → 25 Answers → 5 Results = sum of that language's answers ÷ count), then redirects to `/results/{token}`.
- Feature test `tests/Feature/AssessmentSubmissionTest.php` covers the happy path with five hand-computed distinct averages (1.0, 2.2, 3.0, 4.4, 4.6), missing/out-of-range/non-integer answers, and missing session name — all asserting zero rows on rejection.
