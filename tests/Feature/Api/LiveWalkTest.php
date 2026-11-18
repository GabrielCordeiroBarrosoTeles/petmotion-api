<?php

namespace Tests\Feature\Api;

use App\Events\LiveWalkUpdated;
use App\Models\Pet;
use App\Models\User;
use App\Services\LiveWalkStore;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

class LiveWalkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([LiveWalkUpdated::class]);
    }

    private function as(User $user): array
    {
        $this->app['auth']->forgetGuards();

        return $this->bearer($user);
    }

    private function start(User $walker, array $petIds): string
    {
        return $this->postJson('/api/live/start', ['pet_ids' => $petIds], $this->as($walker))
            ->assertCreated()
            ->json('session');
    }

    private function progress(array $overrides = []): array
    {
        return $overrides + [
            'status' => 'running',
            'elapsed_ms' => 60000,
            'distance' => 120.5,
            'steps' => 160,
            'points' => [[-23.58, -46.65], [-23.581, -46.651]],
            'counts' => ['pee' => 1, 'poop' => 0, 'water' => 2],
        ];
    }

    /** @return array<int, array<string, mixed>> payloads transmitidos no canal do pet */
    private function broadcastsFor(Pet $pet): array
    {
        return Event::dispatched(LiveWalkUpdated::class)
            ->map(fn (array $args) => $args[0])
            ->filter(fn (LiveWalkUpdated $e) => $e->broadcastOn() == new PrivateChannel('pet.'.$pet->id))
            ->map(fn (LiveWalkUpdated $e) => $e->broadcastWith())
            ->values()
            ->all();
    }

    private function walkOf(Pet $pet, User $walker): int
    {
        return $walker->walks()->create([
            'pet_id' => $pet->id,
            'started_at' => now()->subMinutes(10),
            'ended_at' => now(),
            'duration_seconds' => 600,
            'distance_meters' => 700,
            'points' => [[-23.58, -46.65]],
        ])->id;
    }

    public function test_walker_starts_live_and_owner_reads_it(): void
    {
        Carbon::setTestNow('2026-11-18 18:00:00');
        $ana = $this->makeUser();
        $thor = $this->makePet($ana);
        $walker = $this->makeWalker($thor);
        $walker->update(['name' => 'Bruno Passeador']);

        $session = $this->start($walker, [$thor->id]);
        $this->assertTrue(Str::isUuid($session));

        $state = $this->getJson("/api/pets/{$thor->id}/live", $this->as($ana))->assertOk()->json();
        $this->assertSame($session, $state['session']);
        $this->assertSame('running', $state['status']);
        $this->assertSame(['id' => $walker->id, 'name' => 'Bruno Passeador'], $state['walker']);
        $this->assertSame($thor->id, $state['pet_id']);
        $this->assertSame(['Thor'], $state['pet_names']);
        $this->assertSame(0, $state['elapsed_ms']);
        $this->assertSame(0, $state['distance']);
        $this->assertSame(0, $state['steps']);
        $this->assertSame([], $state['points']);
        $this->assertSame(['pee' => 0, 'poop' => 0, 'water' => 0], $state['counts']);
        $this->assertSame(now()->toIso8601String(), $state['started_at']);
        $this->assertSame(now()->toIso8601String(), $state['at']);

        $sent = $this->broadcastsFor($thor);
        $this->assertCount(1, $sent);
        $this->assertSame('running', $sent[0]['status']);
        $this->assertSame($session, $sent[0]['session']);

        $event = Event::dispatched(LiveWalkUpdated::class)->first()[0];
        $this->assertSame('live.updated', $event->broadcastAs());

        // dono sem passeio em andamento: 204; o passeador não lê pelo endpoint do dono
        $mel = $this->makePet($ana, ['name' => 'Mel']);
        $this->getJson("/api/pets/{$mel->id}/live", $this->as($ana))->assertNoContent();
        $this->getJson("/api/pets/{$thor->id}/live", $this->as($walker))->assertNotFound();

        // estado some depois de 3 h sem atualização
        Carbon::setTestNow(now()->addHours(3)->addSecond());
        $this->getJson("/api/pets/{$thor->id}/live", $this->as($ana))->assertNoContent();
    }

    public function test_start_validates_input(): void
    {
        $ana = $this->makeUser();
        $this->makePet($ana);

        $this->postJson('/api/live/start', ['pet_ids' => []], $this->as($ana))
            ->assertUnprocessable()->assertJsonValidationErrors('pet_ids');
    }

    public function test_progress_appends_points_and_broadcasts_new_ones(): void
    {
        $ana = $this->makeUser();
        $thor = $this->makePet($ana);
        $walker = $this->makeWalker($thor);
        $session = $this->start($walker, [$thor->id]);

        $this->postJson("/api/live/{$session}/progress", $this->progress(['pet_ids' => [$thor->id]]), $this->as($walker))
            ->assertNoContent();
        $this->postJson("/api/live/{$session}/progress", $this->progress([
            'pet_ids' => [$thor->id],
            'status' => 'paused',
            'elapsed_ms' => 90000,
            'points' => [[-23.582, -46.652]],
        ]), $this->as($walker))->assertNoContent();

        $state = $this->getJson("/api/pets/{$thor->id}/live", $this->as($ana))->assertOk()->json();
        $this->assertSame('paused', $state['status']);
        $this->assertSame(90000, $state['elapsed_ms']);
        $this->assertEquals(120.5, $state['distance']);
        $this->assertSame(160, $state['steps']);
        $this->assertSame(['pee' => 1, 'poop' => 0, 'water' => 2], $state['counts']);
        $this->assertEquals([[-23.58, -46.65], [-23.581, -46.651], [-23.582, -46.652]], $state['points']);

        $sent = $this->broadcastsFor($thor);
        $this->assertCount(3, $sent);
        $this->assertArrayNotHasKey('points', $sent[2]);
        $this->assertEquals([[-23.582, -46.652]], $sent[2]['new_points']);
        $this->assertSame('paused', $sent[2]['status']);
        $this->assertSame(90000, $sent[2]['elapsed_ms']);

        Event::assertDispatched(LiveWalkUpdated::class, fn (LiveWalkUpdated $e) => ($e->broadcastWith()['new_points'] ?? null) == [[-23.58, -46.65], [-23.581, -46.651]]);

        $this->postJson("/api/live/{$session}/progress", $this->progress(['pet_ids' => [$thor->id], 'status' => 'flying']), $this->as($walker))
            ->assertUnprocessable()->assertJsonValidationErrors('status');
    }

    public function test_progress_keeps_only_the_latest_2000_points(): void
    {
        $ana = $this->makeUser();
        $thor = $this->makePet($ana);
        $session = $this->start($ana, [$thor->id]);

        $batch = fn (int $from) => array_map(fn ($i) => [-23 - $i / 100000, -46.0], range($from, $from + 1499));
        $this->postJson("/api/live/{$session}/progress", $this->progress(['pet_ids' => [$thor->id], 'points' => $batch(0)]), $this->as($ana))
            ->assertNoContent();
        $this->postJson("/api/live/{$session}/progress", $this->progress(['pet_ids' => [$thor->id], 'points' => $batch(1500)]), $this->as($ana))
            ->assertNoContent();

        $points = app(LiveWalkStore::class)->get($thor->id)['points'];
        $this->assertCount(2000, $points);
        $this->assertEquals([-23 - 1000 / 100000, -46.0], $points[0]);
        $this->assertEquals([-23 - 2999 / 100000, -46.0], $points[1999]);
    }

    public function test_finish_keeps_walk_id_for_owner(): void
    {
        $ana = $this->makeUser();
        $thor = $this->makePet($ana);
        $walker = $this->makeWalker($thor);
        $session = $this->start($walker, [$thor->id]);
        $walkId = $this->walkOf($thor, $walker);

        // passeio que não é deste pet/passeador não pode ser apontado ao dono
        $otherWalk = $this->walkOf($this->makePet($ana, ['name' => 'Mel']), $ana);
        $this->postJson("/api/live/{$session}/finish", ['walks' => [['pet_id' => $thor->id, 'walk_id' => $otherWalk]]], $this->as($walker))
            ->assertNotFound();

        $this->postJson("/api/live/{$session}/finish", ['walks' => [['pet_id' => $thor->id, 'walk_id' => $walkId]]], $this->as($walker))
            ->assertNoContent();

        $state = $this->getJson("/api/pets/{$thor->id}/live", $this->as($ana))->assertOk()->json();
        $this->assertSame('finished', $state['status']);
        $this->assertSame($walkId, $state['walk_id']);

        $sent = $this->broadcastsFor($thor);
        $this->assertSame('finished', end($sent)['status']);
        $this->assertSame($walkId, end($sent)['walk_id']);

        // finalizado fica 10 min
        Carbon::setTestNow(now()->addMinutes(9));
        $this->getJson("/api/pets/{$thor->id}/live", $this->as($ana))->assertOk();
        Carbon::setTestNow(now()->addMinutes(2));
        $this->getJson("/api/pets/{$thor->id}/live", $this->as($ana))->assertNoContent();
    }

    public function test_cancel_clears_state(): void
    {
        $ana = $this->makeUser();
        $thor = $this->makePet($ana);
        $walker = $this->makeWalker($thor);
        $session = $this->start($walker, [$thor->id]);

        $this->postJson("/api/live/{$session}/cancel", ['pet_ids' => [$thor->id]], $this->as($walker))->assertNoContent();

        $this->getJson("/api/pets/{$thor->id}/live", $this->as($ana))->assertNoContent();
        $sent = $this->broadcastsFor($thor);
        $this->assertSame(['session' => $session, 'pet_id' => $thor->id, 'status' => 'cancelled'], end($sent));
    }

    public function test_stale_session_gets_409(): void
    {
        $ana = $this->makeUser();
        $thor = $this->makePet($ana);
        $walker = $this->makeWalker($thor);
        $old = $this->start($walker, [$thor->id]);
        $new = $this->start($ana, [$thor->id]);

        $this->postJson("/api/live/{$old}/progress", $this->progress(['pet_ids' => [$thor->id]]), $this->as($walker))
            ->assertStatus(409);
        $this->postJson("/api/live/{$old}/finish", ['walks' => [['pet_id' => $thor->id, 'walk_id' => $this->walkOf($thor, $walker)]]], $this->as($walker))
            ->assertStatus(409);
        $this->postJson("/api/live/{$old}/cancel", ['pet_ids' => [$thor->id]], $this->as($walker))
            ->assertStatus(409);

        $this->assertSame($new, app(LiveWalkStore::class)->get($thor->id)['session']);
        $this->assertSame('running', app(LiveWalkStore::class)->get($thor->id)['status']);

        // sem passeio ao vivo também não há sessão atual
        $mel = $this->makePet($ana, ['name' => 'Mel']);
        $this->postJson("/api/live/{$new}/progress", $this->progress(['pet_ids' => [$mel->id]]), $this->as($ana))
            ->assertStatus(409);
    }

    public function test_group_session_broadcasts_each_owner_only_their_pets(): void
    {
        $ana = $this->makeUser(['name' => 'Ana Souza']);
        $carlos = $this->makeUser(['name' => 'Carlos Lima']);
        $thor = $this->makePet($ana);
        $mel = $this->makePet($ana, ['name' => 'Mel']);
        $bidu = $this->makePet($carlos, ['name' => 'Bidu']);
        $walker = $this->makeWalker($thor);
        $mel->walkers()->attach($walker->id);
        $bidu->walkers()->attach($walker->id);

        $session = $this->start($walker, [$thor->id, $mel->id, $bidu->id]);
        $this->postJson("/api/live/{$session}/progress", $this->progress(['pet_ids' => [$thor->id, $mel->id, $bidu->id]]), $this->as($walker))
            ->assertNoContent();
        $this->postJson("/api/live/{$session}/finish", ['walks' => [
            ['pet_id' => $thor->id, 'walk_id' => $thorWalk = $this->walkOf($thor, $walker)],
            ['pet_id' => $mel->id, 'walk_id' => $melWalk = $this->walkOf($mel, $walker)],
            ['pet_id' => $bidu->id, 'walk_id' => $biduWalk = $this->walkOf($bidu, $walker)],
        ]], $this->as($walker))->assertNoContent();

        foreach ([$thor, $mel] as $pet) {
            $sent = $this->broadcastsFor($pet);
            $this->assertCount(3, $sent);
            $json = json_encode($sent);
            $this->assertStringNotContainsString('Bidu', $json);
            $this->assertStringNotContainsString('Carlos', $json);
            $this->assertSame(['Thor', 'Mel'], $sent[0]['pet_names']);
        }
        $biduSent = $this->broadcastsFor($bidu);
        $this->assertCount(3, $biduSent);
        $json = json_encode($biduSent);
        $this->assertStringNotContainsString('Thor', $json);
        $this->assertStringNotContainsString('Mel', $json);
        $this->assertStringNotContainsString('Ana', $json);
        $this->assertSame(['Bidu'], $biduSent[0]['pet_names']);

        $this->assertSame($thorWalk, $this->broadcastsFor($thor)[2]['walk_id']);
        $this->assertSame($melWalk, $this->broadcastsFor($mel)[2]['walk_id']);
        $this->assertSame($biduWalk, $biduSent[2]['walk_id']);

        $this->assertSame(['Bidu'], $this->getJson("/api/pets/{$bidu->id}/live", $this->as($carlos))->json('pet_names'));
    }

    public function test_stranger_gets_404_everywhere(): void
    {
        $ana = $this->makeUser();
        $thor = $this->makePet($ana);
        $walker = $this->makeWalker($thor);
        $stranger = $this->makeUser();
        $session = $this->start($walker, [$thor->id]);
        $walkId = $this->walkOf($thor, $walker);

        $this->postJson('/api/live/start', ['pet_ids' => [$thor->id]], $this->as($stranger))->assertNotFound();
        $this->postJson("/api/live/{$session}/progress", $this->progress(['pet_ids' => [$thor->id]]), $this->as($stranger))->assertNotFound();
        $this->postJson("/api/live/{$session}/finish", ['walks' => [['pet_id' => $thor->id, 'walk_id' => $walkId]]], $this->as($stranger))->assertNotFound();
        $this->postJson("/api/live/{$session}/cancel", ['pet_ids' => [$thor->id]], $this->as($stranger))->assertNotFound();
        $this->getJson("/api/pets/{$thor->id}/live", $this->as($stranger))->assertNotFound();

        // nada foi transmitido nem alterado pelo estranho
        $this->assertCount(1, $this->broadcastsFor($thor));
        $this->assertSame('running', app(LiveWalkStore::class)->get($thor->id)['status']);
        $this->app['auth']->forgetGuards();
        $this->postJson('/api/live/start', ['pet_ids' => [$thor->id]])->assertUnauthorized();
    }

    public function test_channel_authorizes_only_owner(): void
    {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => 'test-app',
        ]);
        require base_path('routes/channels.php');

        $ana = $this->makeUser();
        $thor = $this->makePet($ana);
        $walker = $this->makeWalker($thor);
        $stranger = $this->makeUser();
        $body = ['socket_id' => '1234.5678', 'channel_name' => 'private-pet.'.$thor->id];

        $this->postJson('/api/broadcasting/auth', $body, $this->as($ana))
            ->assertOk()->assertJsonStructure(['auth']);
        $this->postJson('/api/broadcasting/auth', $body, $this->as($walker))->assertForbidden();
        $this->postJson('/api/broadcasting/auth', $body, $this->as($stranger))->assertForbidden();
        $this->postJson('/api/broadcasting/auth', ['channel_name' => 'private-pet.999999'] + $body, $this->as($ana))->assertForbidden();
    }
}
