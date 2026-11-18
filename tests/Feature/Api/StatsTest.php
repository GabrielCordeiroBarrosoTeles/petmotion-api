<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_stats_include_upcoming_health_from_the_last_week_onwards(): void
    {
        $me = $this->makeUser();
        $pet = $this->makePet($me);
        $this->makeHealth($pet, ['title' => 'Muito atrasada', 'next_date' => now()->subDays(20)->toDateString()]);
        $this->makeHealth($pet, ['title' => 'Atrasada', 'next_date' => now()->subDays(2)->toDateString()]);
        $this->makeHealth($pet, ['title' => 'Em breve', 'next_date' => now()->addDays(3)->toDateString()]);
        $this->makeHealth($pet, ['title' => 'Sem data', 'next_date' => null]);

        $this->getJson("/api/pets/{$pet->id}/stats", $this->bearer($me))
            ->assertOk()
            ->assertJsonStructure(['today', 'week', 'streak_days', 'totals', 'achievements'])
            ->assertJsonCount(2, 'upcoming_health')
            ->assertJsonPath('upcoming_health.0.title', 'Atrasada');
    }

    public function test_stats_of_other_users_pet_are_not_found(): void
    {
        $pet = $this->makePet($this->makeUser());

        $this->getJson("/api/pets/{$pet->id}/stats", $this->bearer($this->makeUser()))->assertNotFound();
    }
}
