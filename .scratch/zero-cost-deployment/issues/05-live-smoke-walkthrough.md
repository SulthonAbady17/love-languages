# 05: Live smoke walkthrough

**What to build:** A Friend can take the full Assessment on the public site exactly as designed, get AI Advice once, and trust their Result Token link — verified end-to-end on the live URL, including persistence across a redeploy.

**Blocked by:** 04 (the live URL exists)

**Status:** done

- [x] Full flow on the live URL: name form → 25 Statements on the 1–5 Likert scale → submit → Advice generated exactly once in Bahasa Indonesia → Result Page renders with ranked Results *(four live submits; advice was English on 1 of the first 2 — see 2026-10-17 comment, prompt hardened and 2 of 2 post-fix live submits in Bahasa)*
- [x] Result Token link re-loaded after a subsequent deploy still resolves (proves persistence against Neon, not ephemeral disk)
- [x] No account, password, or email required at any step
- [x] HTTPS and cookie behaviour verified behind Railway's reverse proxy — TrustProxies live: `secure; httponly; samesite=lax` on session cookies; absolute form action uses `https://love-languages.up.railway.app`
- [x] Site works on a phone inside a chat app's in-app browser *(confirmed by the owner on 2026-10-17)*
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

Four complete submits against `https://love-languages.up.railway.app`, all with live `GROQ_API_KEY`:

| Check | Result |
|-------|--------|
| `POST /assessment/start` (CSRF + name) | 302 → `/assessment`, session cookie set |
| `GET /assessment` | 200, all 25 Statements across 5 groups of 5 |
| `POST /assessment` (25 answers) | 302 → `/results/<uuid>` |
| Result Page | 200, `Bahasa Cinta Utama` + `Peringkat Bahasa Cinta` with all 5 ranked (skewed run: 5.0 top vs 1.0) |
| Advice block | rendered once, on first Result Page load |
| Cold-jar `GET /results/<token>` | 200, no session, advice + rankings intact |
| Share link | absolute `https://love-languages.up.railway.app/results/<token>` |

**Finding — advice language was flaky.** Submit 1 returned **English**; submit 2 returned **Bahasa Indonesia**. The agent prompt said "Bahasa Indonesia" but nothing forbade English, so `gpt-oss-120b` honoured it only sometimes. Per the owner's decision this was fixed inside ticket 05 rather than deferred: `AdviceAgent::instructions()` gained a final directive — *the reply must be entirely in Bahasa Indonesia, never English*. No unit test asserts prompt prose (the ai-advice spec puts that out of scope); the walkthrough below is the evidence.

**Result Token persistence across a redeploy.** The commit (`c2b911e`) was pushed to `main`, Railway reported deployment `success` at 15:27:28, and the token minted **before** that deploy — `35f2f577-46c6-4ad4-9c84-bb19f87fb6bd` — still returned 200 with its original advice, unchanged, after the redeploy. The stored advice is therefore generated exactly once and read back from Neon, never regenerated and never lost to the host's ephemeral disk.

**Post-fix submits:** `ffa34268-94ad-4401-b406-c013e0bba8df` and `1ed48f85-9021-42f5-ba12-9e07d2b5bc53` — **2 of 2 live, both advice entirely in Bahasa Indonesia** (plus 3 of 3 local runs against the same key). Because the output language is model behaviour, not a guarantee the app can assert, the prompt-line fix ships with no unit test: the ai-advice spec puts tests on prompt prose out of scope, and the live walkthrough above is the evidence.

### 2026-10-17 — Phone in-app browser (owner)

Owner completed the flow on a phone inside a chat app's in-app browser — "works fine". Last checkbox of this ticket; nothing left open.

Ticket 05 is complete.
