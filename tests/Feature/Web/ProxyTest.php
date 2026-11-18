<?php

namespace Tests\Feature\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProxyTest extends TestCase
{
    use RefreshDatabase;

    public function test_links_use_https_behind_the_cloudflare_tunnel(): void
    {
        $this->get('/', ['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'petmotion.tolembrado.com.br'])
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://petmotion.tolembrado.com.br">', false);
    }
}
