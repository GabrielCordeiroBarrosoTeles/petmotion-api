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
        $this->makeWalk($pet, now()->subHour());
        $this->makeHealth($pet);

        $this->deleteJson('/api/me', ['password' => 'petmotion'], $this->bearer($user))->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseCount('pets', 0);
        $this->assertDatabaseCount('walks', 0);
        $this->assertDatabaseCount('health_records', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        Storage::disk('public')->assertMissing($local);
    }
}
