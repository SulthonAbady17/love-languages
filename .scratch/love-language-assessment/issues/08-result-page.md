# 08: Result page

**What to build:** The permanent Result Page at `/results/{token}`. A Participant (or returning Participant) sees their full Love Language profile: all 5 Love Languages ranked by score, the top one highlighted, and a short description for each.

Specifics:
- `GET /results/{token}` looks up the User by token; returns 404 if not found
- Loads the User's 5 Result rows, ordered by `value` DESC
- Renders a Blade view styled with RawBlock showing:
  - Participant's name and a heading ("Hasil Penilaian Bahasa Cinta Anda")
  - The top Love Language highlighted prominently (inverted block: black background, white text, Archivo Black)
  - All 5 Love Languages ranked, each showing: rank number, Love Language name, average score (formatted to 1 decimal place), and description
  - A note that this URL is their permanent link ("Simpan tautan ini untuk melihat hasilmu kembali")
- No back/edit action — result page is read-only
- Page is accessible without a session (returning Participant has no session)

**Blocked by:** 07 (submission must create Results before the page has anything to display)

**Status:** ready-for-agent

- [ ] `GET /results/{valid-token}` returns 200 and displays the Participant's name
- [ ] All 5 Love Languages are shown, ordered by score descending
- [ ] The top-scoring Love Language is visually distinguished from the rest
- [ ] Each Love Language shows its name, score (1 decimal), and description
- [ ] `GET /results/{unknown-token}` returns 404
- [ ] The page is accessible without an active session (works for a returning Participant)
- [ ] All copy is in Bahasa Indonesia
- [ ] Styled with RawBlock conventions (no rounded corners, thick borders, correct fonts)
