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

**Status:** ready-for-agent

- [ ] `POST /assessment` with valid answers creates 1 User, 25 Answer, and 5 Result rows
- [ ] The User row has a non-null UUID token
- [ ] Each Result row's `value` is the correct average for that Love Language (verified with a known input)
- [ ] Successful submission redirects to `/results/{token}`
- [ ] Submitting with missing or out-of-range answer values returns a validation error and does not create any rows
- [ ] Submitting without a name in session redirects to `/`
- [ ] Feature test covers the happy path and asserts score correctness
