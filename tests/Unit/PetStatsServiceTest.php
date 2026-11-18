<?php

namespace Tests\Unit;

use App\Services\PetStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PetStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-17 15:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function unlocked(array $stats): array
    {
        return collect($stats['achievements'])->where('unlocked', true)->pluck('code')->values()->all();
    }

    public function test_empty_pet_has_zeroed_stats_and_no_achievements(): void
    {
        $pet = $this->makePet($this->makeUser(), ['daily_goal_minutes' => 30]);

        $stats = (new PetStatsService)->forPet($pet);

        $this->assertSame(0, $stats['today']['minutes']);
        $this->assertSame(0, $stats['streak_days']);
        $this->assertSame(0, $stats['totals']['walks']);
        $this->assertCount(7, $stats['week']);
        $this->assertSame('2026-11-17', $stats['week'][6]['date']);
        $this->assertSame([], $this->unlocked($stats));
        $this->assertCount(8, $stats['achievements']);
    }

    public function test_today_summary_and_goal_progress_capped_at_one(): void
    {
        $pet = $this->makePet($this->makeUser(), ['daily_goal_minutes' => 30]);
        $this->makeWalk($pet, now()->setTime(9, 0), 25, 1800);
        $this->makeWalk($pet, now()->setTime(12, 0), 20, 1500);

        $today = (new PetStatsService)->forPet($pet)['today'];

        $this->assertSame(45, $today['minutes']);
        $this->assertSame(3300, $today['distance_meters']);
        $this->assertSame(2, $today['walks']);
        $this->assertSame(1, $today['goal_progress']);
    }

    public function test_partial_goal_progress(): void
    {
        $pet = $this->makePet($this->makeUser(), ['daily_goal_minutes' => 40]);
        $this->makeWalk($pet, now()->setTime(9, 0), 10);

        $this->assertEqualsWithDelta(0.25, (new PetStatsService)->forPet($pet)['today']['goal_progress'], 0.001);
    }

    public function test_streak_counts_consecutive_days_including_today(): void
    {
        $pet = $this->makePet($this->makeUser());
        foreach ([0, 1, 2] as $daysAgo) {
            $this->makeWalk($pet, now()->subDays($daysAgo)->setTime(10, 0));
        }
        $this->makeWalk($pet, now()->subDays(5)->setTime(10, 0)); // quebra a sequência

        $stats = (new PetStatsService)->forPet($pet);

        $this->assertSame(3, $stats['streak_days']);
        $this->assertContains('streak_3', $this->unlocked($stats));
        $this->assertNotContains('streak_7', $this->unlocked($stats));
    }

    public function test_streak_is_kept_from_yesterday_when_no_walk_today_yet(): void
    {
        $pet = $this->makePet($this->makeUser());
        $this->makeWalk($pet, now()->subDays(1)->setTime(10, 0));
        $this->makeWalk($pet, now()->subDays(2)->setTime(10, 0));

        $this->assertSame(2, (new PetStatsService)->forPet($pet)['streak_days']);
    }

    public function test_week_groups_minutes_per_day(): void
    {
        $pet = $this->makePet($this->makeUser());
        $this->makeWalk($pet, now()->subDays(2)->setTime(8, 0), 15, 1000);
        $this->makeWalk($pet, now()->subDays(2)->setTime(19, 0), 15, 1000);

        $week = (new PetStatsService)->forPet($pet)['week'];

        $this->assertSame(['date' => '2026-11-15', 'minutes' => 30, 'distance_meters' => 2000], $week[4]);
        $this->assertSame(0, $week[5]['minutes']);
    }

    public function test_achievements_for_distance_time_of_day_and_volume(): void
    {
        $pet = $this->makePet($this->makeUser());
        $this->makeWalk($pet, now()->subDays(30)->setTime(6, 15), 40, 3200);  // madrugador + explorador
        $this->makeWalk($pet, now()->subDays(29)->setTime(21, 0), 40, 3000);  // coruja
        for ($i = 0; $i < 18; $i++) {
            $this->makeWalk($pet, now()->subDays(28 - $i)->setTime(12, 0), 30, 1100);
        }

        $codes = $this->unlocked((new PetStatsService)->forPet($pet));

        foreach (['first_walk', 'distance_3k', 'early_bird', 'night_owl', 'walks_20', 'total_25k'] as $code) {
            $this->assertContains($code, $codes, $code);
        }
    }
}
