# 02: Feedback section on the Result Page

**What to build:** A Participant who completed their Assessment sees a Feedback section as the last block on their Result Page — heading "Feedback", a prompt line, a free-text box and a `KIRIM FEEDBACK` button. They can submit exactly once: the message persists against their Assessment, and from then on every visit to the Result Page shows a permanent thank-you message in the same slot instead of the form. They can never read their message back, and nothing about the rest of the Result Page changes for Participants who never give feedback.

**Blocked by:** 01: Feedback schema & domain model.

**Status:** ready-for-agent

- [x] The Result Page renders the feedback form (RawBlock styles: 3px borders, 0 radius, uppercase button) as the last block, when the Participant has not yet given Feedback
- [x] Submitting a valid message persists one Feedback row for the Participant and redirects back to the Result Page
- [x] Re-visiting the Result Page after submitting shows the thank-you section ("Terima kasih sudah mencoba aplikasi ini dan sudah memberikan feedback!") in the form's slot; the form never renders again
- [x] The submitted message never appears anywhere in the rendered Result Page HTML
- [x] Validation failure (empty or over-1000-char message) redirects back with Bahasa Indonesia errors, preserves the typed message, and creates no row
- [x] A repeat POST (double click / resubmit) creates no second row (idempotent redirect)
- [x] The feedback POST route uses the same token regex constraint as the Result Page and is behind `throttle:5,1`, with a test confirming the rate limit engages
- [x] A Participant without a completed Assessment cannot submit feedback (same first-or-fail behaviour as the Result Page)
- [x] Feature tests live at the single HTTP seam, following the existing Result Page / submission test conventions (PHPUnit, `RefreshDatabase`, seeded database)
- [x] Tests pass (`php artisan test`) and formatting is clean (`vendor/bin/pint --dirty --format agent`)
