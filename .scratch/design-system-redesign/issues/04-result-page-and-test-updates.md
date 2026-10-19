# 04: Result page + test updates (mobile-first)

**What to build:** Restyle the Result page to the Relationship Project design system and update the two feature-test assertions that pin RawBlock-specific classes. A Participant viewing their results sees a premium Navy hero block with their top Love Language name in Newsreader italic, a clean ranked list, and easy access to their permanent link — on any device. The test suite stays green.

**Blocked by:** 01 — Design tokens, fonts & app shell

**Status:** complete

- [x] The `#top-love-language` hero section uses Navy fill (`bg-navy`) and cream text (`text-cream`), with `rounded-lg` and generous padding (`p-6 sm:p-10`).
- [x] Inside the hero, the eyebrow label ("Bahasa Cinta Utama") uses the terracotta eyebrow style (uppercase, tracked). The Love Language name is rendered in Newsreader italic at a large editorial size, scaling from `text-3xl` on mobile to `text-5xl md:text-[56px]` on larger screens. The Love Language description uses Plus Jakarta Sans body style in cream.
- [x] Ranked Love Language articles use white-fill cards, 1px `border-line` border, `rounded-lg`. The rank number badge is Navy fill, cream text, `rounded-sm`. Score is displayed in the muted body style.
- [x] The `#permanent-link` aside uses a cream background, `border-line` border, `rounded-lg`. The URL text truncates (`truncate`) on mobile so it doesn't overflow. The copy button uses Navy fill, cream icon.
- [x] The Feedback section (both the form state and the thank-you state) uses the same white card treatment as ranked articles.
- [x] No horizontal scrolling at 320px viewport width.
- [x] `ResultPageTest::test_top_scoring_love_language_is_shown_in_an_inverted_black_block` is renamed to reflect the Navy block, and its assertions are updated from `bg-black` / `text-white` to `bg-navy` / `text-cream`.
- [x] `ResultPageTest::test_stored_advice_renders_a_bordered_block_directly_under_the_top_love_language` assertion on `border-[3px] border-black` is updated to match the new border token class on the `#advice` block.
- [x] All other `ResultPageTest` and `ResultPageFeedbackTest` assertions continue to pass.
