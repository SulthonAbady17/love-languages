# 05: Live smoke walkthrough

**What to build:** A Friend can take the full Assessment on the public site exactly as designed, get AI Advice once, and trust their Result Token link — verified end-to-end on the live URL, including persistence across a redeploy.

**Blocked by:** 04 (the live URL exists)

**Status:** ready-for-agent

- [ ] Full flow on the live URL: name form → 25 Statements on the 1–5 Likert scale → submit → Advice generated exactly once in Bahasa Indonesia → Result Page renders with ranked Results
- [ ] Result Token link re-loaded after a subsequent deploy still resolves (proves persistence against Neon, not ephemeral disk)
- [ ] No account, password, or email required at any step
- [ ] HTTPS and cookie behaviour verified behind Render's reverse proxy (identical to local development); if detection misbehaves, the pre-approved TrustProxies fallback (ticket 07) fires
- [ ] Site works on a phone inside a chat app's in-app browser
- [ ] No schema, package, or behaviour changes needed — if any were required, they are implemented with tests at the existing HTTP seam and pint run
