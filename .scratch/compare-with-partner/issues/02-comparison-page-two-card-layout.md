# 02: Comparison Page — two-card layout

**What to build:** The stub Comparison Page from ticket 01 is replaced with the full view. Two cards are rendered — one per Participant. Each card has a header showing the Participant's name and a body listing all five Love Languages in that Participant's own rank order (highest score first). Each row shows the rank number, Love Language name, score formatted as `x.x/5.0`, and a progress bar scaled to a maximum of 5.0. On desktop the cards sit side by side; on mobile they stack vertically with the Initiating Participant's card first. A back link at the top of the page returns to `/results/{tokenA}`.

**Blocked by:** 01 — Compare form on Result Page

**Status:** ready-for-agent

- [ ] `/compare/{tokenA}/{tokenB}` renders a full Comparison Page (not the stub)
- [ ] The Initiating Participant's card appears first (left on desktop, top on mobile)
- [ ] Each card header shows the Participant's name
- [ ] Each card body lists all five Love Languages in that Participant's own score-descending order
- [ ] Each row shows rank number, Love Language name, and score as `x.x/5.0`
- [ ] Each row includes a progress bar whose width reflects `value / 5.0`
- [ ] Cards are side by side on desktop and stacked on mobile
- [ ] A back link is present and targets `/results/{tokenA}`
- [ ] `CompareControllerTest` covers: both names visible, Love Languages in correct rank order per card, score format, back link target
