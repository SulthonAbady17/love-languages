# 01: Pre-ship verification on Postgres

**What to build:** The owner can trust that the app is engine-safe and the deployed checkout is self-sufficient before anything ships: the full PHPUnit suite passes against a Postgres connection, and every runtime file the deploy needs is tracked in git.

**Blocked by:** None (can start immediately)

**Status:** ready-for-agent

- [ ] Existing PHPUnit feature suite (assessment flow, submission scoring, result page, statements seeder, schema expectations) runs green against a Postgres connection — a Neon database or local Postgres — with zero new test files on the happy path
- [ ] The statements file at the repo root and the advice skill directory are both verified tracked in git
- [ ] `APP_KEY` and `GROQ_API_KEY` verified untracked and absent from git history
- [ ] Composer PHP floor of 8.3 confirmed from the lockfile platform check
