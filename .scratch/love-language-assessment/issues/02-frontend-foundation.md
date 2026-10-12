# 02: Front-end foundation

**What to build:** Install and configure all front-end tooling so the RawBlock design system is available to every subsequent Blade template. By the end of this ticket, `npm run build` succeeds and the app shell at `GET /` renders with correct fonts, colours, and border style.

Specifics:
- Tailwind CSS and Alpine.js installed via Vite (already scaffolded; wire them up)
- RawBlock design tokens configured in Tailwind config: colours (black, white, blue #0000FF, success #008000, warning #FFA500, error #FF0000), font families (Archivo Black, Work Sans, Space Mono), border-radius set to 0 everywhere, no box-shadow utilities
- Google Fonts (Archivo Black, Work Sans, Space Mono) loaded in the base layout
- A base Blade layout (`layouts/app.blade.php`) that all views will extend — includes `<head>` with Vite assets, a `@yield('content')` slot, and nothing else (no nav, no footer — the design system has no decorative chrome)
- The default Laravel welcome view updated to extend the new layout so the shell is visually verifiable

**Blocked by:** None (can start immediately)

**Status:** ready-for-agent

- [ ] Tailwind CSS is installed and `npm run build` produces a compiled stylesheet
- [ ] Alpine.js is available in the browser (no console errors)
- [ ] RawBlock colour palette is available as Tailwind utilities (e.g. `bg-black`, `text-error`)
- [ ] All three fonts load correctly in the browser
- [ ] `border-radius: 0` is the default for all elements (no rounding anywhere)
- [ ] `layouts/app.blade.php` exists and is the base layout
- [ ] `GET /` renders without errors using the new layout
