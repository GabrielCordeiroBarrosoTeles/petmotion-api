<?php

namespace Tests;

use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\User;
use App\Models\Walk;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;

abstract class TestCase extends BaseTestCase
{
    protected function makeUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    protected function makeAdmin(): User
    {
        $admin = $this->makeUser(['email' => 'admin@petmotion.app']);
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }

    protected function makePet(User $user, array $attributes = []): Pet
    {
        return $user->pets()->create($attributes + [
            'name' => 'Thor',
            'species' => 'dog',
            'daily_goal_minutes' => 30,
        ]);
    }

    protected function makeWalker(Pet $pet): User
    {
        $walker = $this->makeUser();
        $pet->walkers()->attach($walker->id);

        return $walker;
    }

    protected function makeWalk(Pet $pet, Carbon $start, int $minutes = 30, int $meters = 2000, array $attributes = []): Walk
    {
        return $pet->user->walks()->create($attributes + [
            'pet_id' => $pet->id,
            'started_at' => $start,
            'ended_at' => $start->copy()->addMinutes($minutes),
            'duration_seconds' => $minutes * 60,
            'distance_meters' => $meters,
            'steps' => (int) ($meters * 1.3),
            'points' => [[-23.58, -46.65], [-23.581, -46.651]],
        ]);
    }

    protected function makeHealth(Pet $pet, array $attributes = []): HealthRecord
    {
        return $pet->healthRecords()->create($attributes + [
            'type' => 'vaccine',
            'title' => 'V10',
            'date' => now()->subMonths(11)->toDateString(),
            'next_date' => now()->addDays(3)->toDateString(),
        ]);
    }

    /** @return array<string, string> */
    protected function bearer(User $user): array
    {
        return ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];
    }
}
