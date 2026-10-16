# 03: Publish `main` as source of truth

**What to build:** The public GitHub repository is the single source of truth: `main` holds the finished AI-advisor feature, and pushing to `main` is the only way the deployed app changes — deployed code and repository can never diverge silently.

**Blocked by:** 02 (the GitHub repository must exist)

**Status:** done

- [x] Local `main` takes over from `test-ai-advisor-feature` (the branch was renamed onto `main` at its tip `6100ede`, 34 commits ahead; linear history, no merge commit — one old divergent *unpushed* local commit was discarded, see comment)
- [x] Remote added and `main` pushed to the public repository
- [x] `main` confirmed to contain every runtime file verified in ticket 01
- [x] `test-*` branches confirmed to never deploy (~~Render watches `main` only~~ — deploy target retargeted to Railway in ticket 02; the repo's only remote branch is `main`)

## Comments

### 2026-10-17 — verification run (agent)

**Branch consolidation — a rename, not a fast-forward.** The checkbox as originally drafted ("local `main` fast-forwarded… nothing extra") was written on a wrong assumption and was corrected after review. What actually happened, per reflog: `test-ai-advisor-feature` was renamed onto `main` at its tip `6100ede`, overwriting a *local-only, never-pushed* old `main` whose tip (`ea2af66`, "implement welcome screen and session name persistence") diverged from `main`'s history. End state is correct for this epic — `main` holds the full feature history, `git log --merges main` is empty (linear, no merge commit), and every local branch (`test-opencode` included) has **0** commits missing from `main` — but one detail of the discarded tip matters: `ea2af66`'s `resources/views/welcome.blade.php` differs from the version that survived (`2e84a2a`, same commit message, rewritten) in **3 styling lines** — `md:text-6xl tracking-tight` on the h1, `md:p-8` on the form section, and `text-base` (no `active:bg-black active:text-white`) on the submit button. Nothing in the suite or routes pins these; whether the rewrite was the preferred design is the owner's call. If the discarded styling is wanted back: `git checkout ea2af66 -- resources/views/welcome.blade.php`. `ea2af66` is currently kept alive only by the reflog — record this comment (or the SHA) is the durable pointer. The 8 commits `main` gained after `6100ede` are the subsequent ticket 02/04/05 work, made directly on `main`.

**Remote pushed.** `origin` = `https://github.com/SulthonAbady17/love-languages.git` (the `love-languanges` typo repo from ticket 02 is gone — the name now matches the spec). Local `main` was 2 commits ahead of `origin/main` (`324909a`, `fed2d87`); pushed with `git push origin main` → `fbe5077..fed2d87`, a clean fast-forward (nothing on the remote was outside local `main`). `git ls-remote --heads origin` then returned **exactly one ref**: `refs/heads/main`. After this ticket's own close-out commit (`a8ece9b`) was pushed, remote `main` again matches local `main` exactly. Remote and local cannot diverge silently from here: any future `main` push is itself a fast-forward or git refuses it.

**Runtime files present in pushed `main`.** `git ls-tree -r origin/main` contains every file ticket 01 verified: `statements.json`, all seven `.agents/skills/stop-slop/**` files, plus the deploy runtime (`docker/start.sh` — referenced by the `Dockerfile`'s `COPY`/`CMD` — `railway.toml`, `Dockerfile`, `composer.json` + lock, `package.json` + lock).

**`test-*` branches never deploy.** Three independent guarantees: (1) the remote has no branch except `main` (`git ls-remote --heads origin` shows one ref), so no `test-*` ref even exists for a deploy platform to watch; (2) no CI workflows exist (`.github/workflows/` absent) and neither `railway.toml` nor `Dockerfile` names any branch — Railway's source-branch setting defaults to the repo's default branch (`main`); (3) nothing local (`test-opencode`) has ever been pushed. Note: the deploy target is Railway, not Render — Render was dropped in ticket 02 (billing-card wall) and retargeted; the "Render watches `main` only" intent is satisfied by the remote-branch fact above, which covers any watcher.

No code changes in this ticket — it is git/deploy verification only, so no TDD seam applied. Full suite re-run as the safety net: **46 passed, 348 assertions, 0 failed** (`php artisan test --compact`).
