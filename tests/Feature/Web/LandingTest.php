<?php

namespace Tests\Feature\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_live_numbers_seo_and_faq(): void
    {
        $pet = $this->makePet($this->makeUser());
        $this->makeWalk($pet, now()->subHour(), 30, 2600);

        $this->get('/')
            ->assertOk()
            ->assertSee('Cada passeio', false)
            ->assertSee('<strong>1 pets</strong>', false)
            ->assertSee('<strong>3 km</strong>', false)
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('Preciso comprar uma coleira com GPS ou outro aparelho?')
            ->assertSee(route('legal.privacy'), false);
    }

    public function test_sitemap_lists_public_pages(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<loc>'.route('landing').'</loc>', false)
            ->assertSee('<loc>'.route('legal.privacy').'</loc>', false)
            ->assertSee('<loc>'.route('legal.terms').'</loc>', false);
    }

    public function test_sitemap_uses_last_walk_date_when_there_are_walks(): void
    {
        $walk = $this->makeWalk($this->makePet($this->makeUser()), now()->subDay());

        $this->get('/sitemap.xml')->assertSee($walk->updated_at->toAtomString(), false);
    }

    public function test_robots_blocks_monitor_allows_ai_crawlers_and_points_to_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /monitor')
            ->assertSee('User-agent: GPTBot')
            ->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_llms_txt_summarises_the_app_and_faq(): void
    {
        $this->get('/llms.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('# PetMotion')
            ->assertSee('### Funciona para gato também?')
            ->assertSee('Termos de Uso: '.route('legal.terms'));
    }
}
