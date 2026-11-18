<?php

namespace Tests\Feature\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/monitor')->assertRedirect('/monitor/login');
        $this->get('/monitor/login')->assertOk()->assertSee('Entrar no monitor');
    }

    public function test_non_admin_cannot_open_dashboard_or_log_in(): void
    {
        $user = $this->makeUser(['email' => 'tutor@x.com', 'password' => 'petmotion']);

        $this->actingAs($user)->get('/monitor')->assertRedirect('/monitor/login');

        $this->post('/monitor/login', ['email' => 'tutor@x.com', 'password' => 'petmotion'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_wrong_password_keeps_email_and_shows_error(): void
    {
        $this->makeAdmin();

        $this->from('/monitor/login')
            ->post('/monitor/login', ['email' => 'admin@petmotion.app', 'password' => 'errada'])
            ->assertRedirect('/monitor/login')
            ->assertSessionHasErrors('email')
            ->assertSessionHasInput('email', 'admin@petmotion.app');
    }

    public function test_admin_logs_in_sees_dashboard_and_logs_out(): void
    {
        $this->makeAdmin();

        $this->post('/monitor/login', ['email' => 'admin@petmotion.app', 'password' => 'password'])
            ->assertRedirect('/monitor');
        $this->get('/monitor/login')->assertRedirect('/monitor');

        $this->post('/monitor/logout')->assertRedirect('/monitor/login');
        $this->assertGuest();
    }

    public function test_dashboard_shows_kpis_ranking_health_and_events(): void
    {
        $admin = $this->makeAdmin();
        $tutor = $this->makeUser(['name' => 'Ana Souza']);
        $pet = $this->makePet($tutor, ['name' => 'Pipoca']);
        $this->makePet($tutor, ['name' => 'Parado']); // sem passeios: fora do ranking
        $walk = $this->makeWalk($pet, now()->subHour(), 30, 2500);
        $walk->events()->create(['type' => 'pee', 'lat' => -23.5, 'lng' => -46.6, 'occurred_at' => now()]);
        $this->makeHealth($pet, ['title' => 'V10 atrasada', 'next_date' => now()->subDays(2)->toDateString()]);
        $this->makeHealth($pet, ['title' => 'Antirrábica hoje', 'next_date' => now()->toDateString()]);
        $this->makeHealth($pet, ['title' => 'Drontal', 'next_date' => now()->addDays(5)->toDateString()]);

        $this->actingAs($admin)->get('/monitor')
            ->assertOk()
            ->assertSee('Últimos passeios')
            ->assertSee('Pipoca')
            ->assertSee('Ana Souza')
            ->assertSee('Atrasada')
            ->assertSee('Hoje')
            ->assertDontSee('Parado · ')
            ->assertSee('"pet":"Pipoca"', false);
    }

    public function test_empty_dashboard_shows_empty_states(): void
    {
        $this->actingAs($this->makeAdmin())->get('/monitor')
            ->assertOk()
            ->assertSee('Nenhum passeio nesta semana.')
            ->assertSee('Nenhum passeio registrado ainda.')
            ->assertSee('Nada vencendo nas próximas duas semanas.');
    }
}
