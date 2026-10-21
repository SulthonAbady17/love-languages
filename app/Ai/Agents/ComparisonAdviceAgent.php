<?php

namespace App\Ai\Agents;

use App\Models\Result;
use Illuminate\Support\Collection;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasSkills;
use Laravel\Ai\Promptable;
use Laravel\Ai\Skills\Skill;
use Stringable;

#[Timeout(15)]
class ComparisonAdviceAgent implements Agent, HasSkills
{
    use Promptable;

    /**
     * @param  Collection<int, Result>  $initiatorResults  The Initiating Participant's Results ordered by score descending, each eager-loaded with its Love Language.
     * @param  Collection<int, Result>  $partnerResults  The Partner's Results ordered by score descending, each eager-loaded with its Love Language.
     */
    public function __construct(
        private readonly string $initiatorName,
        private readonly Collection $initiatorResults,
        private readonly string $partnerName,
        private readonly Collection $partnerResults,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'TEXT'
            You write short guidance in Bahasa Indonesia for two people who just compared their love-language profiles.

            Before writing, load the stop-slop skill with the LoadSkill tool and follow it.
            Produce exactly two short paragraphs of plain prose: no headings, no bullet points, no markdown of any kind.
            The first paragraph is a ringkasan of how the two profiles fit together; the second paragraph is practical saran for the pair.
            Never include either person's name in your reply.
            Be encouraging and practical. Ground every sentence only in the supplied profiles — invent nothing beyond them.
            The reply must be entirely in Bahasa Indonesia — never English, whatever language the profiles use.
            TEXT;
    }

    /**
     * Get the skills available to the agent.
     *
     * @return list<Skill>
     */
    public function skills(): iterable
    {
        return array_values(array_filter([
            Skill::fromDirectory(base_path('.agents/skills/stop-slop')),
        ]));
    }

    /**
     * Generate the comparison advice text for the supplied pair of rankings.
     */
    public function generate(): string
    {
        return $this->prompt($this->profiles())->text;
    }

    /**
     * Assemble the prompt from both supplied rankings, keeping out anything that is not part of the profiles.
     */
    private function profiles(): string
    {
        return 'Initiating Participant: '.$this->initiatorName.PHP_EOL
            .$this->ranking($this->initiatorResults).PHP_EOL.PHP_EOL
            .'Partner: '.$this->partnerName.PHP_EOL
            .$this->ranking($this->partnerResults);
    }

    /**
     * @param  Collection<int, Result>  $results  Ordered by score descending.
     */
    private function ranking(Collection $results): string
    {
        return $results
            ->map(fn (Result $result, int $index): string => ($index + 1).'. '.$result->loveLanguage->name.' — '.$result->value)
            ->implode(PHP_EOL);
    }
}
