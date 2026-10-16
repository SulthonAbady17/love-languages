# Spec: Zero-cost public deployment

Status: ready-for-agent

## Problem Statement

The Love Languages assessment app currently runs only on the owner's local machine (lerd, MySQL). Friends cannot try it — there is no public URL, and the app dies when the laptop is closed. The owner refuses to spend any money: hosting, domain, and database must be 100% free with no credit card on file anywhere, because the purpose is just for friends to try the quiz. At the same time, the product promise in the glossary is that a Result Token link is the Participant's *permanent* link — any deployment whose storage dies on redeploy would silently break that promise.

## Solution

The app ships to a free public HTTPS URL on Render's Free Web Service at a platform-provided subdomain (`https://love-languages.onrender.com`), reading and writing a free Neon Postgres database that lives outside the host's ephemeral disk, so Result Token links stay valid forever — across redeploys, restarts, and instance sleep cycles. A public GitHub repository is the source of truth: pushing to `main` auto-deploys. A free UptimeRobot monitor pings the app every ten minutes so Friends never wait out a cold start, while staying inside Render's 750 free hours per month. AI Advice stays fully enabled via the existing Groq key, stored only in the host's environment settings. Total cost: $0, forever, no cards.

## User Stories

1. As the Project Owner, I want a public HTTPS URL for the app, so that friends can open it from a chat link without my laptop being on.
2. As the Project Owner, I want the entire stack to cost $0 with no credit card on file anywhere, so that I can never receive an unexpected charge.
3. As the Project Owner, I want the domain to be a free subdomain provided by the hosting platform, so that I never buy or renew a domain.
4. As the Project Owner, I want a public GitHub repository as the single source of truth, so that the deployed app and the codebase can never diverge silently.
5. As the Project Owner, I want Render to auto-deploy `main` on every push, so that shipping a fix is a `git push` and never a manual file upload.
6. As the Project Owner, I want production data stored in an external free database rather than the host's ephemeral disk, so that redeploys and host-side restarts never destroy Participant data.
7. As the Project Owner, I want database migrations to run automatically on every deploy, so that schema changes ship without shell access (the free plan has none).
8. As the Project Owner, I want the Groq API key stored only in the host's environment settings, so that the secret never enters git.
9. As the Project Owner, I want a free uptime monitor pinging the app every ten minutes, so that the service stays warm and Friends never see a 30–60 second wake-up.
10. As the Project Owner, I want the keep-alive to fit inside Render's free monthly hour budget, so that the service is never suspended mid-month for overuse.
11. As the Project Owner, I want deploy logs and build output visible in the host dashboard, so that I can diagnose a failed deploy without SSH.
12. As the Project Owner, I want the existing PHPUnit suite to pass against Postgres before shipping, so that engine-specific SQL cannot break production.
13. As the Project Owner, I want the app to degrade gracefully when Groq is unreachable or its quota is spent, so that Assessments still complete and the Result Page renders without Advice.
14. As the Project Owner, I want the local lerd development environment left untouched, so that day-to-day work continues exactly as before.
15. As the Project Owner, I want a pre-approved fallback if the host's native PHP runtime lags behind the application's floor, so that a version gap cannot block the deploy.
16. As the Project Owner, I want HTTPS and cookie behaviour verified behind the host's reverse proxy, so that Result Token URLs behave identically to local development.
17. As the Project Owner, I want a clear split of which deploy steps are human-only (account signups and dashboard clicks) versus agent-executable, so that we never stall mid-deploy waiting on the wrong party.
18. As a Friend, I want the shared link to load instantly over HTTPS with no browser warnings, so that I trust the page enough to enter my name.
19. As a Friend, I want the full Assessment flow on the live site exactly as designed — name form, 25 Statements on the 1–5 Likert scale, submit — so that the experience matches what the owner showed me.
20. As a Friend, I want AI Advice generated exactly once when I complete my Assessment, so that my Result Page includes my personalised guidance in Bahasa Indonesia.
21. As a Friend, I want my Result Token link to still work weeks later, after the owner has redeploys and restarts in between, so that my permanent link stays permanent.
22. As a Friend, I want my completed Assessment to survive an application restart, so that revisiting my Result Page never 404s.
23. As a Friend, I want the site to work on a phone inside a chat app's in-app browser, so that I can take the quiz immediately without installing anything.
24. As a Participant, I want no account, password, or email, so that I can finish the Assessment with zero friction on the public site just as locally.
25. As the Project Owner, I want the subdomain to be memorable and shareable, so that the link I paste into chats looks intentional rather than a random string.

## Implementation Decisions

- **Stack (all free, all card-free)**: GitHub public repository `love-languages`; Render Free Web Service (native PHP environment, Free compute plan, 512 MB RAM) serving `https://love-languages.onrender.com`; Neon Free Postgres as the production database; UptimeRobot free monitor (HTTPS, every 10 minutes) as keep-alive; Groq (existing free-tier key) for Advice.
- **Why not Render's own Postgres**: Render's free Postgres instances expire 30 days after creation, which would destroy every Result Token. Neon's free plan is $0 forever with no card, 1 GB storage, and compute that auto-suspends when idle (no billing risk). Supabase was rejected because its free projects pause after ~7 days idle; Turso was rejected because it would add a driver dependency for no benefit — the migrations here are stock and run on Postgres unchanged.
- **Production branch**: `main`, fast-forwarded from the finished AI-advisor feature branch (34 commits ahead, local `main` has nothing extra). Render watches `main` only; branches named `test-*` never deploy.
- **Production environment shape**: `pgsql` connection via discrete database host/name/user/password values from the Neon pooled host (`sslmode=require`); sessions and cache on the database tables (their migrations already ship); queue driver `sync` (the app dispatches no jobs — Advice is generated inline with a timeout, failing gracefully); `APP_DEBUG=false`; log channel `stderr`; `APP_KEY` generated locally and pasted into host env; `APP_URL` set to the public HTTPS URL; `GROQ_API_KEY` pasted from the owner's local secret.
- **Migrations at build time**: the deploy build command runs composer install (no-dev, optimized), the npm production build, and `php artisan migrate --force`, in that order. The free plan exposes no SSH shell and no one-off jobs, but the host passes service env vars into the build, and the migrate step is idempotent, so it runs safely on every deploy.
- **Runtime files from the repo**: the statements file at the repo root (the seeder's source of truth, 25 Statements across 5 Love Languages) and the advice skill directory consumed by the Advice agent are both verified tracked in git — the deployed checkout is self-sufficient.
- **Pre-approved fallbacks** (only if execution hits them): a minimal Dockerfile (`php:8.4-apache` + opcache) if the host's native PHP is below the application's floor of 8.4.1 (the floor the lockfile's platform check enforces, higher than the `^8.3` root requirement — verified in ticket 01); TrustProxies middleware if HTTPS detection misbehaves behind the host's proxy (small change, same HTTP test seam, pint after); subdomain alternates `love-language-quiz` / `love-langs` if the primary is taken.
- **No schema changes, no new packages, no code changes on the happy path.** The only certain local change is the fast-forward merge to `main` and adding the git remote.
- **Account ownership**: all four services are created and owned by the human, signed up where possible via GitHub OAuth so only one new password (UptimeRobot) is needed. Secrets are pasted by the human into host dashboards; the agent never holds them beyond local read.

## Testing Decisions

- A good test here asserts external behaviour only — HTTP status, page content, persisted rows, a Result Token link that still resolves — never host-dashboard internals or build logs as assertions.
- **Single seam, the app's public HTTP surface** (welcome → start → assessment GET/POST → result page), exercised at two heights:
  1. **Pre-ship, locally**: the existing PHPUnit feature suite run with a Postgres connection (pointed at a Neon database or local Postgres). Prior art already at this seam: `AssessmentFlowTest`, `AssessmentSubmissionTest`, `ResultPageTest`, `DatabaseSeederTest`, `DatabaseSchemaTest` — they cover the full flow, submission scoring, result rendering, the statements seeder, and schema expectations. Zero new test files on the happy path.
  2. **Post-ship, live**: a manual or curl walkthrough of the same four routes on the Render URL, including submitting an Assessment with Advice enabled and re-loading the Result Token link after a subsequent deploy to prove persistence against the external database.
- If the TrustProxies fallback fires, assertions stay at the same seam in the existing feature-test style: the welcome route redirects to HTTPS and the session cookie is secure; run pint after.
- UptimeRobot monitor state and Render deploy success are operational checks (dashboard/monitor green), not automated tests.

## Out of Scope

- Any paid tier, custom domain, or credit card on file — the zero-dollar constraint is absolute.
- Keeping data on the host's ephemeral disk or using Render's expiring free Postgres.
- User accounts, authentication, email, queues/workers, websockets, and file uploads — the app uses none of these.
- Rate limiting or bot protection for Groq quota (a public URL is guessable and Advice costs quota; a possible future follow-up, not this deploy).
- CI pipelines, preview/staging environments, infrastructure-as-code.
- Any change to app behaviour, copy, scoring, or the Advice feature itself.
- Changes to the local lerd development setup.
- Analytics, APM, or monitoring beyond the single free uptime ping.
- Database backups beyond Neon's free instant-restore window (6 hours).

## Further Notes

- **Human-only steps** (cannot be delegated): creating the GitHub repository, the Neon project, the Render web service, and the UptimeRobot monitor; pasting `APP_KEY`, database credentials, and `GROQ_API_KEY` into the Render dashboard. The human should sign up for Render and Neon via their GitHub account to minimise new credentials.
- **Agent-executable steps**: the fast-forward merge to `main`, generating `APP_KEY` locally, running the existing suite against Postgres, adding the remote and pushing, the live smoke walkthrough, and implementing either fallback (Dockerfile or TrustProxies) with tests and pint if execution requires it.
- **Execution-time re-checks**: subdomain availability; the host's native PHP version against the composer floor of 8.4.1 (lockfile platform check, ticket 01); proxy HTTPS detection on first live request; the monthly hours budget (750 free hours vs ~720 hours always-on under keep-alive) — if hours run out, the service suspends until the next month and UptimeRobot will flag it.
- The Groq key already exists in the owner's local `.env`, which is verified untracked and absent from git history; it is reused, not rotated, for this friends-only deployment.
