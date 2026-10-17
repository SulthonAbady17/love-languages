# 06: Keep-alive monitor

**What to build:** Friends never wait out a long cold start — a free monitor pings the live app every ten minutes, inside the free host's budget (Railway credit / Render hours — whichever is live).

**Blocked by:** 04 (the live URL exists)

**Status:** done

- [x] UptimeRobot HTTPS monitor configured against the live URL (`https://love-languages.up.railway.app` after the Railway retarget), interval 10 minutes
- [x] Free-tier budget checked: keep-alive pings must stay inside Railway's trial/Free credit (or Render hours if ever retargeted back) — enable **scale-to-zero on idle** if credit burns too fast
- [x] Monitor shown green in the UptimeRobot dashboard

## Comments

### 2026-10-17 — Monitor verified via UptimeRobot API (agent)

The Project Owner created the monitor by hand on 2026-10-16 (before this ticket was picked up) and supplied the account API key so the agent could verify rather than take it on faith. Read-only API checks (`getMonitors`, key held in-session only, never written to any file):

| Field | Value |
|-------|-------|
| Monitor ID | `804305160` |
| Friendly name | `love-languages.up.railway.app` |
| URL / type | `https://love-languages.up.railway.app` / type 1 (HTTPS) |
| Interval | **600 s = 10 minutes** — exactly the ticket's spec |
| Created | 2026-10-16 19:21 UTC |
| Status | **2 = UP** (green) |

Live URL re-checked from this machine in the same pass: `GET /` → 200 (0.27 s), `GET /up` → 200. The dashboard renders the same server-side `status` field the API returns, so "green in the dashboard" (checkbox 3) and the API reading above are the same fact.

**Spec correction (researched, 2026-10-17):** UptimeRobot's free plan is documented as "every 5 minutes" — that is the *floor*, not the only option. A 600 s interval sits above it and was accepted, so the ticket's 10-minute cadence is real and free (50 monitors, $0, no card).

### 2026-10-17 — Free-tier budget check (agent)

**Railway bills actual per-minute consumption, not configured allocation** (docs.railway.com/pricing/understanding-your-bill + guides/right-size-cpu-memory): RAM $10/GB/mo, CPU $20/vCPU/mo, egress $0.05/GB; stopped/sleeping services cost nothing.

**Observed (owner, Railway → Workspace Settings → Usage, 2026-10-17, day 1 of trial):** usage **$0.01**, trial credit remaining **$5.00**.

**Ceiling estimate, always-on under keep-alive** (Apache + PHP-FPM idle baseline ~0.15 GB, CPU near-zero between pings, friends-only egress):

```
monthly ≈ (0.15 GB × $10) + (0.03 vCPU × $20) + (<1 GB egress × $0.05) ≈ $2.15
```

| Phase | Credit | Always-on estimate | Verdict |
|-------|--------|--------------------|---------|
| Trial (now, 30 days) | $5 one-time | ≈ $1.80–2.55/mo | **Inside budget** — ≈50% of credit, no action |
| Free (after trial) | $1/mo, non-rollover | ≈ $1.80–2.55/mo | **~2× over** — mitigation below |

**Verdict:** keep-alive as configured fits the trial credit with headroom; the observed burn ($0.01 on day 1) is well under the $0.167/day the trial allows. The ticket's conditional ("enable scale-to-zero if credit burns too fast") does **not** fire during the trial.

**Re-check (cheap honesty):** the observed window is one partial day. Read Usage again around 2026-10-24 (week 1); if sustained burn exceeds $0.167/day, flip the service to serverless early (service Settings → Serverless) instead of waiting for the trial end.

**Pre-approved fallback, for the trial→Free transition (recorded now so nobody re-decides it):**

1. Railway's Free plan runs services with **App Sleeping (serverless)** — scale-to-zero after **10 min idle**, waking on the next request. That is exactly the ticket's "scale-to-zero on idle", and it is **the Free plan's default state, nothing to enable**, so post-trial the budget resolves itself to ≈$0 while asleep.
2. Head-wind to know about: the UptimeRobot ping (600 s) and the sleep threshold (10 min) are the *same cadence*, so post-trial the monitor may wake the app around every sleep, and keep-alive and scale-to-zero trade against each other on the Free plan. Accept sleeping (cold starts return) rather than fighting it; friends-only traffic is the tolerance, not always-on.
3. **Trial end needs one click:** when the trial expires, Railway stops the services until the owner clicks **Downgrade to Free** on the workspace Plans page (it does not auto-migrate) and redeploys. Calendar reminder: on/around 2026-11-15. No card, no charge either way.

**No code changed** — this ticket is dashboard configuration plus the check above. Nothing to test at the HTTP seam beyond the `GET /` / `GET /up` re-verification already logged here.
