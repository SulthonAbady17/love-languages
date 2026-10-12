# 03: Research & write statements

**What to build:** A `statements.json` file at the repo root containing all 25 assessment statements in Bahasa Indonesia — 5 per Love Language — plus a short description per Love Language. This file is the source of truth for seeding and is reviewed by the author before the seeder runs.

File structure:
```json
[
  {
    "love_language": "Words of Affirmation",
    "description": "<short description in Bahasa Indonesia>",
    "statements": [
      "Statement satu...",
      "Statement dua...",
      ...
    ]
  },
  ...
]
```

Requirements for the statements:
- Written in Bahasa Indonesia
- Each statement is a first-person declarative sentence suitable for a 1–5 Likert scale (Sangat Tidak Setuju → Sangat Setuju)
- Statements are clearly anchored to their Love Language — a reader can tell which category a statement belongs to
- No two statements are near-duplicates
- Each Love Language has exactly 5 statements

The five Love Languages and their Indonesian names:
1. Kata-kata Penegasan (Words of Affirmation)
2. Waktu Berkualitas (Quality Time)
3. Menerima Hadiah (Receiving Gifts)
4. Tindakan Pelayanan (Acts of Service)
5. Sentuhan Fisik (Physical Touch)

**Blocked by:** None (can start immediately — but ticket 04 waits for author review of this file before proceeding)

**Status:** ready-for-agent

- [x] `statements.json` exists at the repo root
- [x] File contains exactly 5 Love Language objects
- [x] Each Love Language object has a `love_language` name, a `description` (in Bahasa Indonesia), and exactly 5 `statements`
- [x] All statements are in Bahasa Indonesia
- [x] Statements are distinct and clearly anchored to their Love Language
