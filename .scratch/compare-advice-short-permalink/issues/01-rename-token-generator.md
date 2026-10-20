# 01: Rename ResultToken to Token

**What to build:** The token generator that mints Result Tokens is renamed to a neutral name, because it will mint Comparison Tokens too. Purely mechanical prefactor — zero behaviour change, the full suite stays green. The format is untouched: 16 characters, lowercase a–z0–9, CSPRNG.

**Blocked by:** None (can start immediately)

**Status:** ready-for-agent

- [ ] The token generator class carries a neutral name (not Result-specific) and still produces the same token shape.
- [ ] Every call site that mints or references a Result Token (assessment creation, the user factory, any tests) uses the new name.
- [ ] The permalink route constraint is unchanged; malformed tokens still 404 before reaching any controller.
- [ ] Full test suite green with no behaviour change.
