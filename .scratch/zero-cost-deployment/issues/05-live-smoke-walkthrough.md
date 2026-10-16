# 05: Live smoke walkthrough

**What to build:** A Friend can take the full Assessment on the public site exactly as designed, get AI Advice once, and trust their Result Token link — verified end-to-end on the live URL, including persistence across a redeploy.

**Blocked by:** 04 (the live URL exists)

**Status:** ready-for-human

- [ ] Full flow on the live URL: name form → 25 Statements on the 1–5 Likert scale → submit → Advice generated exactly once in Bahasa Indonesia → Result Page renders with ranked Results *(agent smoke: name → statements OK; Advice + Result still needs a full submit with live `GROQ_API_KEY`)*
- [ ] Result Token link re-loaded after a subsequent deploy still resolves (proves persistence against Neon, not ephemeral disk)
- [x] No account, password, or email required at any step
- [x] HTTPS and cookie behaviour verified behind Railway's reverse proxy — TrustProxies live: `secure; httponly; samesite=lax` on session cookies; absolute form action uses `https://love-languages.up.railway.app`
- [ ] Site works on a phone inside a chat app's in-app browser *(human)*
- [x] No schema, package, or behaviour changes needed for the deploy path

## Comments

### 2026-10-16 — Partial smoke (agent)

| Check | Result |
|-------|--------|
| `GET /` | 200 welcome, Bahasa Indonesia |
| `GET /up` | 200 |
| Vite asset | 200 |
| `POST /assessment/start` | 302 → `/assessment` |
| `GET /assessment` | 200, Statements render |
| Session cookies | `secure; httponly; samesite=lax` |
| TLS cert | `*.up.railway.app` → 2026-12-26 |

Still open: full 25-statement submit → Advice once → Result Token; token after redeploy; phone in-app browser.
