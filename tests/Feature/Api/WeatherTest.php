<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class WeatherTest extends TestCase
{
    use RefreshDatabase;

    private function fakeCurrent(array $current): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response(['current' => $current])]);
    }

    private function weather(): TestResponse
    {
        return $this->getJson('/api/weather?lat=-23.58&lng=-46.65', $this->bearer($this->makeUser()));
    }

    public function test_sunny_hot_day_is_dangerous_for_paws(): void
    {
        $this->fakeCurrent(['temperature_2m' => 30, 'is_day' => 1, 'cloud_cover' => 10, 'uv_index' => 9]);

        $this->weather()->assertOk()
            ->assertJsonPath('asphalt_c', 55)
            ->assertJsonPath('level', 'danger')
            ->assertJsonPath('uv_index', 9);
    }

    public function test_sunny_mild_day_is_a_warning(): void
    {
        $this->fakeCurrent(['temperature_2m' => 20, 'is_day' => 1, 'cloud_cover' => 0]);

        $this->weather()->assertJsonPath('asphalt_c', 45)->assertJsonPath('level', 'warning')->assertJsonPath('uv_index', null);
    }

    public function test_cloudy_day_adds_less_heat(): void
    {
        $this->fakeCurrent(['temperature_2m' => 25, 'is_day' => 1, 'cloud_cover' => 90]);

        $this->weather()->assertJsonPath('asphalt_c', 37)->assertJsonPath('level', 'safe');
    }

    public function test_night_is_safe(): void
    {
        $this->fakeCurrent(['temperature_2m' => 19.4, 'is_day' => 0]);

        $this->weather()->assertJsonPath('asphalt_c', 22)->assertJsonPath('is_day', false)->assertJsonPath('level', 'safe');
    }

    public function test_provider_failure_returns_503(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response([], 500)]);

        $this->weather()->assertStatus(503)->assertJsonPath('message', 'Clima indisponível no momento.');
    }

    public function test_coordinates_are_validated(): void
    {
        $this->getJson('/api/weather?lat=200', $this->bearer($this->makeUser()))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['lat', 'lng']);
    }
}
