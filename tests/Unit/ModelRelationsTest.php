<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_relations_connect_user_pet_walk_event_and_health(): void
    {
        $user = $this->makeUser();
        $pet = $this->makePet($user);
        $walk = $this->makeWalk($pet, now()->subHour());
        $event = $walk->events()->create(['type' => 'water', 'lat' => -23.5, 'lng' => -46.6, 'occurred_at' => now()]);
        $record = $this->makeHealth($pet);

        $this->assertTrue($event->walk->is($walk));
        $this->assertTrue($walk->pet->is($pet));
        $this->assertTrue($walk->user->is($user));
        $this->assertTrue($pet->user->is($user));
        $this->assertTrue($record->pet->is($pet));
        $this->assertCount(1, $user->walks);
        $this->assertCount(1, $pet->walks);
        $this->assertSame(-23.5, $event->lat);
        $this->assertSame([[-23.58, -46.65], [-23.581, -46.651]], $walk->points);
    }
}
