# 07: Fallbacks — Dockerfile PHP floor or TrustProxies

**What to build:** A version gap or a proxy HTTPS quirk cannot block the deploy: if execution hits either pre-approved condition, the app ships anyway with the documented fallback, verified at the same public HTTP seam as everything else.

**Blocked by:** 04 (both fallbacks are evaluated during/after first deploy)

**Status:** ready-for-agent

- [x] Conditional: only claimed if the host's native PHP is below the composer floor of 8.3 — **claimed:** Render has no native PHP runtime; Railway is Docker-only for this stack too. Minimal Dockerfile (`php:8.4-apache` + opcache + pdo_pgsql) added; local image build + Neon smoke (`/` and `/up` → 200) green.
- [x] Conditional: only claimed if HTTPS detection misbehaves behind the host's proxy — **claimed for Railway:** Railway always terminates TLS at the edge proxy, so `request()->isSecure()` is false without TrustProxies. Added `$middleware->trustProxies(at: '*')` in `bootstrap/app.php` plus `tests/Feature/TrustProxiesTest.php` (welcome Secure under `X-Forwarded-Proto: https`; session cookie Secure).
- [x] pint run after any PHP change
- [ ] Result Token URLs behave identically to local development after the fallback

### 2026-10-16 — Dockerfile + TrustProxies claimed (agent)

Spec assumed Render native PHP; no free card-free host in this path runs native PHP without Docker. Shipped the pre-approved minimal image (`Dockerfile`, `docker/start.sh`, `.dockerignore`, `railway.toml`).

TrustProxies claimed for Railway (not Render): Railway's edge proxy always strips TLS before the container sees the request. Without trusting proxies, secure cookies and HTTPS URL generation would be wrong on the live site. Feature tests cover the same HTTP seam. Pint run.

### 2026-10-16 — Retarget note

Host moved Render → Railway after Render demanded a card. Dockerfile and TrustProxies still apply; `docker/render-start.sh` renamed to `docker/start.sh` (uses `$PORT` from either platform).

### 2026-10-16 — AH00534 More than one MPM loaded on Railway (agent)

**Symptom:** Local build + Neon smoke fine; Railway deploy dies at start with `AH00534: apache2: Configuration error: More than one MPM loaded`. Known Railway-side issue ([station](https://station.railway.com/questions/more-than-one-mpm-loaded-error-on-php-8-9c836859)): their runtime enables `mpm_event`/`mpm_worker` alongside the image's `mpm_prefork`; Apache allows only one, and mod_php needs prefork.

**Fix:** `docker/start.sh` now forces a single MPM before `apache2-foreground`:
`a2dismod mpm_event mpm_worker || true` → remove leftover symlinks → `a2enmod mpm_prefork` → `apache2ctl -t`.
