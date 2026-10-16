# 07: Fallbacks — Dockerfile PHP floor or TrustProxies

**What to build:** A version gap or a proxy HTTPS quirk cannot block the deploy: if execution hits either pre-approved condition, the app ships anyway with the documented fallback, verified at the same public HTTP seam as everything else.

**Blocked by:** 04 (both fallbacks are evaluated during/after first deploy; the TrustProxies leg is further gated by the HTTPS finding in 05)

**Status:** ready-for-agent

- [x] Conditional: only claimed if the host's native PHP is below the composer floor of 8.3 — **claimed early:** Render has no native PHP runtime at all (Docker-only). Minimal Dockerfile (`php:8.4-apache` + opcache + pdo_pgsql) added; local image build + Neon smoke (`/` and `/up` → 200) green. Live Render deploy still human dashboard (ticket 04).
- [ ] Conditional: only claimed if HTTPS detection misbehaves behind Render's proxy — TrustProxies middleware added, with feature-test assertions that the welcome route redirects to HTTPS and the session cookie is secure
- [x] pint run after any PHP change — no app PHP changed (Docker/shell only)
- [ ] Result Token URLs behave identically to local development after the fallback

### 2026-10-16 — Dockerfile fallback claimed (agent)

Spec assumed "Native PHP" on Render; current Render docs exclude PHP from native runtimes. Shipped the pre-approved minimal image instead (`Dockerfile`, `docker/render-start.sh`, `.dockerignore`). TrustProxies not added yet — Laravel trusts proxies by default; only add if live HTTPS detection fails (ticket 05).
