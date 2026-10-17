# 05: Live smoke walkthrough

**What to build:** A Friend can take the full Assessment on the public site exactly as designed, get AI Advice once, and trust their Result Token link — verified end-to-end on the live URL, including persistence across a redeploy.

**Blocked by:** 04 (the live URL exists)

**Status:** ready-for-human

- [x] Full flow on the live URL: name form → 25 Statements on the 1–5 Likert scale → submit → Advice generated exactly once in Bahasa Indonesia → Result Page renders with ranked Results *(three live submits; advice was English on 1 of the first 2 — see 2026-10-17 comment, prompt hardened and re-verified)*
- [x] Result Token link re-loaded after a subsequent deploy still resolves (proves persistence against Neon, not ephemeral disk)
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

### 2026-10-17 — Full live walkthrough (agent)

Three complete submits against `https://love-languages.up.railway.app`, all with live `GROQ_API_KEY`:

| Check | Result |
|-------|--------|
| `POST /assessment/start` (CSRF + name) | 302 → `/assessment`, session cookie set |
| `GET /assessment` | 200, all 25 Statements across 5 groups of 5 |
| `POST /assessment` (25 answers) | 302 → `/results/<uuid>` |
| Result Page | 200, `Bahasa Cinta Utama` + `Peringkat Bahasa Cinta` with all 5 ranked (skewed run: 5.0 top vs 1.0) |
| Advice block | rendered once, on first Result Page load |
| Cold-jar `GET /results/<token>` | 200, no session, advice + rankings intact |
| Share link | absolute `https://love-languages.up.railway.app/results/<token>` |

**Finding — advice language was flaky.** Submit 1 returned **English**; submit 2 returned **Bahasa Indonesia**. The agent prompt said "Bahasa Indonesia" but nothing forbade English, so `gpt-oss-120b` honoured it only sometimes. Per the owner's decision this was fixed inside ticket 05 rather than deferred: `AdviceAgent::instructions()` gained a final directive — *the reply must be entirely in Bahasa Indonesia, never English* — covered by `tests/Unit/AdviceAgentSkillTest.php`. Local re-check: 3/3 runs in Bahasa.

**Result Token persistence across a redeploy.** The commit (`c2b911e`) was pushed to `main`, Railway reported deployment `success` at 15:27:28, and the token minted **before** that deploy — `35f2f577-46c6-4ad4-9c84-bb19f87fb6bd` — still returned 200 with its original advice, unchanged, after the redeploy. The stored advice is therefore generated exactly once and read back from Neon, never regenerated and never lost to the host's ephemeral disk.

**Post-fix submit** (`ffa34268-94ad-4401-b406-c013e0bba8df`): advice entirely in Bahasa Indonesia.

Still open: phone inside a chat app's in-app browser *(human)*.
