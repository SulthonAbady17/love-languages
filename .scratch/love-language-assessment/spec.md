Status: ready-for-agent

# Spec: Love Language Assessment Application

## Problem Statement

A user wants to discover their dominant Love Language — the way they most prefer to give and receive affection. Currently there is no application to guide them through a structured self-assessment. They need a simple, frictionless web tool that presents statements for reflection, collects their Likert responses, and presents a ranked result they can return to later.

## Solution

A single-page-flow web application built on Laravel + Blade + Alpine.js + Tailwind CSS (RawBlock design system). A Participant enters their name, steps through 5 Love Language sections (5 statements each, Likert 1–5), and receives a permanent Result Page at `/results/{token}` showing their Love Languages ranked by average score, with the top one highlighted and a short description for each.

## User Stories

1. As a Participant, I want to see a welcome screen that explains the assessment, so that I know what I'm about to do before I begin.
2. As a Participant, I want to enter my name before starting the assessment, so that my result is personalised.
3. As a Participant, I want to see a progress indicator (e.g. "Step 2 of 5"), so that I know how far through the assessment I am.
4. As a Participant, I want to see all statements for one Love Language at a time, so that I can focus without being overwhelmed.
5. As a Participant, I want to rate each statement on a 1–5 Likert scale (Sangat Tidak Setuju → Sangat Setuju), so that I can express how strongly I agree with each statement.
6. As a Participant, I want to be prevented from proceeding to the next step until I have answered all statements on the current step, so that my result is complete.
7. As a Participant, I want to navigate back to the previous step to change my answers, so that I can correct a response before submitting.
8. As a Participant, I want to submit my completed assessment, so that my results are calculated and saved.
9. As a Participant, I want to be taken directly to my Result Page after submission, so that I see my results immediately.
10. As a Participant, I want to see all five Love Languages ranked from highest to lowest average score, so that I understand my full profile.
11. As a Participant, I want to see my top Love Language highlighted prominently, so that I can identify it at a glance.
12. As a Participant, I want to see a short description for each Love Language on the Result Page, so that I understand what each one means.
13. As a Participant, I want the Result Page URL to be my permanent link (e.g. `/results/abc123`), so that I can bookmark or share it and return to my results later.
14. As a returning Participant, I want to visit my Result Page URL at any time, so that I can review my Love Language results without retaking the assessment.
15. As a Participant, I want the UI to be in Bahasa Indonesia, so that the application is accessible to Indonesian-speaking users.
16. As a Participant, I want the application to look and feel consistent with the RawBlock design system (brutalist aesthetic, thick borders, no rounded corners, Archivo Black headings), so that the UI has a clear, intentional character.

## Implementation Decisions

- **Framework**: Laravel 13, Blade templates, Alpine.js for front-end interactivity, Tailwind CSS with RawBlock design system tokens.
- **Language**: All UI copy and statements are in Bahasa Indonesia.
- **No authentication**: No login, no password, no email. Identity is a self-reported name and a Result Token.
- **Result Token**: A UUID v4 generated at Assessment submission time and stored as `token` on the `users` table. The Result Page URL is `/results/{token}`.
- **One submission per Participant (soft)**: Enforced naturally by the token — a Participant who loses their token can retake under a new name. No hard browser-cookie enforcement.
- **Database schema** (derived from ERD.dbml, with `categories` renamed to `love_languages`):
  - `users`: `id`, `name` (varchar), `token` (uuid, unique), `created_at`, `updated_at`
  - `love_languages`: `id`, `name` (varchar), `description` (text), `created_at`, `updated_at`
  - `statements`: `id`, `love_language_id` (FK → love_languages), `body` (text, in Bahasa Indonesia), `created_at`, `updated_at`
  - `answers`: `id`, `user_id` (FK → users), `statement_id` (FK → statements), `value` (tinyint 1–5), `created_at`, `updated_at`
  - `results`: `id`, `user_id` (FK → users), `love_language_id` (FK → love_languages), `value` (float — average Likert score), `created_at`, `updated_at`
- **Score calculation**: `value = SUM(answers.value) / COUNT(statements)` per Love Language, computed server-side at submission, stored in `results`.
- **Statements**: 25 total — 5 per Love Language — authored in Bahasa Indonesia and seeded from `statements.json`.
- **Assessment flow**: Multi-step form, one Love Language per step (5 steps). Managed client-side with Alpine.js. State is held in the browser until final submission (single POST).
- **Result display**: Ranked list of all 5 Love Languages by `results.value` DESC. Top one is visually highlighted. Each shows name, score, and short description.
- **Front-end assets**: Tailwind CSS + Alpine.js installed via npm/Vite. Google Fonts (Archivo Black, Work Sans, Space Mono) loaded via `@font-face` or CDN.
- **Seeding**: `DatabaseSeeder` reads `statements.json`, seeds `love_languages` and `statements`. No user/answer/result seeding.

## Testing Decisions

- **What makes a good test**: Test observable HTTP behaviour and stored state — not class internals, not private methods. A good test makes a request and asserts the response or the database.
- **Primary seam**: The HTTP layer (Feature tests via `tests/Feature/`). This is the highest seam possible: it covers routing, controller logic, score calculation, and DB writes in one pass.
- **Modules to test**:
  - `GET /` — welcome page renders with a name field
  - `POST /assessment` — submits answers, creates user + answers + results rows, redirects to `/results/{token}`
  - `GET /results/{token}` — returns 200 with ranked results for a valid token; returns 404 for an unknown token
  - Score calculation: POST with known answers, assert `results.value` equals the expected average
- **Prior art**: `tests/Feature/ExampleTest.php` uses `$this->get(...)→assertStatus(200)` — follow this pattern with `RefreshDatabase`.

## Out of Scope

- User authentication (login, registration, password reset)
- Email collection or email-based result retrieval
- Hard "one attempt" enforcement (cookie-based or IP-based)
- Admin panel or statement management UI
- Multiple languages / i18n framework
- Social sharing buttons
- PDF export of results
- Comparison between multiple Participants
- Charts or radar graphs on the Result Page

## Further Notes

- The `users` table in the ERD was designed for authenticated accounts; it is repurposed here as a lightweight Participant record. The `password`, `email`, `email_verified_at`, and `remember_token` columns from Laravel's default migration are not needed and should be dropped or never created.
- The canonical term in code and copy is **LoveLanguage** (model), **love_languages** (table), never "category".
- Statements must be written and placed in `statements.json` at the repo root before the seeder can run (Step 1 of the user's workflow).
- The RawBlock design system forbids rounded corners, shadows, and any colour other than black/white/pure-blue (links only). Likert radio buttons are the one component that uses a circle shape (per DESIGN.md).
