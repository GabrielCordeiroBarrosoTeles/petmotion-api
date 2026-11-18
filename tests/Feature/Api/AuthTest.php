<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_and_returns_token(): void
    {
        $this->postJson('/api/register', ['name' => 'Ana', 'email' => 'ana@x.com', 'password' => 'segredo1'])
            ->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']])
            ->assertJsonMissingPath('user.is_admin');

        $this->assertDatabaseHas('users', ['email' => 'ana@x.com']);
    }

    public function test_register_validates_input(): void
    {
        $this->makeUser(['email' => 'ana@x.com']);

        $this->postJson('/api/register', ['name' => '', 'email' => 'ana@x.com', 'password' => '123'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_login_returns_token_with_correct_password(): void
    {
        $this->makeUser(['email' => 'gabriel@x.com', 'password' => 'petmotion']);

        $this->postJson('/api/login', ['email' => 'gabriel@x.com', 'password' => 'petmotion'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_rejects_wrong_password(): void
    {
        $this->makeUser(['email' => 'gabriel@x.com', 'password' => 'petmotion']);

        $this->postJson('/api/login', ['email' => 'gabriel@x.com', 'password' => 'errada'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'E-mail ou senha incorretos.');
    }

    public function test_login_rejects_unknown_email(): void
    {
        $this->postJson('/api/login', ['email' => 'ninguem@x.com', 'password' => 'petmotion'])
            ->assertUnprocessable();
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_me_returns_current_user(): void
    {
        $user = $this->makeUser();

        $this->getJson('/api/me', $this->bearer($user))->assertOk()->assertJsonPath('id', $user->id);
    }

    public function test_logout_revokes_the_token(): void
    {
        $user = $this->makeUser();
        $headers = $this->bearer($user);

        $this->postJson('/api/logout', [], $headers)->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_delete_account_requires_correct_password(): void
    {
        $user = $this->makeUser(['password' => 'petmotion']);

        $this->deleteJson('/api/me', ['password' => 'errada'], $this->bearer($user))
            ->assertUnprocessable()
            ->assertJsonPath('errors.password.0', 'Senha incorreta.');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_delete_account_erases_user_pets_walks_health_tokens_and_photos(): void
    {
        Storage::fake('public');
        $user = $this->makeUser(['password' => 'petmotion']);
        $local = UploadedFile::fake()->image('thor.jpg')->store('pets', 'public');
        $pet = $this->makePet($user, ['photo_path' => $local]);
        $this->makePet($user, ['name' => 'Mel', 'photo_path' => 'https://cdn.example.com/mel.jpg']);
        $this->makePet($user, ['name' => 'Bidu', 'photo_path' => 'seed/bidu.jpg']);
        Storage::disk('public')->put('seed/bidu.jpg', 'x'); // mesmo nome no disco: a foto de demonstração não pode ser apagada
        $avatar = UploadedFile::fake()->image('eu.jpg')->store('users', 'public');
        $user->forceFill(['photo_path' => $avatar])->save();
        $this->makeWalk($pet, now()->subHour());
        $this->makeHealth($pet);

        $this->deleteJson('/api/me', ['password' => 'petmotion'], $this->bearer($user))->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseCount('pets', 0);
        $this->assertDatabaseCount('walks', 0);
        $this->assertDatabaseCount('health_records', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        Storage::disk('public')->assertMissing([$local, $avatar]);
        Storage::disk('public')->assertExists('seed/bidu.jpg');
    }

    public function test_walker_deleting_account_takes_their_walks_out_of_the_pet_history(): void
    {
        // é o que a política de privacidade promete: passeio feito pelo passeador sai junto com a conta dele
        $owner = $this->makeUser();
        $pet = $this->makePet($owner);
        $walker = $this->makeWalker($pet);
        $walker->forceFill(['password' => 'petmotion'])->save();
        $ownWalk = $this->makeWalk($pet, now()->subHours(2));
        $walker->walks()->create([
            'pet_id' => $pet->id, 'started_at' => now()->subHour(), 'ended_at' => now(), 'duration_seconds' => 600,
            'distance_meters' => 700, 'points' => [[-23.58, -46.65]],
        ]);

        $this->deleteJson('/api/me', ['password' => 'petmotion'], $this->bearer($walker))->assertNoContent();

        $this->assertSame([$ownWalk->id], $pet->walks()->pluck('id')->all());
        $this->assertDatabaseHas('pets', ['id' => $pet->id]);
        $this->assertDatabaseCount('pet_walkers', 0);
    }

    public function test_me_returns_photo_url_from_upload_seed_or_none(): void
    {
        $expected = [null => null, 'seed/gabriel.jpg' => url('seed/gabriel.jpg'), 'users/eu.jpg' => url('/storage/users/eu.jpg')];

        foreach ($expected as $path => $url) {
            $user = $this->makeUser();
            $user->forceFill(['photo_path' => $path ?: null])->save();
            $this->app['auth']->forgetGuards(); // cada usuário numa sessão nova

            $this->getJson('/api/me', $this->bearer($user))->assertJsonPath('photo_url', $url)->assertJsonMissingPath('photo_path');
        }
    }
}
