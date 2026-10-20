Status: ready-for-agent

# Spec: Comparison Advice & Short Comparison Permalink

## Problem Statement

After comparing profiles, a Participant sees only the two scorecards on the Comparison Page — there is no takeaway. The Result Page has an AI-written **Advice** for one person; Couples and friends who compare get nothing equivalent for the pair, even though a two-person reading of the same data is exactly what they came for. Additionally, the Comparison Permalink is long and unwieldy — it embeds two full Result Tokens (`/compare/{tokenA}/{tokenB}`), which makes it awkward to save, share, or paste back.

## Solution

A Comparison becomes a persisted thing with its own identity. When a Participant submits the comparison form, a **Comparison** record is created for that ordered pair, an AI writes a two-paragraph Bahasa Indonesia **Comparison Advice** (a ringkasan of how the two profiles fit, then saran for the pair), and the Participant is redirected to a short **Comparison Permalink** containing a single opaque **Comparison Token** — the same shape as a Result Permalink. The Comparison Page shows the two cards exactly as today, with a new "Ringkasan & Saran" section underneath, sourced from the stored advice. Resubmitting the same pair reopens the same Comparison with the same token and the same advice.

## User Stories

1. As a Participant, I want to submit the comparison form as a deliberate POST action (with CSRF), so that creating a Comparison is not something a link-click or prefetch can trigger by accident.
2. As a Participant, I want the submission to take me to a short Comparison Permalink containing exactly one Comparison Token, so that the link is easy to save, bookmark, and paste back later.
3. As a Participant, I want the Comparison Permalink to use the same opaque 16-character lowercase-alphanumeric format as the Result Token, so that no new token vocabulary or format is introduced.
4. As a Participant, I want a "Ringkasan & Saran" section on the Comparison Page, so that I get a written takeaway instead of only raw scorecards.
5. As a Participant, I want the ringkasan paragraph to summarize how the two Love Language profiles align and differ, so that I understand the relationship between the scores at a glance.
6. As a Participant, I want the saran paragraph to give practical, couple-oriented guidance based on both rankings, so that I know what to do with the comparison.
7. As a Participant, I want both paragraphs written in Bahasa Indonesia, so that the text matches the rest of the application.
8. As a Participant, I want the "Ringkasan & Saran" section to appear below both scorecards, so that I read the data first and the interpretation after.
9. As a Participant, I want the section styled like the Advice section on the Result Page (terracotta uppercase label, body prose), so that the two pages feel like one product.
10. As a Participant, I want the advice paragraphs separated by a line break, so that the ringkasan and saran read as two distinct thoughts.
11. As a Participant, I want my partner's name and my own name to never appear inside the advice text, so that the AI output is never personalized with identities from the database.
12. As a Participant, I want the copy-link component on the Comparison Page to offer the short Comparison Permalink, so that the link I save is the new short one.
13. As a Participant, I want the back link to my Result Page to keep working unchanged, so that I can still navigate back.
14. As a Participant, I want both scorecards, their rank order, scores, progress bars, and mobile stacking to stay exactly as they are, so that the comparison data itself is untouched.
15. As a Participant, I want submitting the same partner again in the same order to reopen the same Comparison with the same Comparison Token, so that I always get one stable page per pair-and-order.
16. As a Participant, I want a resubmission of an existing pair not to trigger a second AI call, so that repeated visits cost nothing and never change the stored advice.
17. As a Participant, I want my partner starting the comparison in the reverse order (partner pasting my link) to get a separate Comparison with their card first, their own Comparison Token, and its own advice, so that whoever initiates owns the page — as the two orderings are distinct pages today.
18. As a Participant, I want the comparison form on the Result Page to still accept either a bare Result Token or a pasted Result Permalink, so that the input behaviour I know does not change.
19. As a Participant, I want entering my own token to still show the self-comparison inline error on my Result Page, so that the guard I know does not change.
20. As a Participant, I want an unknown partner token or a malformed input to still show the unknown-partner inline error on my Result Page, so that the guard I know does not change.
21. As a Participant, I want my comparison submissions rate-limited, so that the AI cannot be burned by scripted spam of novel pairs.
22. As a Participant whose AI call fails at creation time, I want the Comparison to still be created and the scorecards to still render, so that one provider outage never costs us the comparison itself.
23. As a Participant viewing a Comparison whose advice is absent, I want the "Ringkasan & Saran" section to simply not render, so that I never see an empty box or an error.
24. As a Participant, I want the stored advice to be generated exactly once — never regenerated on revisit or resubmit — so that the text I first saw is the text the link always shows.
25. As a Participant holding an old two-token Comparison link, I want it to 404 rather than silently redirect, so that there is exactly one canonical URL shape for a Comparison.
26. As a Participant, I want the Result Page's own Advice (about my single profile) to remain completely unchanged by this feature, so that my Result Permalink experience is unaffected.

## Implementation Decisions

- **Persistence.** A new `comparisons` table is introduced. Columns: id, initiator FK to users (cascade delete), partner FK to users (cascade delete), token (bounded string, unique index), advice (nullable text), timestamps. A composite unique index on (initiator_id, partner_id) makes the ordered pair the identity of a Comparison. This reverses the current domain rule that a Comparison is derived and never persisted; the glossary is rewritten accordingly.

- **Ordered-pair identity.** (Andi→Budi) and (Budi→Andi) are two distinct Comparisons: two rows, two Comparison Tokens, two advice texts, initiator card first on each. This preserves the existing "two orderings are distinct pages" rule under the new single-token URL scheme.

- **Comparison Token.** The existing token generator class is renamed to a neutral name (it now mints both Result Tokens and Comparison Tokens) and its format is unchanged: 16 characters, lowercase a–z0–9, CSPRNG. The permalink route constraint stays `^[a-z0-9]{16}$`; malformed tokens 404 before reaching the controller.

- **Creation point.** The comparison form on the Result Page changes from GET to POST (with CSRF). The POST route (carrying the initiator's Result Token, receiving the partner reference as input) performs: the existing self-compare and unknown-partner validations (including parsing a pasted Result Permalink down to a bare token), then a find-or-create on the ordered pair. Only in the branch where the row was actually created is the Comparison Advice generated, inline, immediately. Both branches redirect (302) to `GET /compare/{comparisonToken}`.

- **Strict one-shot advice.** Advice is generated only at row creation. If the row already exists — even one whose advice is null after a provider failure — it is never touched again. (The user explicitly deferred the "retry generation on resubmit if advice is null" idea; both Advice kinds currently share the generate-once-never-regenerate rule. See Further Notes.)

- **Failure swallowing.** A provider failure during Comparison Advice generation is logged and swallowed; the Comparison row survives with advice = null. Mirrors the Result Page's Advice behaviour exactly.

- **Rate limiting.** The creation (POST) route carries the same throttle as the feedback route (5 per minute), since every novel pair costs one AI call.

- **Legacy URL removal.** The old `GET /compare/{tokenA}/{tokenB}` route is deleted, not redirected. Old shared links 404. `CompareController` ends up with exactly two entry points: the POST creation route and the GET permalink route.

- **Comparison Advice agent.** A new agent, sibling to the existing Advice agent: same stack (timeout, stop-slop skill, promptable), constructed from both Participants' ranked Results and names, instructed to return exactly two plain-prose paragraphs in Bahasa Indonesia — ringkasan first, saran second — and never to include either Participant's name in the output. It is fake-able through the framework's AI agent fake, the same way the existing Advice agent is faked in feature tests.

- **Comparison model.** Fillable: initiator_id, partner_id, token, advice. Relationships: initiator and partner as belongs-to User. The view receives the Comparison plus both Participants with their ranked Results eager-loaded.

- **View changes.** The copy-link component on the Comparison Page points at the single-token Comparison Permalink (message unchanged). A new "Ringkasan & Saran" section is appended after the two cards, rendered only when advice is non-null, paragraphs via nl2br, markup mirroring the Result Page's Advice section.

- **Preserved behaviour.** Card layout, initiator-first ordering, rank/score/progress-bar rendering, back link, `data-participant` card markers, the results-page compare section's position in the page order, and all three input guards (self, unknown, malformed) are unchanged.

- **Domain docs.** Glossary: rewrite **Comparison** (persisted, ordered pair, identified by Comparison Token), **Comparison Permalink** (single-token URL), and **Initiating Participant** (their Result Token no longer appears in the URL; they own the Comparison and their card comes first); add **Comparison Token** and **Comparison Advice** entries; note that both Advice kinds are generated once and may be absent. The ERD document gains the `comparisons` table, and also gains the Result Page's `advice` column on users, which is already missing there.

## Testing Decisions

- **Good tests here** assert only external behaviour: HTTP status codes, redirect targets, flash session data, and rendered HTML (names, rank order, scores, the presence or absence of the "Ringkasan & Saran" section, the permalink URL in the copy-link component). They do not assert on controller method names, model names, view file names, row counts, or prompt internals.

- **One seam: the existing `CompareControllerTest` feature test file.** Everything is observable through the two routes (`POST /compare/{tokenA}` and `GET /compare/{token}`). No new test files, no new seams. The AI dependency is cut at the framework's agent fake boundary — `ComparisonAdviceAgent::fake()` — which is the same seam `AssessmentSubmissionTest` already uses with `AdviceAgent::fake()`, including the throw-on-prompt form for the provider-failure case.

- **Prior art:** the existing `CompareControllerTest` helpers — `completedParticipant(string $name, array $values): User` (factory user + five Results keyed to seeded Love Language ids), `card()` (regex the `data-participant` article), and `compareSection()` (regex the compare section) — are reworked in place, not replaced. The `AssessmentSubmissionTest` fake/assert-prompted patterns are the model for the agent-fake scenarios.

- **Scenarios to cover:**
  - POST with valid partner token → 302 to `/compare/{16-char token}`; Comparison row exists for the ordered pair.
  - POST with a pasted Result Permalink (with trailing slash and query string) → same as above.
  - Resubmitting the same ordered pair → redirects to the same Comparison Token; agent fake confirms exactly one prompt total.
  - Reverse order (partner as initiator) → a second Comparison with a different token; both pages show their own initiator first.
  - `ComparisonAdviceAgent::fake(['para one', 'para two'])` → section renders with both paragraphs under the cards.
  - Agent throws → redirect still happens, page returns 200, scorecards render, "Ringkasan & Saran" section absent, row exists with null advice.
  - GET `/compare/{token}` with an unknown or malformed token → 404.
  - Legacy `GET /compare/{tokenA}/{tokenB}` (both orders) → 404.
  - Self-compare and unknown/malformed partner → unchanged redirect-back with flash errors, rendered inline on the Result Page.
  - Result Page compare section is a POST form with CSRF; its position between the permanent link and feedback is unchanged.
  - Copy-link on the Comparison Page shows the single-token permalink.
  - Back link to the initiator's Result Page; cards, scores, and progress bars byte-for-byte equivalent to today's assertions.

## Out of Scope

- Retrying a failed Comparison Advice generation on resubmit (explicitly deferred by the user; see Further Notes).
- Regenerating or updating advice after creation, for either Comparison Advice or the Result Page's Advice.
- Queued/backgrounded generation.
- Redirecting or otherwise preserving the legacy two-token Comparison URLs.
- An ADR (decided against; the glossary rewrite carries the context).
- Consent, opt-in, or notification for the Partner being compared.
- Any change to the Result Page's Advice column, agent, or rendering.
- A shorter Comparison Token format (format stays identical to the Result Token).
- A unit test for the new agent's skill wiring (declined; the existing agent's skill test is not being mirrored).
- Canonical normalisation of pair order (the two orderings remain separate Comparisons by design).

## Further Notes

- The deferred question, recorded so it is not lost: when the AI fails at creation, the Comparison exists forever with no advice. The user chose strict one-shot for now because the same rule applies to the Result Page's Advice, and wants to revisit both together later. The glossary should state the shared rule ("generated once, may be absent, never regenerated") so the future decision has a single place to land.
- Indonesian UI strings: the new section label is "Ringkasan & Saran"; the copy-link message on the Comparison Page is unchanged ("Simpan tautan ini untuk melihat perbandingan kalian kembali").
- The word "permalink" in the UI stays; the domain vocabulary is **Comparison Permalink** (the URL) and **Comparison Token** (the opaque value inside it).
- The partner input on the Result Page continues to accept either a bare token or a pasted Result Permalink — only the HTTP method around it changes.
