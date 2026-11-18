<?php

namespace Tests\Feature\Api;

use App\Models\Pet;
use App\Models\PetInvite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InviteTest extends TestCase
{
    use RefreshDatabase;

    private function invite(Pet $pet, array $attributes = []): PetInvite
    {
        return PetInvite::create($attributes + [
            'pet_id' => $pet->id,
            'code' => PetInvite::generateCode(),
            'created_by' => $pet->user_id,
            'expires_at' => now()->addHours(48),
        ]);
    }

    public function test_owner_creates_invite_with_48h_code(): void
    {
        Carbon::setTestNow('2026-11-18 10:00:00');
        $owner = $this->makeUser();
        $pet = $this->makePet($owner);

        $res = $this->postJson("/api/pets/{$pet->id}/invites", [], $this->bearer($owner))->assertCreated();

        $code = $res->json('code');
        $this->assertMatchesRegularExpression('/^[A-HJ-NP-Z2-9]{8}$/', $code);
        $this->assertSame(config('app.url').'/convite/'.$code, $res->json('url'));
        $this->assertTrue(Carbon::parse($res->json('expires_at'))->equalTo(now()->addHours(48)));
        $this->assertDatabaseHas('pet_invites', ['code' => $code, 'pet_id' => $pet->id, 'created_by' => $owner->id]);
    }

    public function test_non_owner_cannot_create_invite(): void
    {
        $pet = $this->makePet($this->makeUser());
        $walker = $this->makeWalker($pet);

        $this->postJson("/api/pets/{$pet->id}/invites", [], $this->bearer($walker))->assertNotFound();
        $this->app['auth']->forgetGuards();
        $this->postJson("/api/pets/{$pet->id}/invites", [], $this->bearer($this->makeUser()))->assertNotFound();
    }

    public function test_preview_shows_pet_and_owner_name(): void
    {
        $owner = $this->makeUser(['name' => 'Gabriel Cordeiro']);
        $pet = $this->makePet($owner);
        $invite = $this->invite($pet);

        $this->getJson("/api/invites/{$invite->code}", $this->bearer($this->makeUser()))
            ->assertOk()
            ->assertJsonPath('code', $invite->code)
            ->assertJsonPath('pet.id', $pet->id)
            ->assertJsonPath('pet.name', 'Thor')
            ->assertJsonPath('pet.species', 'dog')
            ->assertJsonPath('owner.name', 'Gabriel')
            ->assertJsonStructure(['expires_at', 'pet' => ['photo_url']]);
    }

    public function test_preview_rejects_expired_used_unknown(): void
    {
        $viewer = $this->makeUser();
        $pet = $this->makePet($this->makeUser());
        $expired = $this->invite($pet, ['expires_at' => now()->subMinute()]);
        $used = $this->invite($pet, ['accepted_by' => $viewer->id, 'accepted_at' => now()]);

        $this->getJson("/api/invites/{$expired->code}", $this->bearer($viewer))->assertNotFound();
        $this->getJson("/api/invites/{$used->code}", $this->bearer($viewer))->assertNotFound();
        $this->getJson('/api/invites/NAOEXISTE', $this->bearer($viewer))->assertNotFound();
        $this->postJson("/api/invites/{$expired->code}/accept", [], $this->bearer($viewer))->assertNotFound();
    }

    public function test_owner_opening_own_invite_gets_own_pet_error(): void
    {
        $owner = $this->makeUser();
        $invite = $this->invite($this->makePet($owner));

        $this->getJson("/api/invites/{$invite->code}", $this->bearer($owner))
            ->assertStatus(422)->assertJsonPath('error', 'own_pet');
        $this->postJson("/api/invites/{$invite->code}/accept", [], $this->bearer($owner))
            ->assertStatus(422)->assertJsonPath('error', 'own_pet');
        $this->assertNull($invite->fresh()->accepted_at);
    }

    public function test_accept_makes_user_walker_and_burns_code(): void
    {
        $pet = $this->makePet($this->makeUser());
        $invite = $this->invite($pet);
        $guest = $this->makeUser();

        $this->postJson("/api/invites/{$invite->code}/accept", [], $this->bearer($guest))
            ->assertOk()
            ->assertJsonPath('id', $pet->id)
            ->assertJsonPath('role', 'walker');

        $this->assertTrue($pet->canBeWalkedBy($guest));
        $this->assertFalse($pet->isOwnedBy($guest));
        $this->assertSame([$pet->id], $guest->walkingPets()->pluck('pets.id')->all());
        $this->assertSame($guest->id, $invite->fresh()->accepted_by);

        $this->app['auth']->forgetGuards(); // o guard guarda o usuário da requisição anterior
        $this->postJson("/api/invites/{$invite->code}/accept", [], $this->bearer($this->makeUser()))->assertNotFound();
    }

    public function test_accept_returns_pet_without_owner_data(): void
    {
        $owner = $this->makeUser(['name' => 'Ana Souza Lima', 'email' => 'ana.dona@exemplo.com']);
        $pet = $this->makePet($owner);
        $guest = $this->makeUser();

        $code = $this->invite($pet)->code;
        $first = $this->postJson("/api/invites/{$code}/accept", [], $this->bearer($guest));
        // repetir o aceite (toque duplo) devolve o mesmo formato
        $again = $this->postJson("/api/invites/{$code}/accept", [], $this->bearer($guest));

        foreach ([$first, $again] as $response) {
            $response->assertOk()
                ->assertJsonMissingPath('user')
                ->assertJsonPath('role', 'walker')
                ->assertJsonPath('owner_name', 'Ana');
            $this->assertStringNotContainsString('ana.dona@exemplo.com', $response->getContent());
            $this->assertStringNotContainsString('Souza', $response->getContent());
        }
    }

    public function test_accept_twice_by_same_walker_is_idempotent(): void
    {
        $pet = $this->makePet($this->makeUser());
        $invite = $this->invite($pet);
        $guest = $this->makeUser();
        $this->postJson("/api/invites/{$invite->code}/accept", [], $this->bearer($guest))->assertOk();

        $this->postJson("/api/invites/{$invite->code}/accept", [], $this->bearer($guest))
            ->assertOk()->assertJsonPath('role', 'walker');

        $this->assertSame(1, $pet->walkers()->count());
    }

    public function test_already_walker_accepting_new_invite_keeps_single_row(): void
    {
        $pet = $this->makePet($this->makeUser());
        $walker = $this->makeWalker($pet);
        $invite = $this->invite($pet);

        $this->postJson("/api/invites/{$invite->code}/accept", [], $this->bearer($walker))->assertOk();

        $this->assertSame(1, $pet->walkers()->count());
    }

    public function test_owner_lists_and_removes_walkers(): void
    {
        $owner = $this->makeUser();
        $pet = $this->makePet($owner);
        $walker = $this->makeWalker($pet);

        $this->getJson("/api/pets/{$pet->id}/walkers", $this->bearer($owner))
            ->assertOk()->assertExactJson([['id' => $walker->id, 'name' => $walker->name]]);

        $this->deleteJson("/api/pets/{$pet->id}/walkers/{$walker->id}", [], $this->bearer($owner))->assertNoContent();

        $this->getJson("/api/pets/{$pet->id}/walkers", $this->bearer($owner))->assertOk()->assertExactJson([]);
        $this->assertFalse($pet->canBeWalkedBy($walker));
    }

    public function test_walker_cannot_list_or_remove_walkers(): void
    {
        $pet = $this->makePet($this->makeUser());
        $walker = $this->makeWalker($pet);

        $this->getJson("/api/pets/{$pet->id}/walkers", $this->bearer($walker))->assertNotFound();
        $this->deleteJson("/api/pets/{$pet->id}/walkers/{$walker->id}", [], $this->bearer($walker))->assertNotFound();
        $this->assertTrue($pet->canBeWalkedBy($walker));
    }

    public function test_accessible_by_scope_returns_owned_and_walked_pets(): void
    {
        $me = $this->makeUser();
        $mine = $this->makePet($me);
        $other = $this->makeUser();
        $walked = $this->makePet($other, ['name' => 'Mel']);
        $walked->walkers()->attach($me->id);
        $this->makePet($other, ['name' => 'Rex']);

        $ids = Pet::accessibleBy($me)->pluck('id')->sort()->values()->all();

        $this->assertSame([$mine->id, $walked->id], $ids);
        $this->assertTrue($mine->isOwnedBy($me));
        $this->assertTrue($mine->canBeWalkedBy($me));
    }

    public function test_invite_burned_between_read_and_claim_gives_404_and_single_walker(): void
    {
        $pet = $this->makePet($this->makeUser());
        $invite = $this->invite($pet);
        $first = $this->makeUser();
        $second = $this->makeUser();

        // simula o outro aceite vencendo a corrida logo depois da leitura do convite
        PetInvite::retrieved(function (PetInvite $i) use ($first) {
            DB::table('pet_invites')->where('id', $i->id)
                ->update(['accepted_by' => $first->id, 'accepted_at' => now()]);
            $i->pet->walkers()->syncWithoutDetaching([$first->id]);
        });

        $this->postJson("/api/invites/{$invite->code}/accept", [], $this->bearer($second))->assertNotFound();

        $this->assertSame([$first->id], $pet->walkers()->pluck('users.id')->all());
        $this->assertSame($first->id, $invite->fresh()->accepted_by);
    }
}
