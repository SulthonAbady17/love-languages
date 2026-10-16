# 03: Publish `main` as source of truth

**What to build:** The public GitHub repository is the single source of truth: `main` holds the finished AI-advisor feature, and pushing to `main` is the only way the deployed app changes — deployed code and repository can never diverge silently.

**Blocked by:** 02 (the GitHub repository must exist)

**Status:** ready-for-agent

- [ ] Local `main` fast-forwarded from `test-ai-advisor-feature` (34 commits ahead; local `main` has nothing extra, so it is a fast-forward, not a merge)
- [ ] Remote added and `main` pushed to the public repository
- [ ] `main` confirmed to contain every runtime file verified in ticket 01
- [ ] `test-*` branches confirmed to never deploy (Render watches `main` only)
