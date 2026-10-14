<?php

namespace Tests\Unit;

use App\Ai\Agents\AdviceAgent;
use Laravel\Ai\Contracts\HasSkills;
use Laravel\Ai\Skills\Skill;
use Tests\TestCase;

class AdviceAgentSkillTest extends TestCase
{
    public function test_agent_exposes_the_stop_slop_skill(): void
    {
        $agent = new AdviceAgent(collect());

        $this->assertInstanceOf(HasSkills::class, $agent);

        $skills = [...$agent->skills()];

        $this->assertCount(1, $skills);
        $this->assertContainsOnlyInstancesOf(Skill::class, $skills);
        $this->assertSame('stop-slop', $skills[0]->name);
    }
}
