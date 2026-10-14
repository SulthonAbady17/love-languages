# 02: Synchronous Advice generation on submission

**What to build:** When a Participant submits their Assessment, the application asks a Groq-backed advice agent (via the Laravel AI SDK) for short Bahasa Indonesia guidance about their top-ranked Love Language, written to address their full five-score ranking, and stores it on their Participant row before redirecting to the Result Page — so the Result Page already shows Advice on first load. If the provider fails or times out, the submission still succeeds, answers and Results are still persisted, and the Participant simply has no Advice.

**Blocked by:** 01: Advice column and Result Page block.

**Status:** ready-for-agent

- [ ] `laravel/ai` installed as a Composer dependency; its configuration file published, its conversation-storage migrations neither published nor run
- [ ] Groq API key declared in the environment example file and read through the SDK configuration — no credentials in code
- [ ] A dedicated advice agent class following the SDK's `make:agent` convention: no tools, no conversation context, plain text in/out; prompt assembled from the data the agent receives (top Love Language name + description, full five-Love-Language ranking with scores), excluding Participant name, raw Likert Responses, and Result Token
- [ ] Prompt pins the output language to Bahasa Indonesia and instructs two short paragraphs of plain prose (no headings, bullets, or markdown), encouraging and practical, grounded only in the supplied profile
- [ ] Generation runs synchronously inside the submission request, after the Assessment transaction has committed and before the redirect, with a bounded timeout inside a try/catch; exceptions are logged and swallowed so the advice column stays null on failure
- [ ] The agent targets whichever Love Language the Result Page highlights — same first-after-ordering-by-score-descending selection — including when all five scores are equal
- [ ] Feature test (existing submission test file, agent faked): successful submission persists the faked Advice on the Participant row, the redirect target renders the Advice block, and the captured prompt names the highlighted Love Language and omits the Participant's name
- [ ] Feature test (agent faked to throw): submission succeeds, answers and Results persisted, advice stays null, Result Page renders with no block
- [ ] Feature test: five equal scores → the highlighted Love Language and the one named in the prompt are the same
- [ ] Existing submission tests that POST the assessment fake the agent so the suite never contacts a real provider
- [ ] Full suite green: `php artisan test --compact`; Pint clean: `vendor/bin/pint --dirty --format agent`
