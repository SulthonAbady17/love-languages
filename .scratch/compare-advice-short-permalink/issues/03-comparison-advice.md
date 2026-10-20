# 03: Comparison Advice — "Ringkasan & Saran"

**What to build:** When a Comparison is created for the first time, a new agent — sibling to the Result Page's Advice agent — writes the **Comparison Advice**: exactly two plain-prose paragraphs in Bahasa Indonesia, a ringkasan of how the two Love Language profiles fit together, then saran for the pair. Neither Participant's name may appear in the output. The advice is stored on the Comparison and rendered in a "Ringkasan & Saran" section below both scorecards, styled like the Result Page's Advice section, paragraphs separated by a line break, shown only when advice is non-null. Generation happens exactly once, at row creation: an existing Comparison is never touched again, even one left with null advice after a provider failure — a failure is logged and swallowed, the Comparison survives, the page renders cards without the section. The creation route is throttled (5 per minute), since every novel pair costs one AI call. The glossary gains Comparison Advice and states the shared rule both Advice kinds follow: generated once, may be absent, never regenerated.

**Blocked by:** 02: Persist Comparisons behind a short permalink

**Status:** ready-for-agent

- [ ] First-time creation of a Comparison prompts the agent exactly once; the fake returns two paragraphs and both render under the scorecards, separated by a line break.
- [ ] The section label is "Ringkasan & Saran"; the section appears below both cards; markup mirrors the Result Page's Advice section (terracotta uppercase label, body prose).
- [ ] The agent receives both Participants' ranked Results and names, and is instructed never to emit either name.
- [ ] Provider failure at creation is swallowed: the redirect still happens, the Comparison Page returns 200 with scorecards, the section is absent, the row keeps null advice.
- [ ] Resubmitting an existing ordered pair triggers no additional prompt (the agent fake confirms exactly one prompt across both submissions) and never changes the stored advice.
- [ ] A Comparison with null advice renders no empty section and no error.
- [ ] The creation route is rate-limited at 5 requests per minute.
- [ ] The Result Page's own Advice (single-profile) is completely unchanged.
- [ ] All advice scenarios are covered through the existing Compare feature test seam using the agent fake (including the throw-on-prompt form) and are green.
- [ ] Glossary gains Comparison Advice and the shared "generated once, may be absent, never regenerated" rule covering both Advice kinds.
