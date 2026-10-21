# 02: Persist Comparisons behind a short permalink

**What to build:** A Comparison becomes a persisted thing. The compare form on the Result Page submits as a POST (with CSRF). Submitting it finds-or-creates a Comparison for the ordered pair (initiator, partner) and redirects to a short Comparison Permalink containing a single Comparison Token — `/compare/{token}`, same opaque 16-character shape as a Result Permalink. The Comparison Page renders the two scorecards exactly as today, with the copy-link offering the short URL. Resubmitting the same ordered pair reopens the same Comparison with the same token. The partner starting the comparison in reverse order gets a separate Comparison, their own token, their card first. Legacy two-token Comparison URLs are deleted, not redirected — they 404. No advice exists yet: the page is cards plus the short link. Domain docs are rewritten to match: Comparison is now persisted, the Comparison Permalink is single-token, the Initiating Participant's Result Token no longer appears in the URL, and the ERD gains the comparisons table (plus the Result Page's advice column, which is already missing there).

**Blocked by:** 01: Rename ResultToken to Token

**Status:** resolved

- [x] A Comparison row is uniquely identified by its ordered pair; the pair (initiator, partner) can only exist once, and the reverse order is a distinct Comparison.
- [x] POST with a valid partner Result Token → 302 to `/compare/{token}` where token is 16-character lowercase alphanumeric; the Comparison row exists for that ordered pair.
- [x] A pasted Result Permalink (trailing slash and query string included) is accepted exactly like a bare Result Token.
- [x] Resubmitting the same ordered pair redirects to the same Comparison Token; no second row is created.
- [x] Reverse order (partner as initiator) creates a second Comparison with a different token; each Comparison Page shows its own initiator's card first.
- [x] The Comparison Page renders both scorecards unchanged: names, Love Languages in each Participant's own rank order, scores as `x.x/5.0`, progress bars scaled to 5.0, initiator-first ordering, side-by-side desktop and stacked mobile.
- [x] The copy-link component on the Comparison Page shows the single-token Comparison Permalink; its message is unchanged.
- [x] The back link targets the initiator's Result Page.
- [x] GET `/compare/{token}` with an unknown or malformed token → 404 (route constraint rejects malformed before the controller).
- [x] Legacy `GET /compare/{tokenA}/{tokenB}` (either order) → 404, no redirect.
- [x] The compare form on the Result Page is a POST form with CSRF, still positioned between the permanent-link section and the feedback section.
- [x] Self-comparison, unknown partner, and malformed partner input keep their existing inline flash errors on the Result Page; the compare section is still reachable when visiting any Participant's Result Page.
- [x] No "Ringkasan & Saran" section renders (advice does not exist yet).
- [x] The existing Compare feature test (CompareControllerTest) covers all of the above through the two routes and is green.
- [x] Glossary rewritten: Comparison (persisted, ordered pair), Comparison Permalink (single-token URL), Comparison Token (new entry), Initiating Participant (owns the Comparison, card first; token no longer in the URL).
- [x] ERD updated with the comparisons table and the previously missing users advice column.

## Answer

The `comparisons` table (initiator/partner FKs cascade, `token` varchar(32) unique, nullable `advice`, composite unique on the ordered pair) plus the `Comparison` model. `routes/web.php` now exposes exactly two comparison routes: `POST /compare/{token}` (`compare.start`) and `GET /compare/{token}` (`compare.show`), both constrained to `^[a-z0-9]{16}$`; the legacy two-token route is deleted, so old links 404. `CompareController::start` validates the three guards as before, then `firstOrCreate`s the ordered pair with a `Token::generate()` value and 302s to the single-token permalink; `show` resolves the Comparison and eager-loads both participants' ranked Results. The Result Page form is POST + CSRF (position unchanged); the Comparison Page copy-link points at the single-token URL; no advice section renders. Glossary Comparison/Comparison Permalink/Initiating Participant rewritten plus a new Comparison Token entry; ERD gains `comparisons` and the missing `users.advice`. `CompareControllerTest` reworked to 19 tests through the two routes — full suite green (79 tests, 595 assertions).
