# Glossary

## Assessment
The full set of 25 statements a Participant answers in one sitting to determine their Love Language profile. An Assessment belongs to one Participant and produces five Results — one per Love Language.

## Participant
A person taking the Assessment. Identified only by a self-reported name. Has no password or account. Receives a unique Result Token after completing the Assessment.

## Love Language
One of five psychological categories that describe how a person prefers to give and receive affection. The five Love Languages are: Words of Affirmation, Quality Time, Receiving Gifts, Acts of Service, and Physical Touch. Replaces the word "Category" everywhere in the codebase and UI.

## Statement
A single declarative sentence that a Participant rates on a 1–5 Likert scale. Each Statement belongs to exactly one Love Language. There are exactly 5 Statements per Love Language (25 total).

## Likert Response
A Participant's rating for a single Statement. An integer from 1 (Strongly Disagree) to 5 (Strongly Agree). Stored in the `answers` table as `value`.

## Result
The computed average Likert Response score for one Love Language within one Assessment. Calculated as: sum of Likert Responses for that Love Language ÷ number of Statements in that Love Language. Stored as a float.

## Result Token
A unique, opaque string (UUID) generated when an Assessment is completed. Embedded in the result page URL. The only way for a Participant to retrieve their Results later. Not a password; not a secret — just a stable identifier.

## Result Page
The page at `/results/{token}` that displays a Participant's ranked Love Language Results. The top-scoring Love Language is highlighted. Each Love Language shows a short description. This URL is the Participant's permanent link.

## Advice
AI-generated guidance in Bahasa Indonesia about a Participant's top-ranked Love Language, written to address their full score ranking. Generated exactly once when the Assessment is completed, stored with it, and shown on the Result Page. Never regenerated for an Assessment that already has one; an Assessment may have none.
