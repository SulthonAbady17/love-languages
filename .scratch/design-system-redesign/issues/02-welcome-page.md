# 02: Welcome page (mobile-first)

**What to build:** Restyle the Welcome page to the Relationship Project design system. A Participant visiting `/` on a phone or desktop sees a warm, premium page: cream background, brand typography, a name input and submit button that feel considered rather than mechanical, and comfortable touch targets on mobile.

**Blocked by:** 01 — Design tokens, fonts & app shell

**Status:** complete

- [x] Page heading uses the Display typography scale (large, Plus Jakarta Sans 500, tight letter-spacing); scales from `text-3xl` on mobile to the full display size on desktop.
- [x] An eyebrow label above the heading is set in terracotta, uppercase, tracked — matching the brand's section-label pattern.
- [x] The name input is full-width on all screen sizes, uses the cream/line border card treatment, `rounded-sm`, and shows the error state with the `--color-error` token (not black border).
- [x] The submit button ("Mulai Penilaian") uses the primary button style: Navy fill, cream text, `rounded-sm`. Full-width on mobile, auto-width from `sm:`.
- [x] The card wrapping the form uses white fill, 1px `border-line` border, `rounded-lg`, comfortable padding.
- [x] No horizontal scrolling at 320px viewport width. Touch targets (input, button) meet 44px minimum height.
- [x] All existing `WelcomeScreenTest` assertions continue to pass (text content and form behaviour are unchanged).
