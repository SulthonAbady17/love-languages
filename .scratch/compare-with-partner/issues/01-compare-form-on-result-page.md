# 01: Compare form on Result Page

**What to build:** A "Compare with Partner" section appears on every Result Page, between the permanent-link aside and the feedback section. It contains a Result Token input field and a submit button. Submitting a valid partner token redirects to `/compare/{tokenA}/{tokenB}`. Submitting an unknown token redirects back with an inline flash error. Submitting the Participant's own token redirects back with a flash error ("You can't compare with yourself"). A `CompareController` with a `show` method handles the `/compare/{tokenA}/{tokenB}` route and its validation; at this stage the Comparison Page itself is a stub — just enough to return a 200 and confirm both tokens are present.

**Blocked by:** None (can start immediately)

**Status:** complete

- [x] A "Compare with Partner" section is visible on the Result Page between the permanent-link aside and the feedback section
- [x] The section contains a Result Token input field and a submit button
- [x] Submitting a valid partner token (different from the page's own token) redirects to `/compare/{tokenA}/{tokenB}`
- [x] Submitting an unknown token redirects back to the Result Page with an inline flash error
- [x] Submitting the page's own token redirects back with a flash error ("You can't compare with yourself")
- [x] A malformed token (fails the `^[a-z0-9]{16}$` route constraint) returns 404 before reaching the controller
- [x] An unknown `tokenA` in the comparison URL returns 404
- [x] `CompareControllerTest` covers all the above scenarios with HTTP assertions
