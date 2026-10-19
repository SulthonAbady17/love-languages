# 01: Design tokens, fonts & app shell

**What to build:** Replace all RawBlock design tokens with the Relationship Project system and wire the app shell. After this ticket, every page loads with the cream background, correct Google Fonts, named Tailwind utility classes (`bg-navy`, `text-cream`, `border-line`, etc.), the correct radius scale, and a branded nav bar carrying the two-connected-dots SVG logo mark and wordmark. `DESIGN.md` is the new source of truth for the system.

**Blocked by:** None (can start immediately)

**Status:** ready-for-agent

- [ ] `DESIGN.md` is fully rewritten to document the Relationship Project design system: colour tokens, typography scale, radius scale, component rules, do/don't list. RawBlock content is gone.
- [ ] `resources/css/app.css` `@theme` block contains the new colour tokens (`navy`, `navy-ink`, `sage`, `cream`, `terracotta`, `line`, `muted`), font tokens (`sans`/`body` → Plus Jakarta Sans, `editorial` → Newsreader italic), and radius scale (`sm` 8px, `md` 12px, `lg` 16px). All RawBlock tokens (hard black/white/blue/etc., Archivo Black, Space Mono, Work Sans, zero-radius overrides) are removed.
- [ ] The `base` layer retains `[x-cloak]` and the `radio-circle` circular override; all other RawBlock base resets are removed. Body background defaults to cream, text to navy.
- [ ] `resources/views/layouts/app.blade.php` loads Plus Jakarta Sans (400, 500, 600) and Newsreader (italic 400, italic 500) from Google Fonts; the old font links are removed.
- [ ] The layout includes a top nav bar with the two-connected-dots SVG mark and the "relationship project." wordmark (terracotta period). The body has top-padding to offset the fixed/sticky nav.
- [ ] Visiting any page of the app in a browser shows: cream background, Plus Jakarta Sans body text, the branded nav bar — no unstyled content, no console errors from missing font families.
