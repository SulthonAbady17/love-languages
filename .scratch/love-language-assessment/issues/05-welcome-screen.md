# 05: Welcome screen

**What to build:** The entry point of the application. A Participant lands on `GET /`, sees an intro page explaining the assessment, enters their name, and is sent to the first step of the assessment. The name is stored in the Laravel session.

Specifics:
- `GET /` renders a welcome Blade view styled with RawBlock (Archivo Black headline, Work Sans body, thick black borders, no rounded corners)
- The page includes: app title, a short explanation of what the assessment is and how it works, a name input field, and a primary "Mulai Penilaian" button
- `POST /assessment/start` validates that name is present (required, max 100 chars), stores the name in the session, and redirects to the assessment
- Validation error is shown inline below the input using RawBlock helper text style (error red, Work Sans 12px)
- All copy is in Bahasa Indonesia

**Blocked by:** 02 (front-end foundation must be in place)

**Status:** completed

- [x] `GET /` returns 200 and renders the welcome view
- [x] The page displays a name input and a submit button
- [x] Submitting without a name shows a validation error message in Bahasa Indonesia
- [x] Submitting a valid name stores it in the session and redirects to the assessment
- [x] Page is styled using RawBlock conventions (no rounded corners, thick borders, correct fonts)
- [x] All copy is in Bahasa Indonesia
