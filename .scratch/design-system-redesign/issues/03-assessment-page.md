# 03: Assessment page + step indicator (mobile-first)

**What to build:** Restyle the Assessment page to the Relationship Project design system and add a 5-dot step indicator. A Participant stepping through the Assessment on any device sees branded headings, warm card-style Statement fieldsets, clear Navy-fill selected rating options, a visible 5-dot progress indicator, and navigation buttons that are easy to tap on mobile.

**Blocked by:** 01 — Design tokens, fonts & app shell

**Status:** complete

- [x] The step header Love Language name uses the Heading typography scale; scales from `text-2xl` on mobile to `text-4xl md:text-5xl` on larger screens.
- [x] A 5-dot step indicator sits below the "Langkah X dari 5" text label. Dots are horizontally centred, touch-safe spacing. Completed steps: Navy fill. Current step: Sage fill. Upcoming: transparent, `border-line`. The indicator reads `currentStep` from the existing Alpine `x-data` scope — no new component.
- [x] The Love Language description callout uses a cream-background card with `border-line` border and `rounded-lg`.
- [x] Statement fieldsets use white fill, 1px `border-line` border, `rounded-lg`, and comfortable padding (`p-4 sm:p-6`). The statement number badge is Navy fill, cream text, `rounded-sm`.
- [x] Rating options grid is `grid-cols-1` (stacked, full-width) on mobile and `sm:grid-cols-5` (horizontal Likert scale) on tablet and up. Each option has a minimum touch target height of 44px. Selected state: Navy fill, cream text. Unselected: white fill, navy text, `border-line`.
- [x] Navigation buttons ("Kembali", "Lanjut", "Kirim") meet 44px minimum height. The nav row is `flex-col gap-3` on mobile, `sm:flex-row sm:justify-between` on tablet and up. Primary action (Lanjut/Kirim) uses Navy fill, cream text; secondary (Kembali) uses white fill, navy border.
- [x] No horizontal scrolling at 320px viewport width.
- [x] All existing `AssessmentFlowTest` and `AssessmentSubmissionTest` assertions continue to pass (form behaviour and submission are unchanged).
