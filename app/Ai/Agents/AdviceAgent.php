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
class AdviceAgent implements Agent, HasSkills
{
    use Promptable;

    /**
     * @param  Collection<int, Result>  $results  The Participant's Results ordered by score descending, each eager-loaded with its Love Language.
     */
    public function __construct(private readonly Collection $results) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'TEXT'
            You write short guidance in Bahasa Indonesia for someone who just completed a love-language assessment.

            Before writing, load the stop-slop skill with the LoadSkill tool and follow it.
            Produce exactly two short paragraphs of plain prose: no headings, no bullet points, no markdown of any kind.
            Be encouraging and practical. Ground every sentence only in the supplied profile — invent nothing beyond it.
            The reply must be entirely in Bahasa Indonesia — never English, whatever language the profile uses.
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
     * Generate the advice text for the supplied ranking.
     */
    public function generate(): string
    {
        return $this->prompt($this->profile())->text;
    }

    /**
     * Assemble the prompt from the supplied ranking, keeping out anything that is not part of the profile.
     */
    private function profile(): string
    {
        $ranking = $this->results
            ->map(fn (Result $result, int $index): string => ($index + 1).'. '.$result->loveLanguage->name.' — '.$result->value)
            ->implode(PHP_EOL);

        $top = $this->results->first();

        return 'Full Love Language ranking (score descending):'.PHP_EOL
            .$ranking.PHP_EOL.PHP_EOL
            .'Top Love Language: '.$top->loveLanguage->name.PHP_EOL
            .'Top Love Language description: '.$top->loveLanguage->description;
    }
}
