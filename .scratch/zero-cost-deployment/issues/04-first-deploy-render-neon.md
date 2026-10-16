# 04: First deploy to Render on external Postgres

**What to build:** `https://love-languages.onrender.com` serves the welcome page over HTTPS, backed by the external Neon database — so the app runs on a public URL without the owner's laptop, and Result Token links are stored off the host's ephemeral disk.

**Blocked by:** 01 (suite green on Postgres), 02 (Neon + Render exist), 03 (`main` pushed)

**Status:** ready-for-agent

- [ ] Subdomain availability re-checked (`love-languages`, alternates `love-language-quiz` / `love-langs` pre-approved)
- [ ] Host's native PHP version checked against the composer floor of 8.3 — if below, the pre-approved Dockerfile fallback (ticket 07) fires
- [ ] Render build command runs, in order: composer install (no-dev, optimized) → npm production build → `php artisan migrate --force`
- [ ] Production env shape applied: `pgsql` via Neon pooled host with discrete host/name/user/password and `sslmode=require`; sessions and cache on the database tables; queue driver `sync`; `APP_DEBUG=false`; log channel `stderr`; `APP_URL` set to the public HTTPS URL
- [ ] `APP_KEY` (generated locally) and `GROQ_API_KEY` pasted into Render's environment settings by the human — neither enters git
- [ ] Live URL loads the welcome page over HTTPS with no browser warnings
- [ ] Deploy logs and build output visible in the Render dashboard
