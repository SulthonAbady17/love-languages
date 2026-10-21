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
A unique, opaque 16-character lowercase alphanumeric string generated when an Assessment is completed. Embedded in the Result Permalink; Participants never see or exchange it as a standalone value. Not a password; not a secret — just a stable identifier.

## Result Page
The page at `/results/{token}` that displays a Participant's ranked Love Language Results. The top-scoring Love Language is highlighted. Each Love Language shows a short description. Its URL is the Participant's Result Permalink.

## Result Permalink
The URL of a Participant's Result Page. The only address a Participant shares with someone else; the Result Token inside it is an implementation detail.

## Advice
AI-generated guidance in Bahasa Indonesia about a Participant's top-ranked Love Language, written to address their full score ranking. Generated exactly once when the Assessment is completed, stored with it, and shown on the Result Page. Never regenerated for an Assessment that already has one; an Assessment may have none.

## Feedback
A Participant's free-text message about their Assessment experience, stored one per Participant — the database itself refuses a second row for the same Participant. Created only after the Assessment is completed. Immutable once written, and invisible to its author: the Result Page shows either the feedback form or the thank-you, never the stored content.

## Comparison
A persisted side-by-side view of two Participants' Results, stored as one row per ordered pair. The pair (initiator, partner) exists at most once; the reverse order is a distinct Comparison, so whoever initiates owns their own page. Created by submitting the comparison form on a Result Page, never derived on the fly.

## Comparison Permalink
The URL of a Comparison Page: `/compare/{comparisonToken}`, carrying exactly one Comparison Token. Short and stable, so either Participant can save it and reopen the same Comparison.

## Comparison Token
A unique, opaque 16-character lowercase alphanumeric string identifying one Comparison. Same shape and generator as the Result Token, and the same route constraint applies. Embedded in the Comparison Permalink; Participants never see or exchange it as a standalone value. Not a password; not a secret — just a stable identifier.

## Initiating Participant
The Participant who submits the comparison form on a Result Page. They own the Comparison for that ordered pair and their card appears first on the Comparison Page. Their Result Token no longer appears in the Comparison Permalink.

## Partner
The second Participant in a Comparison. Identified by their Result Permalink, which the Initiating Participant pastes into the comparison form.
