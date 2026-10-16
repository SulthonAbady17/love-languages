# 02: Provision the four free accounts

**What to build:** The Project Owner has every $0, card-free service the deployment needs, all human-owned, signed up via GitHub OAuth where possible so only one new password (UptimeRobot) is required. No money, no credit card on file anywhere.

**Blocked by:** None (can start immediately — runs in parallel with ticket 01)

**Status:** ready-for-human

- [x] Public GitHub repository created — actual name `love-languanges` (typo vs spec's `love-languages`; rename is a human one-click, see comment)
- [x] Neon Free Postgres project created (1 GB, no card, $0 forever) — wired into local `.env` (`pgsql` + pooler host), verified
- [ ] ~~Render Free Web Service~~ **Retargeted:** Railway Free/Trial (no card) — Render demanded a billing card the owner cannot provide
- [ ] Railway account + project connected to `SulthonAbady17/love-languanges` (GitHub OAuth, no card)
- [ ] UptimeRobot account created
- [ ] All credentials staged for pasting into host dashboards (the agent never holds them beyond local read) — Neon + app keys staged; Railway/UptimeRobot still pending

## Comments

### 2026-10-16 — verification run (agent)

**GitHub repository — exists, already the origin remote, already pushed.** `SulthonAbady17/love-languanges` is public, default branch `main`, last push matches local `main` (`6100ede`); `git status` shows `main...origin/main` in sync. Two deviations from the spec to settle:

1. **Repo name typo:** the repository is `love-languanges` (extra "a"); the spec says `love-languages`. Cosmetic for git — the Render subdomain comes from the Render *service* name, not the repo name (so name the service `love-languages`, below) — but a one-click rename on GitHub (Settings → General → Repository name; GitHub keeps redirects for renamed repos, so the existing remote URL survives) settles it. The agent cannot rename it: no `gh` CLI and no GitHub token on this machine.
2. **Push already done:** `origin/main` is already at `6100ede`, so ticket 03's "remote added and `main` pushed" step is already satisfied — Render can connect to the repo today.

**Neon / Render / UptimeRobot — not created; human-only per spec.** No API credentials for any of the three exist on this machine (`.env` carries only the app's own keys), and the spec lists account signups as non-delegable. Handoff for the Project Owner — all $0, card-free:

1. **Neon** — neon.tech → *Sign up with GitHub* → create a project on the Free plan (1 GB, no card) → copy the pooled connection details (host ending `-pooler.neon.tech`, database name, user, password, `sslmode=require`) into a password manager. These are pasted into Render during ticket 04; the agent never sees them.
2. **Railway** (replaces Render) — railway.com → *Sign up with GitHub* → **no credit card** for the free trial (`$5` for 30 days, then Free `$1`/month) → New Project → deploy from GitHub → connect `SulthonAbady17/love-languanges` → Railway uses the repo's `Dockerfile` + `railway.toml` automatically → set service name `love-languages` if offered (URL becomes `https://love-languages.up.railway.app`). Create the project/service only; env vars and first deploy follow ticket 04. **Do not** add Railway Postgres — keep Neon. **Do not** enter a card.
3. **UptimeRobot** — uptimerobot.com → sign up with email (the one new password). The monitor itself is ticket 06.

**Credentials staging:** `APP_KEY` and `GROQ_API_KEY` are already in the local untracked `.env` (verified untracked and absent from history in ticket 01) — the human copies them from there into Render's Environment tab in ticket 04. Neon credentials do not exist yet; they appear when the Neon project is created.

### 2026-10-16 — Neon created and wired (human + agent verify)

Project Owner created the Neon Free project and connected it. Agent verified locally (password not printed):

- `.env`: `DB_CONNECTION=pgsql`, host `ep-ancient-bread-a1qmfx69-pooler.ap-southeast-1.aws.neon.tech`, `sslmode=require`
- `php artisan db:monitor` → `pgsql .. [21] OK`
- `DB::select('select 1')` → OK
- `php artisan migrate:status` → all 8 migrations **Ran**

Still human-only: Railway web service, UptimeRobot. Ticket stays `ready-for-human` until both exist.

### 2026-10-16 — Render card wall → Railway retarget (human + agent)

Human could not provide a card; Render's signup demanded billing. Agent researched free card-free options (Sep/Oct 2026): Railway free trial `$5`/30 days with no card, then Free `$1`/month, supports this repo's Dockerfile + external Neon. Project retargeted to Railway (ticket 04 has dashboard steps).
