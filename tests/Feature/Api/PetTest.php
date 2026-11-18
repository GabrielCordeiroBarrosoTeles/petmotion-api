<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PetTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_my_pets(): void
    {
        $me = $this->makeUser();
        $this->makePet($me, ['name' => 'Thor']);
        $this->makePet($this->makeUser(), ['name' => 'Alheio']);

        $this->getJson('/api/pets', $this->bearer($me))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Thor')
            ->assertJsonPath('0.photo_url', null);
    }

    public function test_store_creates_pet_with_photo(): void
    {
        Storage::fake('public');
        $me = $this->makeUser();

        $response = $this->postJson('/api/pets', [
            'name' => 'Mel',
            'species' => 'dog',
            'breed' => 'Vira-lata',
            'birth_date' => '2022-01-10',
            'weight_kg' => 14.5,
            'daily_goal_minutes' => 40,
            'photo' => UploadedFile::fake()->image('mel.jpg'),
        ], $this->bearer($me))->assertCreated();

        $response->assertJsonPath('name', 'Mel')->assertJsonPath('weight_kg', 14.5)->assertJsonPath('birth_date', '2022-01-10');
        $this->assertStringContainsString('/storage/pets/', $response->json('photo_url'));
        $this->assertCount(1, Storage::disk('public')->files('pets'));
    }

    public function test_store_validates_required_fields_and_species(): void
    {
        $this->postJson('/api/pets', ['species' => 'peixe'], $this->bearer($this->makeUser()))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'species']);
    }

    public function test_show_returns_my_pet_and_hides_others(): void
    {
        $me = $this->makeUser();
        $mine = $this->makePet($me);
        $theirs = $this->makePet($this->makeUser());

        $this->getJson("/api/pets/{$mine->id}", $this->bearer($me))->assertOk()->assertJsonPath('id', $mine->id);
        $this->getJson("/api/pets/{$theirs->id}", $this->bearer($me))->assertNotFound();
    }

    public function test_update_is_partial_and_replaces_local_photo(): void
    {
        Storage::fake('public');
        $me = $this->makeUser();
        $old = UploadedFile::fake()->image('old.jpg')->store('pets', 'public');
        $pet = $this->makePet($me, ['photo_path' => $old]);

        $this->postJson("/api/pets/{$pet->id}", [
            'name' => 'Thor II',
            'photo' => UploadedFile::fake()->image('new.jpg'),
        ], $this->bearer($me))->assertOk()->assertJsonPath('name', 'Thor II')->assertJsonPath('species', 'dog');

        Storage::disk('public')->assertMissing($old);
        $this->assertCount(1, Storage::disk('public')->files('pets'));
    }

    public function test_update_keeps_remote_photo_url_untouched_on_disk(): void
    {
        Storage::fake('public');
        $me = $this->makeUser();
        $pet = $this->makePet($me, ['photo_path' => 'https://cdn.example.com/thor.jpg']);

        $this->getJson("/api/pets/{$pet->id}", $this->bearer($me))->assertJsonPath('photo_url', 'https://cdn.example.com/thor.jpg');

        $this->putJson("/api/pets/{$pet->id}", ['photo' => UploadedFile::fake()->image('n.jpg')], $this->bearer($me))->assertOk();
        $this->assertCount(1, Storage::disk('public')->files('pets'));
    }

    public function test_demo_photo_is_served_from_public_seed_and_kept_on_replace(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('seed/thor.jpg', 'x');
        $me = $this->makeUser();
        $pet = $this->makePet($me, ['photo_path' => 'seed/thor.jpg']);

        $this->getJson("/api/pets/{$pet->id}", $this->bearer($me))->assertJsonPath('photo_url', url('seed/thor.jpg'));

        $this->putJson("/api/pets/{$pet->id}", ['photo' => UploadedFile::fake()->image('n.jpg')], $this->bearer($me))->assertOk();
        Storage::disk('public')->assertExists('seed/thor.jpg');
    }

    public function test_update_of_other_users_pet_is_not_found(): void
    {
        $pet = $this->makePet($this->makeUser());

        $this->putJson("/api/pets/{$pet->id}", ['name' => 'X'], $this->bearer($this->makeUser()))->assertNotFound();
    }

    public function test_destroy_deletes_my_pet(): void
    {
        $me = $this->makeUser();
        $pet = $this->makePet($me);

        $this->deleteJson("/api/pets/{$pet->id}", [], $this->bearer($me))->assertNoContent();
        $this->assertDatabaseMissing('pets', ['id' => $pet->id]);
    }
}
