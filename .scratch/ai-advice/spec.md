Status: ready-for-agent

# Spec: AI Advice on the Result Page

## Problem Statement

A Participant completes the assessment and learns which Love Language ranks highest, but a rank and a one-line description tell them little about what to do with that insight. They want practical, personalised guidance for their top Love Language — and they want it to be there every time they open their permanent Result Page link, without the application rethinking or re-generating it on each visit.

## Solution

When an Assessment is submitted, the application asks a free-tier AI provider (via the Laravel AI SDK) for short advice in Bahasa Indonesia, based on the Participant's top-ranked Love Language and their full score ranking. The advice is stored once with the Assessment and rendered as a bordered block on the Result Page, directly under the highlighted top Love Language. It is generated exactly once per Assessment: returning Participants reading their permanent link always get the stored text, never a fresh AI call. If the provider fails, the submission still succeeds and the Result Page simply renders without advice.

## User Stories

1. As a Participant, I want advice about my top-ranked Love Language displayed on my Result Page, so that I understand what my result means for how I give and receive affection.
2. As a Participant, I want the advice generated while I submit my assessment, so that my Result Page already contains it when I land on it.
3. As a Participant, I want the advice written in Bahasa Indonesia, so that it matches the language of the rest of the application.
4. As a Participant, I want the advice to consider my full five-score ranking, not just the winner, so that it reflects how dominant my top Love Language actually is.
5. As a Participant, I want the advice shown as a distinct bordered block directly beneath my highlighted top Love Language, so that it is clearly tied to that result.
6. As a Participant, I want the advice styled consistently with the RawBlock design system, so that the Result Page keeps its visual character.
7. As a Participant, I want my advice stored with my completed Assessment, so that every later visit to my Result Page shows exactly the same text.
8. As a Returning Participant, I want my permanent Result Page to make no AI calls, so that my link stays fast, deterministic, and works even if the AI provider is down.
9. As a Participant whose scores tie at the top, I want the advice to address the Love Language the page actually highlights, so that the two can never contradict each other.
10. As a Participant, I want my assessment submission to succeed even if the AI provider errors or times out, so that my completed answers are never lost.
11. As a Participant whose advice could not be generated, I want the Result Page to render normally without an advice block, so that the page never looks broken.
12. As a Participant concerned about privacy, I want only my Love Language names, descriptions, and score ranking sent to the provider — never my name or my raw answers — so that the minimum necessary data leaves the application.
13. As a Participant who retakes the assessment, I want new advice generated for my new Assessment, so that it reflects my new profile rather than my old one.
14. As a Participant with an Assessment completed before this feature existed, I want my Result Page to keep rendering without advice, so that legacy results remain valid pages.
15. As an operator, I want the AI provider selected by an environment key, so that I can configure or rotate credentials without touching code.
16. As an operator, I want the AI request bounded by a timeout, so that a hung provider cannot hang a Participant's submission.
17. As a developer, I want generation failures logged, so that I can diagnose provider or rate-limit problems after the fact.
18. As a developer, I want the Laravel AI SDK as a Composer dependency, so that provider integration goes through a supported, swappable interface rather than hand-rolled HTTP calls.
19. As a developer, I want tests to fake the advice agent with the SDK's built-in faking support, so that the suite never contacts a real provider.
20. As a developer, I want advice stored on the existing Participant record, so that displaying it needs no extra query, join, or table.

## Implementation Decisions

- **Dependency**: Add `laravel/ai` (Laravel AI SDK). Publish its configuration file only; do **not** publish or run its conversation-storage migrations — this feature keeps no conversation history.
- **Provider**: Groq as the sole provider, using the provider's default cheapest text model. No failover chain yet; failover across free providers is deferred until rate limits actually cause failures.
- **Credentials**: A single new API-key environment variable declared in the environment example file and read through the SDK's configuration.
- **Agent**: A dedicated advice agent class following the SDK's `make:agent` convention. No tools, no conversation context, no structured output — plain text in, plain text out. The prompt is assembled from data the agent receives, not by a separate prompt-builder module.
- **Prompt contents**: the top-ranked Love Language's name and description, plus the full five-Love-Language ranking with scores, so the model can judge dominance. Explicitly excluded: Participant name, raw Likert Responses, Result Token, and anything else identifying.
- **Prompt instructions**: reply in Bahasa Indonesia; two short paragraphs; plain prose — no headings, bullet lists, or markdown, since the RawBlock block renders plain text; an encouraging, practical tone; guidance grounded only in the supplied profile.
- **Timing**: advice is generated synchronously inside the submission request, after the Assessment transaction has committed and before the redirect. A provider failure can therefore never roll back answers or Results.
- **Failure handling**: the generation call runs with a bounded timeout inside a try/catch; exceptions are logged and swallowed. The submission proceeds and the advice column stays null.
- **Storage**: one new nullable TEXT column `advice` on the existing `users` table (one row = one completed Assessment). No provider name, model, token-usage, or timestamp columns — nothing reads them.
- **Regeneration policy**: generate exactly once, at submission. The results endpoint is a pure read: stored advice is displayed, null renders no block. No lazy fill, no backfill command, no refresh action.
- **Tie rule**: the advice targets whichever Love Language the Result Page highlights — the same "first after ordering by score descending" selection the view already uses — guaranteeing highlight and advice agree, including when all five scores are equal.
- **Display**: one new block on the Result Page, placed directly under the highlighted top Love Language section: font-mono label in Bahasa Indonesia, `border-[3px]` black border, no rounded corners, per the RawBlock system. The block is emitted only when advice exists.
- **Legacy assessments**: rows created before this feature keep a null advice and render no block. Deliberately not backfilled.
- **Domain vocabulary**: the new concept is **Advice**, added to `GLOSSARY.md`; existing terms (Assessment, Participant, Love Language, Result, Result Page) are used unchanged.

## Testing Decisions

- **What makes a good test**: assert only external behaviour — the HTTP response, what was persisted, and what was sent across the AI boundary. Never assert internal class structure or the exact prose of the prompt template.
- **Single seam**: the HTTP feature layer, extending the two existing feature test files that already own submission and results behaviour. No new test files, no new seams.
- **The one double**: the SDK's built-in `fake()` on the advice agent class. Tests may capture the prompt passed to the fake to assert boundary-level facts (e.g. the highlighted Love Language is named, the Participant's name is absent).
- **Scenarios covered at that seam**:
  1. Successful submission: agent faked → advice persisted on the Participant row, redirect target renders the advice block with the faked text, captured prompt names the highlighted Love Language.
  2. Provider failure: agent faked to throw → submission still succeeds, answers and Results still persisted, advice stays null, Result Page renders with no advice block.
  3. Result Page read path: a Participant with stored advice sees the block; one without sees none (covers pre-feature rows).
  4. Tie case: five equal scores → the highlighted Love Language and the one named in the prompt are the same.
- **Prior art**: `tests/Feature/AssessmentSubmissionTest.php` (submission + persisted-state assertions, `RefreshDatabase`, seeded statements) and `tests/Feature/ResultPageTest.php` (block-level HTML assertions via helper selectors). Follow their naming and structure; note that any existing test which POSTs the assessment will now hit the generation path and must fake the agent.
- Tests run offline: `php artisan test --compact`, PHPUnit 12, SQLite `:memory:`.

## Out of Scope

- Provider failover / multi-provider chains (add when free-tier rate limits prove painful)
- Backfilling or lazily generating Advice for Assessments completed before this feature
- Queued or streamed generation
- Structured multi-field advice (title, tips, action items) or any non-plain-text output
- Regenerating, editing, or manually re-triggering Advice after submission
- Storing provider name, model, usage, or generation timestamp alongside Advice
- Persisting conversation history (the SDK's conversation tables)
- Sending the Participant's name or raw answers to the provider
- Admin UI, dashboards, or analytics over Advice
- Switching the application to English or adding an i18n framework
- Unit tests asserting the literal prompt text

## Further Notes

- The whole UI is Bahasa Indonesia; the prompt must explicitly pin the output language or the model will default to English.
- Existing Result Page tests assert exact HTML structure (highlighted-block regex, article counts) — the new block must be inserted without disturbing those selectors, and tests asserting them may need their expectations updated once, deliberately.
- The generation call adds roughly 1–2 seconds to the submission request; the trade-off (fast permanent link forever vs. fast submit now) was made consciously in favour of a pure-read Result Page.
- No ADR: both the timing decision and the single-provider choice are cheap to reverse (one call site, one nullable column, one environment key).
- Groq's free tier rate limits are the most likely real-world failure; the failure path (log, no advice, page unaffected) is the designed-for state, not an edge case.
