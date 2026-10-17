# 01: Feedback schema & domain model

**What to build:** The database guarantees one Feedback per Participant: a `feedback` table stores a single free-text message against the Participant's Assessment, with a unique foreign key so a second Feedback row can never exist — regardless of UI or race conditions. The Feedback term is documented in the glossary and the schema documentation mirrors the new table.

**Blocked by:** None (can start immediately).

**Status:** ready-for-agent

- [x] Migration creates the `feedback` table: id, unique FK to the Participant (`users`) with cascade delete, `message` text, timestamps
- [x] A duplicate Feedback for the same Participant is rejected at the database level (verified by test)
- [x] A Feedback model with a factory exists, and the Participant model exposes a `hasOne` Feedback relation
- [x] Glossary gains a **Feedback** entry: one per Participant, created only after Assessment completion, immutable and invisible to its author; the Result Page shows either the form or the thank-you, never the content
- [x] The ERD/schema documentation file mirrors the new table
- [x] Tests pass (`php artisan test`) and formatting is clean (`vendor/bin/pint --dirty --format agent`)
