# 03: Publish `main` as source of truth

**What to build:** The public GitHub repository is the single source of truth: `main` holds the finished AI-advisor feature, and pushing to `main` is the only way the deployed app changes — deployed code and repository can never diverge silently.

**Blocked by:** 02 (the GitHub repository must exist)

**Status:** done

- [x] Local `main` fast-forwarded from `test-ai-advisor-feature` (34 commits ahead; local `main` has nothing extra, so it is a fast-forward, not a merge)
- [x] Remote added and `main` pushed to the public repository
- [x] `main` confirmed to contain every runtime file verified in ticket 01
- [x] `test-*` branches confirmed to never deploy (Render watches `main` only)

## Comments

### 2026-10-17 — verification run (agent)

**Fast-forward (not a merge).** `test-ai-advisor-feature` no longer exists as a separate branch: the reflog shows it was renamed to `main` at `6100ede` (its tip), so `main` *is* the feature branch — no merge commit ever happened (`git log --merges main` is empty, history is linear). The other local branch `test-opencode` has **0** commits not in `main` (`git rev-list --count main..test-opencode` = 0), so no local branch holds anything `main` lacks. The 8 commits `main` gained after `6100ede` are the subsequent ticket 02/04/05 work, made directly on `main`.

**Remote pushed.** `origin` = `https://github.com/SulthonAbady17/love-languages.git` (the `love-languanges` typo repo from ticket 02 is gone — the name now matches the spec). Local `main` was 2 commits ahead of `origin/main` (`324909a`, `fed2d87`); pushed with `git push origin main` → `fbe5077..fed2d87`, a clean fast-forward (nothing on the remote was outside local `main`). `git ls-remote --heads origin` now returns **exactly one ref**: `refs/heads/main` at `fed2d87`, identical to local `main`. Remote and local cannot diverge silently from here: any future `main` push is itself a fast-forward or git refuses it.

**Runtime files present in pushed `main`.** `git ls-tree -r origin/main` contains every file ticket 01 verified: `statements.json`, all seven `.agents/skills/stop-slop/**` files, plus the deploy runtime (`docker/start.sh` — referenced by the `Dockerfile`'s `COPY`/`CMD` — `railway.toml`, `Dockerfile`, `composer.json` + lock, `package.json` + lock).

**`test-*` branches never deploy.** Three independent guarantees: (1) the remote has no branch except `main` (`git ls-remote --heads origin` shows one ref), so no `test-*` ref even exists for a deploy platform to watch; (2) no CI workflows exist (`.github/workflows/` absent) and neither `railway.toml` nor `Dockerfile` names any branch — Railway's source-branch setting defaults to the repo's default branch (`main`); (3) nothing local (`test-opencode`) has ever been pushed. Note: the deploy target is Railway, not Render — Render was dropped in ticket 02 (billing-card wall) and retargeted; the "Render watches `main` only" intent is satisfied by the remote-branch fact above, which covers any watcher.

No code changes in this ticket — it is git/deploy verification only, so no TDD seam applied. Full suite re-run as the safety net: **46 passed, 348 assertions, 0 failed** (`php artisan test --compact`).
