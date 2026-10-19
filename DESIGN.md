# Relationship Project Design System

## Overview

Relationship Project is a modern relationship lifestyle brand designed for Indonesian couples. The brand identity is calm, warm, premium, and intentional — emphasizing growth, quiet partnership, and mutual understanding. It is explicitly **not** dating, **not** couples therapy, and **not** wedding planning.

The design system translates this identity into a cohesive, mobile-first digital experience across all views. Stark monochrome aesthetics, harsh borders, and heavy shadows are rejected in favor of warm tones, editorial typography, subtle borders, and intentional rounding.

`DESIGN.md` is the canonical source of truth for all color tokens, typography scales, radius values, component guidelines, and implementation rules in the application.

---

## Colors

The palette balances deep midnight navy for grounded authority, warm off-white (cream) for approachable calm, soft sage green for connection and growth, and terracotta for a singular warm accent.

### Palette Tokens

| Token Name | CSS Variable | Hex | Role & Usage |
|---|---|---|---|
| **Navy** | `--color-navy` | `#1E293B` | Primary: body text, wordmark, primary button fills, hero backgrounds, completed step dots |
| **Navy Ink** | `--color-navy-ink` | `#151E2B` | Deep navy for small high-contrast labels, badges, and icon details |
| **Sage** | `--color-sage` | `#A8C3A0` | Secondary: connective elements, logo mark connecting curve, step indicator active dot |
| **Cream** | `--color-cream` | `#FAF9F6` | Surface / Background: replaces pure white for page background, callout backgrounds, inverse text on navy |
| **Terracotta** | `--color-terracotta` | `#C97C5D` | Accent: eyebrow labels, logo period mark, single focal highlights (used sparingly, never as large fills) |
| **Line** | `--color-line` | `#E4E1D8` | Structural: 1px borders, card outlines, dividers, inactive step dot borders |
| **Muted** | `--color-muted` | `#6B7280` | Secondary text: descriptions, captions, unselected states, secondary body copy |

### Semantic Validation Tokens

Used strictly for form validation feedback and status alerts:

- **Error**: `--color-error` (`#FF0000`) — validation error messages and input alert states.
- **Success**: `--color-success` (`#008000`) — success confirmations and positive indicators.

---

## Typography

The typography system pairs **Plus Jakarta Sans** as the contemporary, human workhorse for all interface and body copy with **Newsreader Italic** as a warm serif accent reserved exclusively for editorial moments.

### Font Families

- **Sans / Body (`--font-sans`, `--font-body`)**: `'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif`
  - Loaded weights: 400 (Regular), 500 (Medium), 600 (SemiBold).
  - Used for all page headings, body copy, navigation, buttons, form controls, and UI labels.
- **Editorial Accent (`--font-editorial`)**: `'Newsreader', Georgia, serif`
  - Loaded weights: 400 Italic, 500 Italic.
  - Used **only** for the top Love Language reveal on the Results page. Never used for general interface elements, buttons, or body copy.
- **Monospace (`--font-mono`)**: `ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace`
  - Retained solely for code, raw tokens, or technical URL display.

### Typography Scale

| Level | Font Family | Size | Weight | Tracking / Line Height | Notes |
|---|---|---|---|---|---|
| **Display** | Plus Jakarta Sans | 64px (`4xl`–`5xl` / `text-[64px]`) | 500 | Tracking −0.02em, Line-height 1.05 | Welcome hero header on desktop |
| **Heading** | Plus Jakarta Sans | 34px (`text-2xl`–`text-4xl`) | 500 | Tracking −0.01em, Line-height 1.2 | Assessment step headers, section titles |
| **Subheading** | Plus Jakarta Sans | 26px (`text-xl`–`text-2xl`) | 500 | Tracking normal, Line-height 1.3 | Category headings, modal titles |
| **Body** | Plus Jakarta Sans | 16–17px (`text-base`) | 400 | Tracking normal, Line-height 1.6–1.65 | Standard reading copy, descriptions |
| **Eyebrow / UI Label** | Plus Jakarta Sans | 12px (`text-xs`) | 500 | Tracking 0.14–0.16em, Uppercase | Terracotta accent label above headings |
| **Caption** | Plus Jakarta Sans | 13px (`text-xs`–`text-sm`) | 400 | Tracking normal, Line-height 1.5 | Muted secondary notes, helper copy |
| **Editorial Accent** | Newsreader Italic | 26px+ (`text-3xl`–`text-[56px]`) | 400–500 Italic | Italic, Line-height 1.15 | Results page top Love Language name only |

---

## Border Radius Scale

All interactive elements and surface containers follow an intentional, softened radius scale:

| Token | CSS Variable | Value | Usage |
|---|---|---|---|
| **Small** | `--radius-sm` | `8px` (`rounded-sm`) | Buttons, text inputs, small badges, statement number tags |
| **Medium** | `--radius-md` | `12px` (`rounded-md`) | Sub-containers, small cards, aside callouts |
| **Large** | `--radius-lg` | `16px` (`rounded-lg`) | Main cards, statement fieldsets, hero banner blocks |
| **Full / Circle** | `rounded-full` | `9999px` | Circular radio button selectors (`.radio-circle`) |

> [!NOTE]
> Harsh 0px corners from brutalist systems are completely removed. Border radius creates a gentle, tactile experience.

---

## Elevation & Shadows

The Relationship Project design system does **not** use drop shadows (`box-shadow`).

Visual hierarchy and depth are achieved entirely through:
1. **Surface contrast**: Warm cream background (`#FAF9F6`) against pure white card surfaces (`#FFFFFF`) or rich navy hero fills (`#1E293B`).
2. **Subtle line borders**: Clean 1px borders using `--color-line` (`#E4E1D8`).
3. **Typography scale**: Crisp proportional contrast between terracotta eyebrows, large headings, and readable body text.

---

## Responsive Layout & Shell

All screens are designed **mobile-first**: base Tailwind utility classes target small mobile viewports (≥320px), progressively enhanced with `sm:` (≥640px), `md:` (≥768px), and `lg:` (≥1024px).

### Page Container
- **Mobile**: Full-bleed with horizontal padding (`px-4 py-8`).
- **Tablet / Desktop**: Centered readable single-column container (`max-w-3xl mx-auto`).
- **Body**: Cream background (`bg-cream`), navy text (`text-navy`), top padding (`pt-16 sm:pt-20`) to prevent overlap with the fixed top navigation bar.

### Top Navigation Bar
- A fixed header pinned to the top of all pages (`fixed top-0 inset-x-0 z-50 bg-cream border-b border-line`).
- Contains the brand mark:
  - **Concept 03 SVG Mark**: Two weighted dots (Navy `#1E293B` and Terracotta `#C97C5D`) connected by a gentle unhurried Sage green curve (`#A8C3A0`), symbolizing partnership and growth.
  - **Wordmark**: `relationship project` in lowercase Plus Jakarta Sans (Medium, tracking 0.08em, Navy) followed by a Terracotta period (`.`).

---

## Components

### Buttons
- **Primary Button**:
  - Navy fill (`bg-navy`), cream text (`text-cream`), 1px line border (`border border-line`), `rounded-sm`.
  - Hover: Opacity 90% (`hover:opacity-90`).
  - Minimum touch target height: 44px (`min-h-[44px]`). Full width on mobile (`w-full`), auto on tablet/desktop (`sm:w-auto`).
- **Secondary Button**:
  - White fill (`bg-white`), navy text (`text-navy`), 1px line border (`border border-line`), `rounded-sm`.
  - Hover: Navy fill (`hover:bg-navy hover:text-cream`).
  - Minimum touch target height: 44px.
- **Disabled State**:
  - Muted border, muted text, `cursor-not-allowed`, opacity 60%.

### Cards & Fieldsets
- White background (`bg-white`), 1px border (`border border-line`), rounded corners (`rounded-lg`), generous padding (`p-4 sm:p-6`).
- No shadows.

### Text Inputs
- White background (`bg-white`), 1px border (`border border-line`), navy text (`text-navy`), `rounded-sm`.
- Padding: `px-3.5 py-3`, minimum height 44px.
- Focus: Outline none, border and ring in Navy (`focus:border-navy focus:ring-1 focus:ring-navy`).
- Error state: Border in `--color-error` (`border-error`).

### Statement Fieldset & Rating Options
- Retains semantic `<fieldset>` and `<legend>` for accessibility.
- Statement number badge: Navy fill (`bg-navy`), cream text (`text-cream`), `rounded-sm`, font-medium.
- Likert Rating scale:
  - Mobile: Stacked 1-column list (`grid-cols-1 gap-2.5`).
  - Tablet/Desktop: 5-column grid (`sm:grid-cols-5 gap-2`).
  - Touch target: Minimum height 44px (`min-h-[44px]`), flex items centered.
  - Unselected state: White background, navy text, 1px line border (`border border-line`), `rounded-sm`.
  - Selected state: Navy background (`bg-navy`), cream text (`text-cream`), line border, `rounded-sm`.

### Step Indicator (Assessment Flow)
- 5 dots positioned under the "Langkah X dari 5" progress label.
- Dot dimensions: 10px diameter with touch-safe spacing (`gap-3`).
- **Completed steps**: Navy fill (`bg-navy`).
- **Current active step**: Sage fill (`bg-sage`).
- **Upcoming steps**: Transparent fill with 1px line border (`border border-line`).

### Result Page Hero
- Container: Navy fill (`bg-navy`), cream text (`text-cream`), large radius (`rounded-lg`), generous padding (`p-6 sm:p-10`).
- Eyebrow: "BAHASA CINTA UTAMA" in terracotta (`text-terracotta`), uppercase, tracked.
- Love Language Name: Rendered in Newsreader Italic (`font-editorial italic`), display scale (`text-3xl sm:text-5xl md:text-[56px]`).
- Description: Plus Jakarta Sans body copy in cream text (`text-cream/90`).

### Permanent Link Aside
- Surface: Cream background (`bg-cream`), line border (`border border-line`), `rounded-lg`.
- Link display: Monospace font, truncated on small screens (`truncate`).
- Copy Button: Navy fill (`bg-navy`), cream icon (`text-cream`), `rounded-sm`, minimum 44px touch target.

---

## Do / Don't Rules

### Do:
- **DO** use Plus Jakarta Sans for all UI copy, navigation, buttons, labels, and headings.
- **DO** reserve Newsreader Italic strictly for editorial moments (the top Love Language name on the Result page).
- **DO** default the page background to warm off-white Cream (`#FAF9F6`), not stark white.
- **DO** use Terracotta (`#C97C5D`) with restraint as a focused accent (eyebrow text, logo period), keeping it rare and meaningful.
- **DO** build every component mobile-first with minimum 44px touch targets.
- **DO** use named Tailwind utility tokens (`bg-navy`, `text-cream`, `border-line`, `rounded-sm`, `rounded-lg`) rather than hardcoded hex codes.

### Don't:
- **DON'T** use brutalist RawBlock styling (no 3px/5px black borders, no sharp 0px radius resets, no pure black background blocks).
- **DON'T** use drop shadows (`box-shadow`) anywhere in the application.
- **DON'T** use romantic clichés (hearts, rings, cupid imagery, or wedding motifs) in iconography or branding.
- **DON'T** use Newsreader for body text, inputs, buttons, or form labels.
- **DON'T** allow horizontal scrolling on mobile viewports down to 320px width.
- **DON'T** introduce arbitrary colors outside the defined brand palette.
