<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_checks_ok_returns_200(): void
    {
        Storage::fake('public');

        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database.status', 'ok')
            ->assertJsonPath('checks.cache.status', 'ok')
            ->assertJsonPath('checks.storage.status', 'ok')
            ->assertJsonStructure(['app', 'environment', 'time', 'checks' => ['database' => ['ms']]]);
    }

    public function test_database_failure_returns_503_with_message_in_debug(): void
    {
        Storage::fake('public');
        config(['app.debug' => true]);
        DB::partialMock()->shouldReceive('select')->andThrow(new RuntimeException('banco fora'));

        $this->getJson('/api/health')
            ->assertStatus(503)
            ->assertJsonPath('status', 'degraded')
            ->assertJsonPath('checks.database.status', 'fail')
            ->assertJsonPath('checks.database.error', 'banco fora');
    }

    public function test_cache_returning_wrong_value_fails_and_hides_details_outside_debug(): void
    {
        Storage::fake('public');
        config(['app.debug' => false]);
        // o throttle da rota também usa o cache, então ele sai deste teste
        $this->withoutMiddleware(ThrottleRequests::class);
        Cache::shouldReceive('put')->once()->andReturnTrue();
        Cache::shouldReceive('pull')->once()->andReturn('outra-coisa');

        $this->getJson('/api/health')
            ->assertStatus(503)
            ->assertJsonPath('checks.cache.status', 'fail')
            ->assertJsonPath('checks.cache.error', 'unavailable');
    }

    public function test_storage_failure_is_reported(): void
    {
        Storage::partialMock()->shouldReceive('disk')->andThrow(new RuntimeException('disco cheio'));

        $this->getJson('/api/health')->assertStatus(503)->assertJsonPath('checks.storage.status', 'fail');
    }
}
