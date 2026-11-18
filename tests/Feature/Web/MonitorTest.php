<?php

namespace Tests\Feature\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class MonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/monitor')->assertRedirect('/monitor/login');
        $this->get('/monitor/login')->assertOk()->assertSee('Entrar no monitor')->assertSee('passeios hoje');
    }

    public function test_login_page_shows_only_aggregated_live_numbers(): void
    {
        // relógio fixo: perto da meia-noite, "30 minutos atrás" cairia em ontem
        $this->travelTo(now()->setTime(12, 0));
        $pet = $this->makePet($this->makeUser(['name' => 'Tutor Secreto']));
        $this->makeWalk($pet, now()->subMinutes(30), 30, 2600);
        $this->makeWalk($pet, now()->subDays(3), 30, 4400);
        $this->makeWalk($pet, now()->subDays(20), 30, 9000); // fora dos 7 dias

        $this->get('/monitor/login')
            ->assertOk()
            ->assertSee('passeio hoje')
            ->assertSee('7 km')
            ->assertDontSee('Tutor Secreto');
    }

    public function test_demo_login_hint_only_shows_locally(): void
    {
        $this->app['env'] = 'local';
        $this->get('/monitor/login')->assertSee('Demo: admin@petmotion.app');

        $this->app['env'] = 'production';
        $this->get('/monitor/login')->assertDontSee('Demo: admin@petmotion.app');
    }

    public function test_seed_uses_the_admin_password_from_env_when_set(): void
    {
        putenv('SEED_ADMIN_PASSWORD=senha-forte-123');
        $this->seed();
        putenv('SEED_ADMIN_PASSWORD');

        $this->post('/monitor/login', ['email' => 'cordeiro@suporte.com', 'password' => '12345678'])->assertSessionHasErrors('email');
        $this->post('/monitor/login', ['email' => 'cordeiro@suporte.com', 'password' => 'senha-forte-123'])->assertRedirect('/monitor');
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

    public function test_dashboard_shows_kpis_map_ranking_events_and_recent_walks(): void
    {
        $this->travelTo(now()->setTime(12, 0));
        $admin = $this->makeAdmin();
        $tutor = $this->makeUser(['name' => 'Ana Souza']);
        $pet = $this->makePet($tutor, ['name' => 'Pipoca']);
        $this->makePet($tutor, ['name' => 'Parado']); // sem passeios: fora do ranking
        $walk = $this->makeWalk($pet, now()->subHour(), 30, 2500);
        $walk->events()->create(['type' => 'pee', 'lat' => -23.5, 'lng' => -46.6, 'occurred_at' => now()]);
        $this->makeWalk($pet, now()->subDays(9), 30, 1000); // semana anterior: base da tendência
        $this->makeHealth($pet, ['next_date' => now()->subDays(2)->toDateString()]);

        $this->actingAs($admin)->get('/monitor')
            ->assertOk()
            ->assertSee('Visão geral')
            ->assertSee('Pipoca')
            ->assertSee('Ana Souza')
            ->assertSee('sobre a semana anterior')
            ->assertSee('Ver quem precisa de atenção')
            ->assertDontSee('Parado · ')
            ->assertSee('"pet":"Pipoca"', false)
            ->assertSee(route('monitor.walks.show', $walk), false);
    }

    public function test_empty_dashboard_shows_empty_states(): void
    {
        $this->actingAs($this->makeAdmin())->get('/monitor')
            ->assertOk()
            ->assertSee('Nenhum passeio nesta semana.')
            ->assertSee('Nenhum passeio registrado ainda.')
            ->assertSee('Sem semana anterior para comparar')
            ->assertSee('Tudo em dia');
    }

    public function test_sidebar_marks_current_page_and_counts_walks_today_and_overdue_care(): void
    {
        $this->travelTo(now()->setTime(12, 0));
        $pet = $this->makePet($this->makeUser());
        $this->makeWalk($pet, now()->subHour());
        $this->makeHealth($pet, ['next_date' => now()->subDay()->toDateString()]);
        $this->makeHealth($pet, ['next_date' => now()->toDateString()]); // hoje não é atraso

        $response = $this->actingAs($this->makeAdmin())->get('/monitor/pets')
            ->assertOk()
            ->assertSee('title="1 passeios hoje"', false)
            ->assertSee('title="1 cuidados atrasados"', false);
        $this->assertMatchesRegularExpression('#href="'.preg_quote(route('monitor.pets'), '#').'"\s+aria-current="page"#', $response->getContent());
    }

    public function test_walks_list_filters_by_period_and_search_and_paginates(): void
    {
        $this->travelTo(now()->setTime(12, 0));
        $admin = $this->makeAdmin();
        $thor = $this->makePet($this->makeUser(['name' => 'Gabriel']), ['name' => 'Thor']);
        $mel = $this->makePet($this->makeUser(['name' => 'Ana']), ['name' => 'Mel']);
        $this->makeWalk($thor, now()->subHour(), 30, 2000);
        $this->makeWalk($mel, now()->subDays(10), 30, 3000);

        $this->actingAs($admin)->get('/monitor/passeios')
            ->assertOk()->assertSee('Thor')->assertDontSee('>Mel<', false)->assertSee('1 passeios, 2,0 km');
        $this->get('/monitor/passeios?period=all')->assertSee('Mel')->assertSee('2 passeios, 5,0 km');
        $this->get('/monitor/passeios?period=today')->assertSee('Thor');
        $this->get('/monitor/passeios?period=30d&q=Ana')->assertSee('Mel')->assertDontSee('>Thor<', false);
        $this->get('/monitor/passeios?period=all&q=Thor')->assertSee('Thor');
        $this->get('/monitor/passeios?period=invalido&q=ninguem')->assertSee('Nenhum passeio encontrado.');

        foreach (range(1, 21) as $i) {
            $this->makeWalk($thor, now()->subMinutes($i + 90));
        }
        $this->get('/monitor/passeios?period=all')->assertSee('Próxima')->assertSee('rel="next"', false);
        $this->get('/monitor/passeios?period=all&page=2')->assertSee('rel="prev"', false);
    }

    public function test_walk_detail_shows_map_data_and_summary(): void
    {
        $pet = $this->makePet($this->makeUser(['name' => 'Gabriel']), ['name' => 'Thor']);
        $walk = $this->makeWalk($pet, now()->subHour(), 40, 3420, ['temperature_c' => 22]);
        $walk->events()->create(['type' => 'water', 'lat' => -23.5, 'lng' => -46.6, 'occurred_at' => now()]);
        $short = $this->makeWalk($pet, now()->subHours(3), 1, 20);

        $this->actingAs($this->makeAdmin())->get(route('monitor.walks.show', $walk))
            ->assertOk()->assertSee('Thor, 3,42 km')->assertSee('22 °C')->assertSee('min/km')->assertSee('"type":"water"', false);
        $this->get(route('monitor.walks.show', $short))->assertOk()->assertSee('—');
    }

    public function test_tutors_list_search_and_profile(): void
    {
        $admin = $this->makeAdmin();
        $gabriel = $this->makeUser(['name' => 'Gabriel Cordeiro', 'email' => 'gabriel@x.com']);
        $this->makeUser(['name' => 'Novo Tutor']);
        $thor = $this->makePet($gabriel, ['name' => 'Thor', 'breed' => 'Golden']);
        $this->makePet($gabriel, ['name' => 'Nina', 'species' => 'cat']);
        $this->makeWalk($thor, now()->subHour(), 30, 2000);

        $this->actingAs($admin)->get('/monitor/tutores')
            ->assertOk()->assertSee('Gabriel Cordeiro')->assertSee('Nunca passeou')
            ->assertDontSee(route('monitor.tutors.show', $admin), false); // admin não entra na lista de tutores
        $this->get('/monitor/tutores?q=gabriel@')->assertSee('Gabriel Cordeiro')->assertDontSee('Novo Tutor');
        $this->get('/monitor/tutores?q=zzz')->assertSee('Ninguém encontrado.');

        $this->get(route('monitor.tutors.show', $gabriel))
            ->assertOk()->assertSee('Thor')->assertSee('Golden')->assertSee('Gato')->assertSee('2,0');
    }

    public function test_premium_tutors_are_marked_in_list_profile_and_dashboard(): void
    {
        $admin = $this->makeAdmin();
        $member = $this->makeUser(['name' => 'Membro']);
        $member->forceFill(['premium_until' => now()->addDays(5)])->save();
        $free = $this->makeUser(['name' => 'Gratuito']);

        $this->actingAs($admin)->get('/monitor/tutores')->assertSeeInOrder(['Gratuito', 'Membro', 'Matilha']);
        $this->get(route('monitor.tutors.show', $member))->assertSee('Matilha até '.now()->addDays(5)->format('d/m/Y'));
        $this->get(route('monitor.tutors.show', $free))->assertSee('Plano grátis');
        $this->get('/monitor')->assertSee('1 na Matilha');
    }

    public function test_tutor_without_pets_and_admin_profile_is_hidden(): void
    {
        $admin = $this->makeAdmin();
        $empty = $this->makeUser(['name' => 'Sem Pet']);

        $this->actingAs($admin)->get(route('monitor.tutors.show', $empty))
            ->assertOk()->assertSee('Ainda sem pets.')->assertSee('Nenhum passeio ainda.');
        $this->get(route('monitor.tutors.show', $admin))->assertNotFound();
    }

    public function test_pets_list_filters_by_species_and_shows_weekly_goal(): void
    {
        $this->travelTo(now()->setTime(12, 0));
        $tutor = $this->makeUser();
        $thor = $this->makePet($tutor, ['name' => 'Thor', 'daily_goal_minutes' => 10, 'weight_kg' => 32.4]);
        $this->makePet($tutor, ['name' => 'Bidu', 'breed' => 'Beagle', 'daily_goal_minutes' => 10]);
        $this->makePet($tutor, ['name' => 'Rex', 'daily_goal_minutes' => 0]);
        $this->makePet($tutor, ['name' => 'Nina', 'species' => 'cat']);
        $this->makeWalk($thor, now()->subHour(), 70);

        $this->actingAs($this->makeAdmin())->get('/monitor/pets')
            ->assertOk()->assertSee('100% de 70 min')->assertSee('32,4 kg')->assertSee('0% de 0 min');
        $this->get('/monitor/pets?species=cat')->assertSee('Nina')->assertDontSee('Thor');
        $this->get('/monitor/pets?species=x&q=beagle')->assertSee('Bidu')->assertDontSee('Thor');
        $this->get('/monitor/pets?q=zzz')->assertSee('Nenhum pet encontrado.');

        $this->makeWalk($thor, now()->subHours(2), 0)->update(['duration_seconds' => 2400]); // 40+70 = 110 de 70: continua 100%
        $this->makeWalk($this->makePet($tutor, ['name' => 'Meio', 'daily_goal_minutes' => 10]), now()->subHour(), 40);
        $this->get('/monitor/pets?q=Meio')->assertSee('57% de 70 min');
    }

    public function test_health_page_separates_overdue_from_upcoming(): void
    {
        $this->travelTo(now()->setTime(0, 20));
        $pet = $this->makePet($this->makeUser(['name' => 'Lucas']), ['name' => 'Bidu']);
        $this->makeHealth($pet, ['title' => 'Atrasada 1', 'next_date' => now()->subDay()->toDateString()]);
        $this->makeHealth($pet, ['title' => 'Atrasada 3', 'next_date' => now()->subDays(3)->toDateString()]);
        $this->makeHealth($pet, ['title' => 'Vence hoje', 'next_date' => now()->toDateString()]);
        $this->makeHealth($pet, ['title' => 'Amanhã', 'next_date' => now()->addDay()->toDateString()]);
        $this->makeHealth($pet, ['title' => 'Semana', 'next_date' => now()->addDays(5)->toDateString()]);
        $this->makeHealth($pet, ['title' => 'Mês', 'next_date' => now()->addDays(20)->toDateString()]);

        $this->actingAs($this->makeAdmin())->get('/monitor/saude')
            ->assertOk()
            ->assertSeeInOrder(['Atrasados', 'Atrasada 3', '3 dias de atraso', 'Atrasada 1', '1 dia de atraso', 'Vencendo em breve', 'Vence hoje', 'Hoje', 'Amanhã', 'Em 1 dia', 'Semana', 'Em 5 dias', 'Mês', 'Em 20 dias']);
    }

    public function test_health_page_empty_states(): void
    {
        $this->actingAs($this->makeAdmin())->get('/monitor/saude')
            ->assertOk()->assertSee('Nada atrasado.')->assertSee('Nada vencendo.');
    }

    public function test_system_page_shows_checks_environment_and_counts(): void
    {
        Storage::fake('public');
        $this->makeWalk($this->makePet($this->makeUser()), now()->subHour());

        $this->actingAs($this->makeAdmin())->get('/monitor/sistema')
            ->assertOk()
            ->assertSee('Tudo funcionando')
            ->assertSee('Banco de dados')
            ->assertSee(PHP_VERSION)
            ->assertSee('Administradores');
    }

    public function test_system_page_reports_a_failing_check(): void
    {
        config(['app.debug' => true]);
        Storage::partialMock()->shouldReceive('disk')->andThrow(new RuntimeException('disco cheio'));

        $this->actingAs($this->makeAdmin())->get('/monitor/sistema')
            ->assertOk()
            ->assertSee('Atenção: algo falhou')
            ->assertSee('disco cheio')
            ->assertSee('Falhou')
            ->assertSee('nunca');
    }

    public function test_monitor_uses_pais_de_pet(): void
    {
        $admin = $this->makeAdmin();
        $this->makeUser(['name' => 'Gabriel Cordeiro']);

        $this->actingAs($admin)->get('/monitor/tutores')
            ->assertOk()->assertSee('Pais de pet')->assertSee('Pai/mãe de pet')->assertDontSee('Tutores');
        $this->get('/monitor')->assertSee('Pais de pet')->assertDontSee('Tutores');
        $this->get('/monitor/pets')->assertDontSee('<th>Tutor</th>', false);
    }

    public function test_walk_detail_shows_walker_when_not_the_owner(): void
    {
        $owner = $this->makeUser(['name' => 'Dona Maria']);
        $pet = $this->makePet($owner, ['name' => 'Thor']);
        $walker = $this->makeWalker($pet);
        $walker->update(['name' => 'Joao Passeador']);
        $byOwner = $this->makeWalk($pet, now()->subHours(2));
        $byWalker = $walker->walks()->create([
            'pet_id' => $pet->id, 'started_at' => now()->subHour(), 'ended_at' => now(), 'duration_seconds' => 1800,
            'distance_meters' => 1500, 'steps' => 1900, 'points' => [[-23.58, -46.65]],
        ]);

        $this->actingAs($this->makeAdmin())->get(route('monitor.walks.show', $byWalker))
            ->assertOk()->assertSee('Passeador')->assertSee('Joao Passeador')->assertSee('Dona Maria');
        $this->get(route('monitor.walks.show', $byOwner))->assertOk()->assertDontSee('Passeador')->assertSee('Dona Maria');
    }
}
