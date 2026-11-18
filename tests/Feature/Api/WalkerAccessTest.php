<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WalkerAccessTest extends TestCase
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
            'points' => [[-23.58, -46.65], [-23.581, -46.651]],
        ];
    }

    public function test_walker_group_walk_with_pets_of_two_owners(): void
    {
        $ana = $this->makeUser(['name' => 'Ana Souza']);
        $carlos = $this->makeUser(['name' => 'Carlos Lima']);
        $thor = $this->makePet($ana);
        $mel = $this->makePet($ana, ['name' => 'Mel']);
        $bidu = $this->makePet($carlos, ['name' => 'Bidu']);
        $walker = $this->makeWalker($thor);
        $mel->walkers()->attach($walker->id);
        $bidu->walkers()->attach($walker->id);
        $stranger = $this->makePet($this->makeUser(), ['name' => 'Alheio']);

        $this->postJson('/api/walks', $this->payload($thor->id, ['companion_ids' => [$mel->id, $bidu->id]]), $this->bearer($walker))
            ->assertCreated();

        $this->assertDatabaseCount('walks', 3);
        $this->assertSame(1, \App\Models\Walk::distinct()->pluck('group_id')->count());
        $this->assertSame(3, \App\Models\Walk::where('user_id', $walker->id)->count());

        $this->postJson('/api/walks', $this->payload($thor->id, ['companion_ids' => [$stranger->id]]), $this->bearer($walker))
            ->assertNotFound();
        $this->assertDatabaseCount('walks', 3);
    }

    public function test_companions_only_lists_pets_the_viewer_can_see(): void
    {
        $ana = $this->makeUser();
        $carlos = $this->makeUser();
        $thor = $this->makePet($ana);
        $mel = $this->makePet($ana, ['name' => 'Mel']);
        $bidu = $this->makePet($carlos, ['name' => 'Bidu']);
        $walker = $this->makeWalker($thor);
        $mel->walkers()->attach($walker->id);
        $bidu->walkers()->attach($walker->id);

        $res = $this->postJson('/api/walks', $this->payload($thor->id, ['companion_ids' => [$mel->id, $bidu->id]]), $this->bearer($walker))
            ->assertCreated();
        // quem passeou vê os dois
        $this->assertCount(2, $res->json('companions'));

        $this->app['auth']->forgetGuards();
        $walkId = \App\Models\Walk::where('pet_id', $thor->id)->value('id');
        $this->getJson("/api/walks/{$walkId}", $this->bearer($ana))
            ->assertOk()
            ->assertJsonCount(1, 'companions')
            ->assertJsonPath('companions.0.name', 'Mel');
    }

    public function test_pets_index_lists_owned_and_walking_with_role(): void
    {
        $ana = $this->makeUser(['name' => 'Ana Souza']);
        $me = $this->makeUser();
        $own = $this->makePet($me, ['name' => 'Meu']);
        $shared = $this->makePet($ana, ['name' => 'Thor']);
        $shared->walkers()->attach($me->id);
        $this->makePet($this->makeUser(), ['name' => 'Alheio']);

        $res = $this->getJson('/api/pets', $this->bearer($me))->assertOk()->assertJsonCount(2);
        $byName = collect($res->json())->keyBy('name');
        $this->assertSame('owner', $byName['Meu']['role']);
        $this->assertArrayNotHasKey('owner_name', $byName['Meu']);
        $this->assertArrayNotHasKey('user', $byName['Meu']);
        $this->assertArrayNotHasKey('user', $byName['Thor']);
        $this->assertSame('walker', $byName['Thor']['role']);
        $this->assertSame('Ana', $byName['Thor']['owner_name']);

        $this->getJson("/api/pets/{$shared->id}", $this->bearer($me))
            ->assertOk()->assertJsonPath('role', 'walker')->assertJsonPath('owner_name', 'Ana')->assertJsonMissingPath('user');
        $this->getJson("/api/pets/{$own->id}", $this->bearer($me))
            ->assertOk()->assertJsonPath('role', 'owner')->assertJsonMissingPath('owner_name')->assertJsonMissingPath('user');
    }

    public function test_walker_can_walk_shared_pet(): void
    {
        $pet = $this->makePet($this->makeUser());
        $walker = $this->makeWalker($pet);

        $this->postJson('/api/walks', $this->payload($pet->id), $this->bearer($walker))->assertCreated();

        $this->assertDatabaseHas('walks', ['pet_id' => $pet->id, 'user_id' => $walker->id]);
    }

    public function test_removed_walker_cannot_start_new_walk(): void
    {
        $pet = $this->makePet($this->makeUser());
        $walker = $this->makeWalker($pet);
        $pet->walkers()->detach($walker->id);

        $this->postJson('/api/walks', $this->payload($pet->id), $this->bearer($walker))->assertNotFound();
        $this->getJson("/api/walks?pet_id={$pet->id}", $this->bearer($walker))->assertNotFound();
        $this->assertDatabaseCount('walks', 0);
    }

    public function test_owner_sees_all_walks_of_pet_walker_sees_only_his(): void
    {
        $owner = $this->makeUser();
        $pet = $this->makePet($owner);
        $walker = $this->makeWalker($pet);
        $other = $this->makeWalker($pet);
        $this->makeWalk($pet, Carbon::parse('2026-11-10 10:00'));
        $mine = $this->makeWalk($pet, Carbon::parse('2026-11-11 10:00'));
        $mine->forceFill(['user_id' => $walker->id])->save();
        $theirs = $this->makeWalk($pet, Carbon::parse('2026-11-12 10:00'));
        $theirs->forceFill(['user_id' => $other->id])->save();

        $this->getJson("/api/walks?pet_id={$pet->id}", $this->bearer($owner))->assertOk()->assertJsonCount(3, 'data');

        $this->app['auth']->forgetGuards();
        $this->getJson("/api/walks?pet_id={$pet->id}", $this->bearer($walker))
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $mine->id);

        // sem pet_id: só os passeios de quem pergunta
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/walks', $this->bearer($walker))->assertJsonCount(1, 'data');

        // show/destroy: dono do pet ou quem passeou
        $this->app['auth']->forgetGuards();
        $this->getJson("/api/walks/{$theirs->id}", $this->bearer($walker))->assertNotFound();
        $this->app['auth']->forgetGuards();
        $this->deleteJson("/api/walks/{$theirs->id}", [], $this->bearer($walker))->assertNotFound();
        $this->app['auth']->forgetGuards();
        $this->getJson("/api/walks/{$theirs->id}", $this->bearer($owner))->assertOk();
        $this->app['auth']->forgetGuards();
        $this->deleteJson("/api/walks/{$mine->id}", [], $this->bearer($walker))->assertNoContent();
        $this->app['auth']->forgetGuards();
        $this->deleteJson("/api/walks/{$theirs->id}", [], $this->bearer($owner))->assertNoContent();
    }

    public function test_walker_cannot_see_health_or_edit_pet(): void
    {
        $pet = $this->makePet($this->makeUser());
        $record = $this->makeHealth($pet);
        $walker = $this->makeWalker($pet);
        $h = $this->bearer($walker);

        $this->getJson("/api/pets/{$pet->id}/health-records", $h)->assertNotFound();
        $this->postJson("/api/pets/{$pet->id}/health-records", ['type' => 'vaccine', 'title' => 'X', 'date' => '2026-11-01'], $h)->assertNotFound();
        $this->deleteJson("/api/health-records/{$record->id}", [], $h)->assertNotFound();
        $this->putJson("/api/pets/{$pet->id}", ['name' => 'Novo'], $h)->assertNotFound();
        $this->deleteJson("/api/pets/{$pet->id}", [], $h)->assertNotFound();
        $this->assertDatabaseHas('pets', ['id' => $pet->id, 'name' => 'Thor']);
    }

    public function test_walker_stats_hide_upcoming_care(): void
    {
        $pet = $this->makePet($this->makeUser());
        $this->makeHealth($pet);
        $walker = $this->makeWalker($pet);

        $this->getJson("/api/pets/{$pet->id}/stats", $this->bearer($walker))
            ->assertOk()
            ->assertJsonStructure(['today', 'week', 'totals'])
            ->assertJsonCount(0, 'upcoming_health');
    }

    public function test_walk_json_has_walker_name(): void
    {
        $owner = $this->makeUser();
        $pet = $this->makePet($owner);
        $walker = $this->makeWalker($pet);
        $walker->forceFill(['name' => 'Paulo Passeador'])->save();

        $this->postJson('/api/walks', $this->payload($pet->id), $this->bearer($walker))
            ->assertCreated()
            ->assertJsonPath('walker', ['id' => $walker->id, 'name' => 'Paulo Passeador']);

        $this->app['auth']->forgetGuards();
        $this->getJson("/api/walks?pet_id={$pet->id}", $this->bearer($owner))
            ->assertJsonPath('data.0.walker.name', 'Paulo Passeador');
    }

    public function test_removed_walker_loses_sight_of_his_past_walks(): void
    {
        $owner = $this->makeUser();
        $pet = $this->makePet($owner);
        $walker = $this->makeWalker($pet);
        $walkId = $this->postJson('/api/walks', $this->payload($pet->id), $this->bearer($walker))->assertCreated()->json('id');
        $this->app['auth']->forgetGuards();
        $this->getJson("/api/walks/{$walkId}", $this->bearer($walker))
            ->assertOk()->assertJsonPath('walker.id', $walker->id);

        $pet->walkers()->detach($walker->id);

        $this->app['auth']->forgetGuards();
        $this->getJson("/api/walks/{$walkId}", $this->bearer($walker))->assertNotFound();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/walks', $this->bearer($walker))->assertJsonCount(0, 'data');
        $this->app['auth']->forgetGuards();
        $this->deleteJson("/api/walks/{$walkId}", [], $this->bearer($walker))->assertNotFound();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/walks', $this->bearer($owner))->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $walkId);
        $this->getJson("/api/walks?pet_id={$pet->id}", $this->bearer($owner))->assertJsonCount(1, 'data');
        $this->getJson("/api/walks/{$walkId}", $this->bearer($owner))->assertOk();
    }
}
