# 01: Advice column and Result Page block

**What to build:** A Participant who has stored Advice sees it as a bordered block directly under their highlighted top Love Language on the Result Page; a Participant with no Advice (including rows created before this feature) sees the page render exactly as it does today, with no empty or broken block.

**Blocked by:** None (can start immediately).

**Status:** ready-for-agent

- [ ] Nullable TEXT `advice` column added to the existing Participant row, fillable on the model; migration runs clean on SQLite `:memory:`
- [ ] Result Page renders the Advice block only when stored Advice exists: font-mono label in Bahasa Indonesia, `border-[3px]` black border, no rounded corners, plain text, placed directly beneath the highlighted top Love Language section
- [ ] A Participant with null Advice renders the Result Page with no Advice block and no visual regression
- [ ] Feature test in the existing Result Page test file: stored Advice renders the block with its text
- [ ] Feature test in the existing Result Page test file: null Advice renders no block (covers legacy rows)
- [ ] Existing Result Page assertions (highlighted-block regex, article count, ordering) still pass untouched or with only deliberate selector updates
- [ ] GLOSSARY entry for Advice is present and matches the spec wording
