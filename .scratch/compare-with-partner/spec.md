Status: ready-for-agent

# Spec: Compare with Partner

## Problem Statement

After completing the Assessment, a Participant receives a permanent Result Page showing their Love Language profile. There is currently no way to compare their profile with another Participant's. Couples and friends who both take the Assessment cannot easily see where their Love Languages align or differ.

## Solution

Add a "Compare with Partner" flow. On any Result Page, a Participant can enter another Participant's Result Token and be taken to a Comparison Page at `/compare/{tokenA}/{tokenB}`. The Comparison Page shows both Participants' ranked Love Language Results side by side as cards, so they can read each other's profile at a glance.

No AI summary is generated — the Comparison Page is purely derived data from two existing Assessments. No new database tables or persisted state are required.

## User Stories

1. As a Participant, I want to see a "Compare with Partner" section on my Result Page, so that I know the comparison feature exists.
2. As a Participant, I want to enter my partner's Result Token into a field on my Result Page, so that I can initiate a comparison.
3. As a Participant, I want to submit the comparison form and be taken to a dedicated Comparison Page, so that I can see both profiles at once.
4. As a Participant, I want the Comparison Page URL to contain both Result Tokens, so that I can bookmark or share the link with my partner.
5. As a Participant, I want to see my name in the header of my card on the Comparison Page, so that I know which card is mine.
6. As a Participant, I want to see my partner's name in the header of their card, so that I can identify whose profile is whose.
7. As a Participant, I want each card to list all five Love Languages ranked by that Participant's own scores (highest first), so that I can see each person's full profile in order of priority.
8. As a Participant, I want each Love Language row to show the score in the format `4.5/5.0`, so that I understand what the score means without needing a legend.
9. As a Participant, I want each Love Language row to show a progress bar scaled to 5.0, so that I can compare scores visually at a glance.
10. As a Participant, I want the two cards to appear side by side on desktop, so that I can scan across both profiles easily.
11. As a Participant, I want the two cards to stack vertically on mobile (my card first, partner's below), so that both profiles are readable on a small screen.
12. As a Participant, I want a back link on the Comparison Page that returns me to my own Result Page, so that I can navigate back without using the browser button.
13. As a Participant, I want to see an inline error on my Result Page if I enter a Result Token that does not belong to any Participant, so that I know to try a different token.
14. As a Participant, I want to see an inline error if I enter my own Result Token in the comparison field, so that I know I need to enter someone else's token.
15. As a Participant visiting any Result Page (not just my own), I want to be able to initiate a comparison from that page, so that the comparison feature is accessible without needing to complete my own Assessment first.

## Implementation Decisions

- **No persistence.** The Comparison is fully derived on every request from the two existing `User` + `Result` + `LoveLanguage` records. No new table, no new model, no new migration.

- **One new route.** `GET /compare/{tokenA}/{tokenB}` → `CompareController@show`. Both tokens are validated by the existing route constraint (`^[a-z0-9]{16}$`). The controller 404s if either token is unknown, and redirects back to `/results/{tokenA}` with a flash error if tokenA equals tokenB (self-comparison).

- **Compare form on the Result Page.** A new section is added between the permanent-link aside and the feedback section. It is a plain `GET` form that submits the partner token as a query parameter; the controller or a redirect action constructs the canonical `/compare/{tokenA}/{tokenB}` URL. The current page's token (`tokenA`) is baked in as a hidden field or part of the form action. Validation (unknown token, self-comparison) is enforced in the controller and surfaced via a session flash error rendered inline in this section.

- **CompareController.** A single `show(string $tokenA, string $tokenB)` method. Looks up both Users by token (`firstOrFail` for tokenA, manual check + redirect-back for tokenB). Guards against self-comparison with redirect-back. Passes both Users with their `rankedResults` (eager-loaded with `loveLanguage`) to the view.

- **Comparison Page view.** Two cards rendered side by side (Tailwind grid, `md:grid-cols-2`; single column on mobile). Each card: header with Participant name; body with Love Languages in that Participant's own rank order (highest score first). Each row shows rank number, Love Language name, score as `4.5/5.0`, and a progress bar (width percentage = `value / 5 * 100`). A back link at the top targets `/results/{tokenA}`.

- **Directional URL.** `/compare/{tokenA}/{tokenB}` — tokenA is always the Initiating Participant (the one whose Result Page contained the form). No canonical sorting. The two orderings are distinct pages.

- **Token format enforced by route.** The route constraint (`^[a-z0-9]{16}$`) rejects malformed tokens before the controller runs, returning 404 automatically.

- **Error handling for unknown partner token.** Controller manually checks if the partner User exists. If not, redirects to `/results/{tokenA}` with `session('error')` containing a user-facing message. The Result Page section renders this error inline.

- **Self-comparison guard.** If `tokenA === tokenB`, redirect to `/results/{tokenA}` with a flash error before any DB lookup for the partner.

## Testing Decisions

- **One new feature test file:** `CompareControllerTest`. This is the single seam for the entire feature — all behaviour is observable through HTTP responses, the same way `ResultPageTest` and `ResultPageFeedbackTest` cover their respective routes.

- **Good tests here** assert on rendered HTML (Participant names, Love Language names in rank order, scores in `x.x/5.0` format), HTTP status codes, and redirect targets with flash session data. They do not assert on controller method names, view file names, or query counts.

- **Prior art:** `ResultPageTest` — the `completedParticipant(string $name, array $values): User` helper (creates a `User` via factory and 5 `Result` rows keyed to the seeded `LoveLanguage` IDs) is the exact pattern to replicate for both Participants in a Comparison test.

- **Scenarios to cover:**
  - Happy path: valid tokenA + valid tokenB → 200, both names visible, scores rendered.
  - Unknown partner token → redirect to `/results/{tokenA}`, flash error visible.
  - Self-comparison (tokenA === tokenB) → redirect to `/results/{tokenA}`, flash error visible.
  - Unknown tokenA → 404.
  - Malformed token (wrong format) → 404 (route constraint).
  - Compare form section is present on the Result Page.
  - Back link on Comparison Page targets `/results/{tokenA}`.
  - Rank order within each card follows that Participant's own score descending.

## Out of Scope

- AI-generated summary or advice on the Comparison Page.
- Persisting Comparison records to the database.
- Consent or opt-in mechanism for being compared.
- Canonical URL normalisation (sorting the two tokens alphabetically).
- Limiting comparison to only the Participant who owns the Result Page.
- Notifications to the Partner when a Comparison is initiated.

## Further Notes

- The word "permalink" was used during design but the glossary term is **Result Token**. The comparison input field should use Result Token in its label and help text.
- The Comparison Page introduces two new domain concepts — **Comparison** and **Initiating Participant** — which have been added to `GLOSSARY.md`.
- The progress bar scale is fixed at 5.0 (the Likert maximum), not relative to the highest score on the page, so bars represent absolute scores and remain comparable across both cards.
