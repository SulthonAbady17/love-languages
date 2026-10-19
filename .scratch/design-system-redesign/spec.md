# Spec: Relationship Project Design System Redesign

## Problem Statement

The app currently uses **RawBlock** — a brutalist design system built for experimental portfolios. Its black borders, zero rounding, and high-contrast inversions are misaligned with the product's identity: a warm, premium, intentional relationship lifestyle brand for Indonesian couples. A brand guide (`relationship-project-brand-guide.html`) has been produced and defines the correct design language. `DESIGN.md` currently documents RawBlock, not the actual brand.

## Solution

Extract the design system from the brand guide, document it in `DESIGN.md` as the single source of truth, then implement it across the three Blade views and the Tailwind CSS theme — replacing RawBlock entirely. All three pages (Welcome, Assessment, Result) are restyled to use the Relationship Project palette, typography, spacing, and component language using a **mobile-first approach**: base styles target small screens; `sm:` / `md:` / `lg:` prefixes layer up from there. No application logic, routes, or data models change.

## User Stories

1. As a Participant, I want the Welcome page to feel warm and premium, so that I trust the brand before entering my name.
2. As a Participant, I want the name input and submit button to feel intentional and calm, not harsh and monochrome, so that the first impression matches the brand's promise.
3. As a Participant, I want the Assessment page's progress to be visualised with a 5-dot step indicator, so that I can see at a glance how far through the Assessment I am.
4. As a Participant, I want each Assessment step header to feel branded, so that the category name (Love Language) reads as an editorial label rather than a form heading.
5. As a Participant, I want Statement cards to use soft borders and rounded corners, so that the form feels considered rather than mechanical.
6. As a Participant, I want the rating options (Sangat Tidak Setuju → Sangat Setuju) to show a clear, brand-consistent selected state (Navy fill, cream text), so that my current choice is always obvious.
7. As a Participant, I want navigation buttons (Kembali / Lanjut / Kirim) to carry the brand's primary colour (Navy), so that CTAs feel purposeful.
8. As a Participant, I want the Result page hero block to use Navy with cream text, so that the top Love Language reads as a distinguished, premium reveal.
9. As a Participant, I want the top Love Language name rendered in Newsreader italic, so that it reads as an editorial moment rather than a UI label.
10. As a Participant, I want the ranked Love Language list to use brand-consistent card styling, so that scores feel legible and calm rather than clinical.
11. As a Participant, I want the permanent-link aside and Feedback section to use consistent brand surface colours, so that the page reads as a unified whole.
12. As a Participant, I want the in-app logo mark (two connected dots + wordmark) in the page header/nav, so that I can identify the Relationship Project brand throughout my session.
13. As a Participant, I want the page background to be warm off-white (cream), so that the overall tone feels human and considered rather than stark.
14. As a developer, I want `DESIGN.md` to describe the Relationship Project design system in full, so that future contributors know the canonical tokens, components, and do/don't rules.
15. As a developer, I want all Tailwind theme tokens (colours, fonts, radius) defined in `app.css` using Tailwind v4's `@theme` block, so that the design system is enforced at the utility class level.
16. As a developer, I want the existing Feature tests to pass after the redesign, so that the refactor doesn't silently break user-facing behaviour.
17. As a Participant on a mobile phone, I want the Welcome page to be fully usable one-handed, so that I can start the Assessment from my phone without zooming or sideways scrolling.
18. As a Participant on a mobile phone, I want the name input and submit button to span the full screen width, so that touch targets are comfortable to hit.
19. As a Participant on a mobile phone, I want the Assessment step header and Love Language name to scale down gracefully, so that the heading doesn't overflow or feel cramped.
20. As a Participant on a mobile phone, I want Statement rating options to stack into a readable vertical list (not a cramped 5-column row), so that I can read each label and tap accurately.
21. As a Participant on a mobile phone, I want the Back / Next / Submit navigation buttons to be large enough to tap without precision, so that I don't accidentally skip or submit.
22. As a Participant on a mobile phone, I want the 5-dot step indicator to be clearly visible and appropriately sized for touch, so that I can track my progress at a glance.
23. As a Participant on a mobile phone, I want the Result page hero block, ranked list, and permanent-link aside to stack cleanly in a single column, so that I can read my results without horizontal scrolling.
24. As a Participant on a tablet or desktop, I want rating options to expand into a 5-column row, so that the full scale is legible at wider widths.
25. As a Participant on a tablet or desktop, I want the page content to be constrained to a readable max-width, so that lines of text don't stretch uncomfortably wide.

## Implementation Decisions

### Files changed

- `DESIGN.md` — fully rewritten to document the Relationship Project design system (replaces RawBlock content).
- `resources/css/app.css` — `@theme` block replaced with new tokens; base layer updated.
- `resources/views/layouts/app.blade.php` — Google Fonts link updated; body class updated.
- `resources/views/welcome.blade.php` — restyled to new system.
- `resources/views/assessment.blade.php` — restyled; 5-dot step indicator added.
- `resources/views/results.blade.php` — restyled; hero block changed from black to Navy; Love Language name rendered in Newsreader italic.
- `tests/Feature/ResultPageTest.php` — Tailwind class assertions updated to match new tokens (see Testing Decisions).

### Design tokens (`@theme`)

Colours (replacing all RawBlock colour vars):

| Token name | Hex | Role |
|---|---|---|
| `--color-navy` | `#1E293B` | Primary — text, wordmark, hero fills, buttons |
| `--color-navy-ink` | `#151E2B` | Deep navy for small labels |
| `--color-sage` | `#A8C3A0` | Secondary — logo mark, step indicator current dot |
| `--color-cream` | `#FAF9F6` | Page background, inverse text on navy |
| `--color-terracotta` | `#C97C5D` | Accent — eyebrow labels, logo period |
| `--color-line` | `#E4E1D8` | Borders, dividers |
| `--color-muted` | `#6B7280` | Secondary body text |

Semantic aliases kept for error/success states (unchanged, used only for validation UI):
- `--color-error: #FF0000`
- `--color-success: #008000`

Fonts:

| Token | Value |
|---|---|
| `--font-sans` | `'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif` |
| `--font-body` | same as `--font-sans` |
| `--font-editorial` | `'Newsreader', Georgia, serif` |
| `--font-mono` | removed from active design; kept for any code or URL display |

Border radius scale (replaces hard `0px` override):

| Token | Value | Usage |
|---|---|---|
| `--radius-sm` | `8px` | Buttons, inputs, small chips |
| `--radius-md` | `12px` | Small frames |
| `--radius-lg` | `16px` | Main cards, statement fieldsets |

Shadows: removed entirely — no shadow tokens defined. Visual hierarchy via border colour and weight only (line border `#E4E1D8`, 1px).

### Typography scale

| Scale | Font | Size | Weight | Notes |
|---|---|---|---|---|
| Display | Plus Jakarta Sans | 64px | 500 | Letter-spacing −0.02em, line-height 1.05 |
| Heading | Plus Jakarta Sans | 34px | 500 | Letter-spacing −0.01em |
| Subheading | Plus Jakarta Sans | 26px | 500 | |
| Body | Plus Jakarta Sans | 16–17px | 400 | Line-height 1.65 |
| Eyebrow / UI label | Plus Jakarta Sans | 12px | 500 | Uppercase, 0.14–0.16em tracking, terracotta |
| Caption | Plus Jakarta Sans | 13px | 400 | Muted colour |
| Editorial accent | Newsreader italic | 26px+ | 400–500 italic | Results page top Love Language name **only** |

### Component decisions

**Buttons:**
- Primary: Navy fill (`bg-navy`), cream text, `rounded-sm`, 1px line border. Hover: slight opacity reduction (no full inversion).
- Secondary: white fill, navy text, 1px line border, `rounded-sm`. Hover: navy fill, cream text.
- Disabled: muted border, muted text, `cursor-not-allowed`.

**Cards / fieldsets:**
- Background: white (`#FFFFFF`).
- Border: 1px `#E4E1D8` (line), `rounded-lg`.
- Padding: 24–28px.

**Statement fieldset:**
- Retains `<fieldset>` / `<legend>` for accessibility.
- Statement number badge: Navy fill, cream text, `rounded-sm`, small.
- Rating options: 5-column grid (sm and above). Selected: Navy fill, cream text. Unselected: white fill, navy text, line border. Border between option bar and statement: 1px `line` colour.

**Step indicator (Assessment page):**
- 5 dots rendered via Alpine `x-data` reading existing `currentStep`.
- Completed step dots: Navy fill.
- Current step dot: Sage fill.
- Upcoming step dots: transparent, line border.
- Sits immediately below the "Langkah X dari 5" text label (which is kept).
- No new Alpine component — reads `currentStep` from the parent `x-data` scope.

**Result page hero:**
- `<section id="top-love-language">`: `bg-navy text-cream rounded-lg`.
- Love Language name: Newsreader italic, large (display scale).
- Eyebrow ("Bahasa Cinta Utama"): terracotta, uppercase, tracking.

**Permanent link aside:**
- Surface: cream background, line border, `rounded-lg`.
- Copy button: Navy fill, cream icon.

**Feedback section:**
- Same card treatment as all other sections (white card, line border).
- Submit button: uses secondary button style.

### Logo mark

An inline SVG (two dots + connecting curve, matching Concept 03 from the brand guide) placed in the page header, followed by the wordmark "relationship project" in Plus Jakarta Sans with a terracotta period. Rendered in `layouts/app.blade.php` as a top-of-page `<header>` nav bar.

### Google Fonts

Remove: `Archivo Black`, `Space Mono`, `Work Sans`.
Add: `Plus Jakarta Sans` (weights 400, 500, 600) and `Newsreader` (italic 400, italic 500).

### Responsive layout — mobile-first

All base styles are written for mobile (≥320px). Breakpoints used:

| Breakpoint | Tailwind prefix | When |
|---|---|---|
| `sm` | `sm:` | ≥640px — tablet portrait and up |
| `md` | `md:` | ≥768px — tablet landscape and up |
| `lg` | `lg:` | ≥1024px — desktop |

**Page shell (all views):**
- Full-bleed on mobile (`px-4 py-8`), centred max-width container (`max-w-3xl mx-auto`) on `sm:`.
- Single-column layout throughout; no grid used at the page level.

**Header / nav:**
- Logo mark (SVG) + wordmark stack horizontally, always. No hamburger menu needed (no nav links to collapse).
- Fixed top bar on all screen sizes; body receives top-padding to offset.

**Welcome page:**
- Name input: full-width (`w-full`) on all sizes.
- Submit button: full-width on mobile (`w-full`), auto-width from `sm:` (`sm:w-auto`).
- Hero title: `text-3xl` mobile → `text-5xl md:text-[64px]` desktop.

**Assessment page:**
- Step header title: `text-2xl` mobile → `text-4xl md:text-5xl` desktop.
- Step indicator dots: 10px diameter on all sizes, horizontally centred, touch-safe spacing (`gap-3`).
- Description callout card: full-width, stacks above statements.
- Statement fieldsets: full-width, `rounded-lg`, comfortable padding (`p-4 sm:p-6`).
- Rating options: `grid-cols-1` (stacked, full-width) on mobile → `sm:grid-cols-5` (horizontal scale) on tablet and up. Each label has a minimum touch target height of 44px (`min-h-[44px]`).
- Navigation buttons: minimum touch target height 44px. Full-width on mobile, auto-width from `sm:`. Nav row: `flex-col gap-3` on mobile, `sm:flex-row sm:justify-between` on tablet and up.

**Result page:**
- Hero block: full-bleed inside the container, generous padding (`p-6 sm:p-10`). Title text: `text-3xl sm:text-5xl md:text-[56px]`.
- Ranked list articles: single column, full-width on all sizes.
- Permanent-link aside: URL text truncates (`truncate`) on mobile; copy button always visible.
- Feedback section: full-width textarea, full-width button on mobile.

### No logic changes

Routes, controllers, models, migrations, seeders, and all Alpine.js step/form logic are untouched. Alpine.js is already installed; no new dependency is added.

## Testing Decisions

### What makes a good test here

Tests should assert **user-visible content and structure** — that the right text appears, that elements with the right semantic IDs exist, and that the correct Tailwind utility classes are present for any styling the product relies on (e.g. the Navy hero block, the Advice block border). Tests must not over-specify every Tailwind class; they pin only the ones that encode a product decision (colour, role, accessibility).

### Tests that need updating

`ResultPageTest::test_top_scoring_love_language_is_shown_in_an_inverted_black_block`:
- Currently asserts `bg-black` and `text-white` on `#top-love-language`.
- Must be updated to assert `bg-navy` and `text-cream` (the new token class names).
- The test method name should be updated to reflect "Navy block" rather than "inverted black block".

`ResultPageTest::test_stored_advice_renders_a_bordered_block_directly_under_the_top_love_language`:
- Currently asserts `border-[3px] border-black` on `#advice`.
- Must be updated to assert the new border class (`border border-line` or equivalent Tailwind v4 token class).

All other existing Feature tests assert text content and routes — these are unaffected by the redesign.

### No new tests required

The redesign introduces no new application behaviour. The 5-dot step indicator is purely client-side Alpine; its correctness is covered by `AssessmentFlowTest` at the HTTP level (which tests step progression via form posts, not Alpine state). Adding a visual regression suite is out of scope.

## Out of Scope

- Dark mode.
- Animation (the brand guide notes the logo curve "can breathe" — deferred).
- Any copy changes (all Indonesian text stays as-is).
- New pages or routes.
- Admin, AI/advice generation, or any backend logic.
- A visual regression test suite.
- The `relationship-project-brand-guide.html` file itself — it stays as a reference document, not modified.

## Further Notes

- Tailwind v4 is in use (`@tailwindcss/vite ^4.0.0`). The CSS-first `@theme` approach (no `tailwind.config.js`) is the correct pattern for this project.
- The `radio-circle` base-layer override for circular radio inputs must be retained — it is the only intentional radius exception in the system.
- The `[x-cloak]` display-none rule must be retained.
- Token class names in Blade templates should use the named token (e.g. `bg-navy`, `text-cream`, `border-line`) rather than arbitrary hex values, so that `DESIGN.md` and the actual classes stay in sync.
- The `--color-error` semantic token remains for `@error()` validation states on inputs.
