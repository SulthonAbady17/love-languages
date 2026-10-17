# 02: Encrypted Participant name at rest

**What to build:** The Participant's name is protected at rest while the Result Page keeps greeting them by name. The name attribute carries the framework's `encrypted` cast (a `casts()` method on the model), so every read inside the application still returns plaintext but the database only ever stores ciphertext. The name column widens from string to text in a new migration so the base64 encrypted payload of a 100-character multi-byte name cannot overflow a 255-byte varchar. Session payloads are encrypted at rest by flipping the session-encrypt key to true in both the local environment file and the environment example file, closing the plaintext-name-in-session-rows hole. A new test reads the raw users row after a real submission and asserts the plaintext name is absent. The ER diagram shows the name column as text. Only the name receives this treatment — Likert Responses, Results, and Advice are untouched.

**Blocked by:** None (can start immediately)

**Status:** done

- [x] The name column is text after migration and the model decrypts transparently: all existing Result Page and submission tests pass with `$user->name` returning the plaintext
- [x] After a real POST, the raw users row's name value does not contain the submitted plaintext name (new test at the existing submission seam)
- [x] Session-encrypt key is `true` in both the environment file and the environment example file
- [x] Likert Responses, Results, and Advice remain stored in plain form (no casts added to them)
- [x] ER diagram name column updated to text
- [x] `vendor/bin/pint --dirty --format agent` clean; full suite green via `php artisan test --compact`
