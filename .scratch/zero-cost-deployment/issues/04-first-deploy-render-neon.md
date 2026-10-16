# 04: First deploy to Railway on external Postgres

**What to build:** `https://love-languages.up.railway.app` serves the welcome page over HTTPS, backed by the external Neon database — so the app runs on a public URL without the owner's laptop, and Result Token links are stored off the host's ephemeral disk.

**Blocked by:** 01 (suite green on Postgres), 02 (Neon + Railway exist), 03 (`main` pushed)

**Status:** done

**Host retarget:** Render Free demanded a credit card the owner cannot provide (2026-10-16). Primary host is now **Railway** (free trial `$5`/30 days, no card; then Free `$1`/month). Same Dockerfile, same Neon database. Railway injects `$PORT` (handled by `docker/start.sh`).

- [x] Subdomain is Railway-provided: `https://love-languages.up.railway.app`
- [x] Railway service built from the repo `Dockerfile` + `railway.toml` (builder DOCKERFILE, healthcheck `/up`)
- [x] **No card** on the Railway account; **no** Railway-managed Postgres (Neon stays the database)
- [x] Production env shape applied on the Railway Variables tab (session/cache on database, `SESSION_SECURE_COOKIE=true`, HTTPS cookies observed live)
- [x] `APP_KEY` and `GROQ_API_KEY` pasted into Railway Variables by the human — neither in git
- [x] Live URL loads the welcome page over HTTPS; session cookies `secure; httponly; samesite=lax`; wildcard `*.up.railway.app` cert valid to 2026-12-26
- [x] Deploy logs and build output visible in the Railway dashboard

## Comments

### 2026-10-16 — Dockerfile path (agent)

**Finding:** Render's native runtimes no longer include PHP. Ticket 07's pre-approved `php:8.4-apache` + opcache Dockerfile ships.

**Added (agent):**
- `Dockerfile` — multi-stage: Node 22 builds Vite assets; Composer 2 installs `--no-dev`; `php:8.4-apache` (satisfies lockfile floor ≥ 8.4.1) with `pdo_pgsql` + `opcache` + rewrite; DocumentRoot `public/`; ships `statements.json` and `.agents/skills/stop-slop` (Advice agent skill).
- `docker/start.sh` — binds Apache to the platform's `$PORT`, runs `php artisan migrate --force`, then `apache2-foreground` (Railway and Render both inject `$PORT`).
- `.dockerignore` — keeps secrets, `vendor/`, `node_modules/`, and host `bootstrap/cache` out of the image.

**Local proof:** `podman build` succeeded; running the image with the Neon connection vars from `.env` printed `Nothing to migrate.` and `GET /` → **200**, `GET /up` → **200**.

### 2026-10-16 — Railway retarget + TrustProxies (agent)

**Why Railway:** Render signup required a card. Railway free trial needs none; same Dockerfile works; Neon stays external.

**Added (agent):**
- `railway.toml` — DOCKERFILE builder, healthcheck `/up`, restart on failure.
- Renamed `docker/render-start.sh` → `docker/start.sh` (host-agnostic).
- `bootstrap/app.php`: `$middleware->trustProxies(at: '*')` — Railway terminates TLS at the proxy; without this `request()->isSecure()` is false and session cookies are not Secure. Pre-approved by ticket 07.
- `tests/Feature/TrustProxiesTest.php` — welcome is secure under `X-Forwarded-Proto: https`; session cookie is Secure.

**Human dashboard (ticket 02/04):** Railway → New Project → deploy `SulthonAbady17/love-languanges` (Dockerfile auto-detected). Variables tab — paste from local `.env` / Neon / password manager (never git):

| Key | Value |
|-----|--------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | from local `.env` (`APP_KEY=…`) |
| `APP_URL` | `https://love-languages.up.railway.app` (or the actual service URL) |
| `LOG_CHANNEL` | `stderr` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | Neon pooler host (`…-pooler.ap-southeast-1.aws.neon.tech`) |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | from Neon |
| `DB_USERNAME` | from Neon |
| `DB_PASSWORD` | from Neon |
| `DB_SSLMODE` | `require` |
| `SESSION_DRIVER` | `database` |
| `SESSION_SECURE_COOKIE` | `true` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `sync` |
| `GROQ_API_KEY` | from local `.env` |

Then Deploy. On the service Settings, enable **sleep / scale to zero when idle** so the free credit lasts.

### 2026-10-16 — Live verified (agent)

- `GET /` → 200, welcome in Bahasa Indonesia, Vite assets on the Railway URL
- `GET /up` → 200
- `POST /assessment/start` with CSRF + name → 302 to `/assessment`, statements render (181 KB)
- Cookies: `secure; httponly; samesite=lax` — TrustProxies working behind Railway
- TLS: `CN=*.up.railway.app`, valid until 2026-12-26

Ticket closed. Full Friend walkthrough + redeploy persistence is ticket 05; UptimeRobot is ticket 06.
