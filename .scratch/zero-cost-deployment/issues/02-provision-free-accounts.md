# 02: Provision the four free accounts

**What to build:** The Project Owner has every $0, card-free service the deployment needs, all human-owned, signed up via GitHub OAuth where possible so only one new password (UptimeRobot) is required. No money, no credit card on file anywhere.

**Blocked by:** None (can start immediately — runs in parallel with ticket 01)

**Status:** ready-for-human

- [x] Public GitHub repository created — actual name `love-languanges` (typo vs spec's `love-languages`; rename is a human one-click, see comment)
- [ ] Neon Free Postgres project created (1 GB, no card, $0 forever)
- [ ] Render Free Web Service created (native PHP, Free plan, 512 MB RAM)
- [ ] UptimeRobot account created
- [ ] All credentials staged for pasting into host dashboards (the agent never holds them beyond local read)

## Comments

### 2026-10-16 — verification run (agent)

**GitHub repository — exists, already the origin remote, already pushed.** `SulthonAbady17/love-languanges` is public, default branch `main`, last push matches local `main` (`6100ede`); `git status` shows `main...origin/main` in sync. Two deviations from the spec to settle:

1. **Repo name typo:** the repository is `love-languanges` (extra "a"); the spec says `love-languages`. Cosmetic for git — the Render subdomain comes from the Render *service* name, not the repo name (so name the service `love-languages`, below) — but a one-click rename on GitHub (Settings → General → Repository name; GitHub keeps redirects for renamed repos, so the existing remote URL survives) settles it. The agent cannot rename it: no `gh` CLI and no GitHub token on this machine.
2. **Push already done:** `origin/main` is already at `6100ede`, so ticket 03's "remote added and `main` pushed" step is already satisfied — Render can connect to the repo today.

**Neon / Render / UptimeRobot — not created; human-only per spec.** No API credentials for any of the three exist on this machine (`.env` carries only the app's own keys), and the spec lists account signups as non-delegable. Handoff for the Project Owner — all $0, card-free:

1. **Neon** — neon.tech → *Sign up with GitHub* → create a project on the Free plan (1 GB, no card) → copy the pooled connection details (host ending `-pooler.neon.tech`, database name, user, password, `sslmode=require`) into a password manager. These are pasted into Render during ticket 04; the agent never sees them.
2. **Render** — render.com → *Sign up with GitHub* → New → Web Service → connect `SulthonAbady17/love-languanges` → name the service `love-languages` (this mints the spec's `https://love-languages.onrender.com`; alternates `love-language-quiz` / `love-langs` if taken) → Environment: Native PHP, Plan: Free (512 MB). Create the service only; env vars, build command and the first deploy are ticket 04.
3. **UptimeRobot** — uptimerobot.com → sign up with email (the one new password). The monitor itself is ticket 06.

**Credentials staging:** `APP_KEY` and `GROQ_API_KEY` are already in the local untracked `.env` (verified untracked and absent from history in ticket 01) — the human copies them from there into Render's Environment tab in ticket 04. Neon credentials do not exist yet; they appear when the Neon project is created.
