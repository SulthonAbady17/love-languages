# 04: First deploy to Render on external Postgres

**What to build:** `https://love-languages.onrender.com` serves the welcome page over HTTPS, backed by the external Neon database — so the app runs on a public URL without the owner's laptop, and Result Token links are stored off the host's ephemeral disk.

**Blocked by:** 01 (suite green on Postgres), 02 (Neon + Render exist), 03 (`main` pushed)

**Status:** ready-for-agent

- [ ] Subdomain availability re-checked (`love-languages`, alternates `love-language-quiz` / `love-langs` pre-approved)
- [ ] ~~Host's native PHP version checked against the composer floor of 8.3~~ **N/A:** Render no longer offers a native PHP runtime (docs list PHP as Docker-only). Ticket 07's Dockerfile fallback is now the happy path — added as `Dockerfile` + `docker/render-start.sh`.
- [ ] Render service Runtime = **Docker** (Dockerfile at repo root); build runs in-image: `npm ci` + vite → `composer install --no-dev --optimize-autoloader` → image CMD runs `php artisan migrate --force` then Apache on `$PORT`
- [ ] Production env shape applied: `pgsql` via Neon pooled host with discrete host/name/user/password and `sslmode=require`; sessions and cache on the database tables; queue driver `sync`; `APP_DEBUG=false`; log channel `stderr`; `APP_URL` set to the public HTTPS URL
- [ ] `APP_KEY` (generated locally) and `GROQ_API_KEY` pasted into Render's environment settings by the human — neither enters git
- [ ] Live URL loads the welcome page over HTTPS with no browser warnings
- [ ] Deploy logs and build output visible in the Render dashboard

## Comments

### 2026-10-16 — Dockerfile path (agent)

**Finding:** Render's native runtimes no longer include PHP ([native-runtimes](https://render.com/docs/native-runtimes.md) names PHP as a Docker-only case). The spec's "Native PHP" environment is gone; ticket 07's pre-approved `php:8.4-apache` + opcache Dockerfile is the path that ships.

**Added (agent):**
- `Dockerfile` — multi-stage: Node 22 builds Vite assets; Composer 2 installs `--no-dev`; `php:8.4-apache` (satisfies lockfile floor ≥ 8.4.1) with `pdo_pgsql` + `opcache` + rewrite; DocumentRoot `public/`; ships `statements.json` and `.agents/skills/stop-slop` (Advice agent skill).
- `docker/render-start.sh` — binds Apache to Render's `$PORT`, runs `php artisan migrate --force`, then `apache2-foreground`.
- `.dockerignore` — keeps secrets, `vendor/`, `node_modules/`, and host `bootstrap/cache` out of the image.

**Local proof:** `podman build -t love-languages:render .` succeeded; running the image with the Neon connection vars from `.env` printed `Nothing to migrate.` and `GET /` → **200**, `GET /up` → **200**.

**Human dashboard (ticket 02/04):** create Web Service from `SulthonAbady17/love-languanges`, Runtime **Docker**, branch `main`, name `love-languages`, Free plan. Env vars to paste (from local `.env` / password manager — never git):

| Key | Value |
|-----|--------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | from local `.env` (`APP_KEY=…`) |
| `APP_URL` | `https://love-languages.onrender.com` (or the actual service URL) |
| `LOG_CHANNEL` | `stderr` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | Neon pooler host (`…-pooler.ap-southeast-1.aws.neon.tech`) |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | from Neon |
| `DB_USERNAME` | from Neon |
| `DB_PASSWORD` | from Neon |
| `DB_SSLMODE` | `require` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `sync` |
| `GROQ_API_KEY` | from local `.env` |
