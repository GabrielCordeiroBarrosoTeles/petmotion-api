<?php

namespace Tests\Unit;

use Illuminate\Broadcasting\BroadcastManager;
use Tests\TestCase;

class BroadcastingConfigTest extends TestCase
{
    public function test_reverb_publish_gives_up_fast(): void
    {
        $this->assertSame(
            ['connect_timeout' => 1.0, 'timeout' => 2.0],
            config('broadcasting.connections.reverb.client_options'),
        );

        // o Guzzle do Pusher recebe esses limites de verdade
        config(['broadcasting.connections.reverb' => ['key' => 'k', 'secret' => 's', 'app_id' => '1'] + config('broadcasting.connections.reverb')]);
        $pusher = app(BroadcastManager::class)->connection('reverb')->getPusher();
        $client = (fn () => $this->client)->call($pusher);
        $this->assertSame(1.0, $client->getConfig('connect_timeout'));
        $this->assertSame(2.0, $client->getConfig('timeout'));
    }
}
