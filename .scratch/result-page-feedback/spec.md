# Spec: Feedback section on the Result Page

Status: ready-for-agent

## Problem Statement

As the developer of the Love Languages assessment app, I have no way to hear from the people who actually use it. A Participant completes an Assessment, reads their Result Page, and leaves — there is no channel for them to tell me what worked, what confused them, or what they would change. I need a way to collect that feedback, once per Participant, without building accounts, an admin panel, or any way for feedback to become a burden to maintain.

## Solution

After completing an Assessment, a Participant sees a Feedback section on their Result Page — a single free-text box with a submit button. It appears together with their results and stays there on every visit until they submit. On submit, the section permanently swaps to a thank-you message; the Participant never sees the form again, cannot edit or delete what they wrote, and cannot read it back. Their Feedback is stored against their Assessment, capped at one per Participant, and read by the developer directly from the database.

## User Stories

1. As a Participant, I want to see a feedback form on my Result Page after completing my Assessment, so that I can share my thoughts about the app right when my experience is fresh.
2. As a Participant, I want the feedback form to use a single free-text box, so that I can write whatever I want without being forced through a rigid survey.
3. As a Participant, I want a clear prompt telling me my feedback goes to the developer, so that I understand who reads it and why it is being collected.
4. As a Participant, I want to submit my feedback with one button, so that the process takes seconds.
5. As a Participant, I want immediate confirmation after submitting, so that I know my feedback was received.
6. As a Participant, I want the thank-you message to be shown every time I revisit my Result Page afterwards, so that the page feels settled rather than offering me a form I already completed.
7. As a Participant, I want the feedback form to disappear forever once I have submitted, so that I cannot accidentally submit twice.
8. As a Participant, I want no way to edit or delete my feedback after submitting, so that what the developer receives is exactly what I sent, untampered.
9. As a Participant, I want no way to read my feedback back from the UI, so that the interface stays a one-way channel to the developer.
10. As a Participant, I want my feedback attempt rejected with a friendly validation message if I submit an empty message, so that I don't send nothing by accident.
11. As a Participant, I want an overly long message rejected with a clear character-limit message, so that I know to shorten it rather than losing it.
12. As a Participant, I want my typed message preserved in the form when validation fails, so that I never lose what I wrote.
13. As a Participant, I want my Result Page to work exactly as before if I never give feedback, so that skipping the form costs me nothing.
14. As a Participant, I want my feedback stored only once no matter how many times I click submit or reload, so that the developer doesn't receive duplicates.
15. As a Participant who shares my Result Token link, I want anyone holding the link to see the same form-until-submitted state as me, so that the page behaves consistently under the app's existing trust model (the token identifies me).
16. As the developer, I want exactly one Feedback row per Participant, enforced in the database, so that duplicates are impossible regardless of UI or race conditions.
17. As the developer, I want each Feedback row linked to the Participant's Assessment, so that I know who sent it without any contact details being collected.
18. As the developer, I want feedback to contain only the free-text message, so that participants are never asked for emails, names, or anything else they'd have to invent.
19. As the developer, I want submission to the feedback endpoint rate-limited, so that a token link cannot be used to flood my database.
20. As the developer, I want to read Feedback directly from the database, so that I can ship the collection feature without building a read/admin surface first.
21. As the developer, I want validation messages in Bahasa Indonesia matching the rest of the app, so that the experience is consistent for my audience.
22. As a returning Participant, I want the thank-you state and the form state to occupy the same slot on the page, so that the layout never jumps or looks broken between visits.
23. As a Participant who never completed an Assessment, I want feedback submission to be impossible, so that only real results carry feedback.
24. As a maintainer, I want the new Feedback term documented in the glossary, so that future contributors use one vocabulary for this concept.

## Implementation Decisions

- **Domain term: Feedback.** One per Participant, created only after the Assessment is completed, immutable and invisible to its author. The Result Page shows either the form or the thank-you — never the submitted content. Added to `GLOSSARY.md`.
- **Schema:** new table `feedback` (Laravel's pluralizer leaves `feedback` unpluralised) with `id`, `user_id` (FK to `users`, **unique**, cascade delete), `message` (text), timestamps. A `Feedback` model with a factory, plus a `hasOne` `feedback` relation on the Participant model.
- **Gate = database constraint.** "One feedback only" is enforced by the unique `user_id` column, not by session state: the session dies at Assessment completion and the Result Token is the only durable identity (it is explicitly "not a secret", same trust model as the Result Page itself).
- **Route:** one new POST endpoint nested under the existing Result Page URL pattern (`/results/{token}/feedback`), same token regex constraint as the Result Page route, behind `throttle:5,1`. No GET/read/edit/delete routes exist — the Result Page GET is the only reader.
- **Controller:** a new action on the existing result controller (not a new controller — one extra action). Looks up the Participant by token with the same first-or-fail semantics as the Result Page; if Feedback already exists, redirects back (idempotent, the unique constraint is the backstop); otherwise inline-validates (`message` required, string, max 1000 chars, Indonesian custom messages — house pattern) and creates the row, then redirects to the Result Page.
- **Validation failure:** redirect back to the Result Page with errors and `old('message')`; the form re-renders with the typed message preserved.
- **State swap is server-rendered:** the Result Page controller passes the Participant's Feedback (or absence) to the view; the view renders a `@if` form / thank-you branch. No flash, no Alpine state, no session flag — the DB row *is* the state.
- **Placement:** the Feedback section is the last block on the Result Page, after the permanent-link aside; thank-you occupies the same slot forever.
- **Copy (Bahasa Indonesia, RawBlock design system — 3px borders, 0 radius, uppercase buttons):**
  - Heading: `Feedback`; sub-line: *"Sudah coba aplikasi ini? Ceritakan pengembang apa yang kamu pikirkan."*
  - Button: `KIRIM FEEDBACK`
  - Thank-you (permanent): *"Terima kasih sudah mencoba aplikasi ini dan sudah memberikan feedback!"*
- **ERD:** the schema documentation file is updated to mirror the new table.
- **No ADR:** nothing here is hard to reverse or surprising without context — the whole app already treats the token-holder as the Participant.

## Testing Decisions

- **Good tests here exercise only external behaviour** — HTTP requests and what they render or persist — never controller internals or view structure. Assertions target rendered HTML sections by id/text (the house pattern used for the advice and permanent-link sections) plus database state.
- **Single seam: the HTTP routes.** This is the existing seam used by the prior-art tests; no new seams are introduced (no controller unit tests, no JS tests — the state swap is server-rendered).
- **Modules covered:**
  - `GET` the Result Page before feedback → form section renders (heading, textarea, button).
  - `POST` feedback → row persisted, redirect, re-`GET` shows thank-you and the form is gone; the submitted message never appears anywhere in the rendered HTML.
  - Second `POST` → no second row (idempotent redirect).
  - Validation failure (empty / over-limit message) → no row, errors rendered, typed message preserved via `old()`.
  - Unauthenticated-to-the-Assessment angle: a token for a Participant with no such route access fails the same way the Result Page already does (first-or-fail).
  - Throttle: at least one test confirming the rate limit engages on rapid repeat submission (cheap, one assertion block).
- **Prior art:** the existing Result Page feature test (GET rendering with seeded Participant) and the assessment submission feature test (POST → rows → redirect). Same scaffolding: `RefreshDatabase`, seeded database per test, the shared completed-participant factory helper, `assertSee`/`assertDatabaseHas` style assertions.
- **Runner:** PHPUnit (not Pest), `php artisan test`, narrow to the new test file first, then the full suite.

## Out of Scope

- Any read, edit, or delete capability for Feedback — by design, for Participants *and* the developer.
- Admin UI, dashboard, or artisan command to read Feedback (developer reads the database directly; build a reader later if the pain warrants).
- Ratings, star scores, contact-email capture, or any field beyond the single free-text message.
- Flash/one-time thank-you rendering — the thank-you is permanent by design.
- Accounts, authentication, or any identity model beyond the existing Result Token.
- Spam protection beyond `throttle:5,1` (no CAPTCHA, no honeypot, no fingerprinting).
- Feedback analytics, tagging, or export.
- Rate-limit-specific UX (a throttled participant just sees Laravel's default 429).

## Further Notes

- Known accepted edge case: anyone holding a Result Token sees the feedback form until the Participant submits; afterwards everyone sees the thank-you. This matches the Result Page's existing trust model (the token identifies the Participant and is documented as "not a secret").
- The glossary previously implied the feedback *section* disappears after submission; the confirmed behaviour is that the **form** disappears and a thank-you section stands in its place permanently. The new glossary entry must word it that way.
- Glossary vocabulary to use throughout: **Participant**, **Assessment**, **Result Token**, **Result Page**, **Feedback**.
