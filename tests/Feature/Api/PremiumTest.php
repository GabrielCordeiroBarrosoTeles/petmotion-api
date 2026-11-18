<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PremiumTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_are_not_premium(): void
    {
        $user = $this->makeUser();

        $this->getJson('/api/me', $this->bearer($user))
            ->assertOk()
            ->assertJsonPath('is_premium', false)
            ->assertJsonPath('premium_until', null)
            ->assertJsonPath('trial_available', true);
    }

    public function test_starting_the_trial_gives_seven_days_once(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 18)->setTime(10, 0));
        $user = $this->makeUser();
        $headers = $this->bearer($user);

        $this->postJson('/api/premium/trial', [], $headers)
            ->assertOk()
            ->assertJsonPath('is_premium', true)
            ->assertJsonPath('trial_available', false)
            ->assertJsonPath('premium_until', '2026-11-25T13:00:00.000000Z');

        $this->postJson('/api/premium/trial', [], $headers)
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Você já usou seu teste grátis da Matilha.');
    }

    public function test_premium_expires(): void
    {
        $user = $this->makeUser();
        $user->forceFill(['premium_until' => now()->subMinute(), 'trial_started_at' => now()->subDays(8)])->save();

        $this->getJson('/api/me', $this->bearer($user))->assertJsonPath('is_premium', false)->assertJsonPath('trial_available', false);
    }

    public function test_free_plan_allows_one_pet_and_premium_unlimited(): void
    {
        $user = $this->makeUser();
        $headers = $this->bearer($user);
        $this->makePet($user);

        $this->postJson('/api/pets', ['name' => 'Mel', 'species' => 'dog'], $headers)
            ->assertForbidden()
            ->assertJsonPath('code', 'premium_required')
            ->assertJsonPath('message', 'No plano grátis cabe 1 pet. Entre para a Matilha para ter pets ilimitados.');

        $user->forceFill(['premium_until' => now()->addDays(3)])->save();
        $this->app['auth']->forgetGuards(); // nova requisição: o usuário é recarregado do banco
        $this->postJson('/api/pets', ['name' => 'Mel', 'species' => 'dog'], $headers)->assertCreated();
    }
}
