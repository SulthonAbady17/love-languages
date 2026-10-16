# 07: Fallbacks — Dockerfile PHP floor or TrustProxies

**What to build:** A version gap or a proxy HTTPS quirk cannot block the deploy: if execution hits either pre-approved condition, the app ships anyway with the documented fallback, verified at the same public HTTP seam as everything else.

**Blocked by:** 04 (both fallbacks are evaluated during/after first deploy; the TrustProxies leg is further gated by the HTTPS finding in 05)

**Status:** ready-for-agent

- [ ] Conditional: only claimed if the host's native PHP is below the composer floor of 8.3 — minimal Dockerfile (`php:8.4-apache` + opcache) added and Render switched to it, deploy verified green
- [ ] Conditional: only claimed if HTTPS detection misbehaves behind Render's proxy — TrustProxies middleware added, with feature-test assertions that the welcome route redirects to HTTPS and the session cookie is secure
- [ ] pint run after any PHP change
- [ ] Result Token URLs behave identically to local development after the fallback
