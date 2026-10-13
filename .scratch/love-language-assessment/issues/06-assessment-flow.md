# 06: Assessment multi-step flow

**What to build:** The 5-step assessment form. A Participant steps through one Love Language at a time, rating 5 statements per step on a 1–5 Likert scale. The form is managed client-side by Alpine.js; no page reload between steps. A single POST is sent at the end.

Specifics:
- `GET /assessment` loads all 5 Love Languages with their 5 Statements from the database and passes them to the Blade view
- The Blade view renders a single form with all 25 statements grouped by Love Language, but only one group is visible at a time (Alpine.js controls the visible step)
- Each statement shows 5 radio buttons labelled: Sangat Tidak Setuju (1), Tidak Setuju (2), Netral (3), Setuju (4), Sangat Setuju (5)
- A progress indicator shows "Langkah X dari 5" and the current Love Language name
- "Lanjut" (Next) button is disabled until all 5 statements on the current step have a response; Alpine.js enforces this without a server round-trip
- "Kembali" (Back) button is shown from step 2 onward
- On step 5, the Next button becomes "Kirim" (Submit) and submits the form via POST to `/assessment`
- If the Participant navigates directly to `GET /assessment` without a name in session, they are redirected to `GET /`
- Styled with RawBlock: Likert radio buttons are circular (the one allowed exception per DESIGN.md), statements use Work Sans body size, Love Language title uses Archivo Black

**Blocked by:** 04 (seed data must exist), 05 (welcome screen and session name must be in place)

**Status:** completed

- [x] `GET /assessment` returns 200 when name is in session; redirects to `/` when not
- [x] All 5 Love Languages and their statements are rendered in the page source
- [x] Only one Love Language step is visible at a time
- [x] Progress indicator shows the current step number and Love Language name
- [x] "Lanjut" button is disabled until all 5 statements on the current step are answered
- [x] "Kembali" button appears from step 2 onward and returns to the previous step without losing answers
- [x] On step 5, the submit button is labelled "Kirim" and submits the form
- [x] Likert radio buttons are circular, everything else has no border-radius
- [x] All copy is in Bahasa Indonesia
