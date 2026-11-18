<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalkTest extends TestCase
{
    use RefreshDatabase;

    private function payload(int $petId, array $overrides = []): array
    {
        return $overrides + [
            'pet_id' => $petId,
            'started_at' => '2026-11-17T20:00:00Z',
            'ended_at' => '2026-11-17T20:10:00Z',
            'duration_seconds' => 600,
            'distance_meters' => 700,
            'steps' => 900,
            'temperature_c' => 22.5,
            'points' => [[-23.58, -46.65], [-23.581, -46.651]],
            'events' => [['type' => 'pee', 'lat' => -23.58, 'lng' => -46.65, 'occurred_at' => '2026-11-17T20:05:00Z']],
        ];
    }

    public function test_store_saves_walk_with_events_converting_utc_to_local_time(): void
    {
        $me = $this->makeUser();
        $pet = $this->makePet($me);

        $this->postJson('/api/walks', $this->payload($pet->id), $this->bearer($me))
            ->assertCreated()
            ->assertJsonPath('events.0.type', 'pee')
            ->assertJsonPath('pet.name', 'Thor')
            ->assertJsonPath('started_at', '2026-11-17T20:00:00.000000Z');

        // 20:00 UTC = 17:00 em São Paulo: é assim que fica no banco
        $this->assertDatabaseHas('walks', ['pet_id' => $pet->id, 'started_at' => '2026-11-17 17:00:00']);
        $this->assertDatabaseHas('walk_events', ['type' => 'pee', 'occurred_at' => '2026-11-17 17:05:00']);
    }

    public function test_store_without_events(): void
    {
        $me = $this->makeUser();
        $pet = $this->makePet($me);
        $data = $this->payload($pet->id);
        unset($data['events']);

        $this->postJson('/api/walks', $data, $this->bearer($me))->assertCreated()->assertJsonCount(0, 'events');
    }

    public function test_store_rejects_pet_of_another_user(): void
    {
        $pet = $this->makePet($this->makeUser());

        $this->postJson('/api/walks', $this->payload($pet->id), $this->bearer($this->makeUser()))->assertNotFound();
        $this->assertDatabaseCount('walks', 0);
    }

    public function test_store_group_walk_saves_one_walk_per_pet_with_same_route_and_events(): void
    {
        $me = $this->makeUser();
        $thor = $this->makePet($me);
        $mel = $this->makePet($me, ['name' => 'Mel']);

        $res = $this->postJson('/api/walks', $this->payload($thor->id, ['companion_ids' => [$mel->id, $thor->id]]), $this->bearer($me))
            ->assertCreated()
            ->assertJsonPath('pet.name', 'Thor')
            ->assertJsonCount(1, 'companions')
            ->assertJsonPath('companions.0.name', 'Mel');

        $this->assertDatabaseCount('walks', 2);
        $this->assertDatabaseCount('walk_events', 2);
        $melWalk = $mel->walks()->sole();
        $this->assertSame($res->json('group_id'), $melWalk->group_id);
        $this->assertSame(700, $melWalk->distance_meters);

        // o detalhe do passeio da Mel mostra o Thor como companheiro
        $this->getJson("/api/walks/{$melWalk->id}", $this->bearer($me))->assertJsonPath('companions.0.name', 'Thor');
    }

    public function test_store_alone_has_no_group_nor_companions(): void
    {
        $me = $this->makeUser();
        $pet = $this->makePet($me);

        $this->postJson('/api/walks', $this->payload($pet->id, ['companion_ids' => []]), $this->bearer($me))
            ->assertCreated()
            ->assertJsonPath('group_id', null)
            ->assertJsonCount(0, 'companions')
            ->assertJsonPath('group_walks', [['pet_id' => $pet->id, 'walk_id' => $pet->walks()->sole()->id]]);
    }

    public function test_store_group_returns_the_walk_of_each_pet_main_first(): void
    {
        $me = $this->makeUser();
        $thor = $this->makePet($me);
        $mel = $this->makePet($me, ['name' => 'Mel']);
        $bidu = $this->makePet($me, ['name' => 'Bidu']);

        $res = $this->postJson('/api/walks', $this->payload($mel->id, ['companion_ids' => [$bidu->id, $thor->id]]), $this->bearer($me))
            ->assertCreated()
            ->assertJsonCount(3, 'group_walks');

        $this->assertSame([
            ['pet_id' => $mel->id, 'walk_id' => $mel->walks()->sole()->id],
            ['pet_id' => $bidu->id, 'walk_id' => $bidu->walks()->sole()->id],
            ['pet_id' => $thor->id, 'walk_id' => $thor->walks()->sole()->id],
        ], $res->json('group_walks'));
        $this->assertSame($res->json('id'), $res->json('group_walks.0.walk_id'));
    }

    public function test_store_group_rejects_companion_of_another_user(): void
    {
        $me = $this->makeUser();
        $pet = $this->makePet($me);
        $other = $this->makePet($this->makeUser(), ['name' => 'Alheio']);

        $this->postJson('/api/walks', $this->payload($pet->id, ['companion_ids' => [$other->id]]), $this->bearer($me))->assertNotFound();
        $this->assertDatabaseCount('walks', 0);
    }

    public function test_store_validates_payload(): void
    {
        $me = $this->makeUser();
        $pet = $this->makePet($me);

        $this->postJson('/api/walks', $this->payload($pet->id, [
            'ended_at' => '2026-11-17T19:00:00Z',
            'events' => [['type' => 'latido', 'lat' => 0, 'lng' => 0, 'occurred_at' => '2026-11-17T20:05:00Z']],
        ]), $this->bearer($me))->assertUnprocessable()->assertJsonValidationErrors(['ended_at', 'events.0.type']);
    }

    public function test_index_paginates_and_filters_by_pet(): void
    {
        $me = $this->makeUser();
        $thor = $this->makePet($me);
        $mel = $this->makePet($me, ['name' => 'Mel']);
        $this->makeWalk($thor, now()->subDay());
        $this->makeWalk($thor, now()->subHours(2));
        $this->makeWalk($mel, now()->subHour());
        $this->makeWalk($this->makePet($this->makeUser()), now());

        $this->getJson('/api/walks', $this->bearer($me))->assertOk()->assertJsonPath('total', 3);
        $this->getJson("/api/walks?pet_id={$thor->id}", $this->bearer($me))
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('data.0.pet.name', 'Thor');
    }

    public function test_show_and_destroy_only_my_walks(): void
    {
        $me = $this->makeUser();
        $mine = $this->makeWalk($this->makePet($me), now()->subHour());
        $theirs = $this->makeWalk($this->makePet($this->makeUser()), now()->subHour());
        $headers = $this->bearer($me);

        $this->getJson("/api/walks/{$mine->id}", $headers)->assertOk()->assertJsonPath('id', $mine->id);
        $this->getJson("/api/walks/{$theirs->id}", $headers)->assertNotFound();
        $this->deleteJson("/api/walks/{$theirs->id}", [], $headers)->assertNotFound();
        $this->deleteJson("/api/walks/{$mine->id}", [], $headers)->assertNoContent();
        $this->assertDatabaseMissing('walks', ['id' => $mine->id]);
    }
}
