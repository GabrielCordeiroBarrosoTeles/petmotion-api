@extends('monitor.app')
@section('title', 'Visão geral')

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
@endpush

@push('styles')
<style>
    .map-live { height: 420px; }
    .rank { list-style: none; margin: 0; padding: 0; counter-reset: r; }
    .rank li { display: grid; grid-template-columns: 34px 1fr auto; align-items: center; gap: 12px; padding: 11px 0; border-bottom: 1px solid var(--line); }
    .rank li:last-child { border-bottom: 0; }
    .rank li::before { counter-increment: r; content: counter(r); font: 800 16px var(--display); width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; background: var(--fog); }
    .rank li:first-child::before { background: var(--ball); }
    .rank b { display: block; }
    .rank small { color: var(--muted); }
    .rank .km { font: 800 19px var(--display); }
    .chart-box { position: relative; height: 260px; }
    .events { display: grid; gap: 10px; }
    .event { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-radius: var(--r-sm); font-weight: 700; }
    .event strong { font: 800 26px/1 var(--display); }
    .event.pee { background: #FFF1C9; }
    .event.poop { background: #F1E4D6; }
    .event.water { background: #DCEEFA; }
</style>
@endpush

@section('content')
<div class="page-head">
    <div>
        <h1>Visão geral</h1>
        <p>{{ now()->translatedFormat('l, j \d\e F') }}. O que está acontecendo na comunidade.</p>
    </div>
</div>

<section class="kpis" aria-label="Indicadores">
    <div class="kpi hot">
        <span><x-monitor.icon name="footprints" :size="16" />Passeios hoje</span>
        <strong>{{ $kpis['walksToday'] }}</strong>
        <small>{{ $kpis['activePets'] }} pets ativos nos últimos 7 dias</small>
    </div>
    <div class="kpi">
        <span><x-monitor.icon name="route" :size="16" />Km em 7 dias</span>
        <strong>{{ number_format($kpis['kmWeek'], 1, ',', '.') }}</strong>
        @if ($kpis['kmTrend'] === null)
            <small>Sem semana anterior para comparar</small>
        @else
            <small class="{{ $kpis['kmTrend'] >= 0 ? 'trend-up' : 'trend-down' }}">{{ $kpis['kmTrend'] >= 0 ? '+' : '' }}{{ $kpis['kmTrend'] }}% sobre a semana anterior</small>
        @endif
    </div>
    <div class="kpi">
        <span><x-monitor.icon name="users" :size="16" />Comunidade</span>
        <strong>{{ $kpis['tutors'] }}</strong>
        <small>pais de pet e {{ $kpis['pets'] }} pets · {{ $kpis['premium'] }} na Matilha</small>
    </div>
    <a class="kpi kpi-link {{ $kpis['lateHealth'] ? 'alert' : '' }}" href="{{ route('monitor.health') }}">
        <span><x-monitor.icon name="heart" :size="16" />Cuidados atrasados</span>
        <strong>{{ $kpis['lateHealth'] }}</strong>
        <small>{{ $kpis['lateHealth'] ? 'Ver quem precisa de atenção' : 'Tudo em dia' }}</small>
    </a>
</section>

<div class="grid grid-2-1">
    <section class="panel">
        <div class="panel-head"><h2>Mapa ao vivo</h2><a href="{{ route('monitor.walks') }}">Todos os passeios</a></div>
        <div id="map" class="map map-live" role="img" aria-label="Mapa com os {{ count($routes) }} passeios mais recentes"></div>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Ranking da semana</h2><a href="{{ route('monitor.pets') }}">Pets</a></div>
        <ol class="rank">
            @forelse ($ranking as $pet)
                <li>
                    <div><b>{{ $pet->name }}</b><small>{{ $pet->user->name }} · {{ $pet->week_walks }} passeios</small></div>
                    <span class="km">{{ number_format(($pet->week_meters ?? 0) / 1000, 1, ',', '.') }} km</span>
                </li>
            @empty
                <li class="empty"><b>Nenhum passeio nesta semana.</b>O ranking aparece quando alguém passear.</li>
            @endforelse
        </ol>
    </section>
</div>

<div class="grid grid-2-1">
    <section class="panel">
        <div class="panel-head"><h2>Atividade em 14 dias</h2></div>
        <div class="chart-box"><canvas id="activity" aria-label="Gráfico de passeios e quilômetros por dia"></canvas></div>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Marcações da semana</h2></div>
        <div class="events">
            <div class="event pee"><span>Xixi</span><strong>{{ $eventsWeek['pee'] ?? 0 }}</strong></div>
            <div class="event poop"><span>Cocô</span><strong>{{ $eventsWeek['poop'] ?? 0 }}</strong></div>
            <div class="event water"><span>Água</span><strong>{{ $eventsWeek['water'] ?? 0 }}</strong></div>
        </div>
    </section>
</div>

<section class="panel">
    <div class="panel-head"><h2>Últimos passeios</h2><a href="{{ route('monitor.walks') }}">Ver todos</a></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Pet</th><th>Pai/mãe de pet</th><th>Quando</th><th>Duração</th><th>Distância</th><th>Marcações</th></tr></thead>
            <tbody>
            @forelse ($recentWalks as $walk)
                <tr class="link" data-href="{{ route('monitor.walks.show', $walk) }}">
                    <td><a href="{{ route('monitor.walks.show', $walk) }}">{{ $walk->pet->name }}</a></td>
                    <td>
                        {{ $walk->pet->user->name }}
                        @if ($walk->user_id !== $walk->pet->user_id)
                            <small class="muted">com {{ $walk->user->name }}</small>
                        @endif
                    </td>
                    <td class="muted">{{ $walk->started_at->diffForHumans() }}</td>
                    <td class="num">{{ intdiv($walk->duration_seconds, 60) }} min</td>
                    <td class="num">{{ number_format($walk->distance_meters / 1000, 2, ',', '.') }} km</td>
                    <td class="num">{{ $walk->events_count }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty"><b>Nenhum passeio registrado ainda.</b>Eles aparecem aqui assim que o app salvar o primeiro.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
    const routes = @json($routes);
    const chart = @json($chart);
    const css = (v) => getComputedStyle(document.documentElement).getPropertyValue(v).trim();

    const map = L.map('map', { zoomControl: false, scrollWheelZoom: false });
    L.control.zoom({ position: 'bottomright' }).addTo(map);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(map);

    // a view precisa existir antes das linhas, senão o Leaflet não cria o SVG delas
    const bounds = routes.flatMap((r) => r.points ?? []);
    bounds.length ? map.fitBounds(bounds, { padding: [30, 30] }) : map.setView([-23.5874, -46.6576], 14);

    const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
    [...routes].reverse().forEach((r, idx) => {
        const latest = idx === routes.length - 1;
        if (!r.points?.length) return;
        const line = L.polyline(r.points, {
            color: latest ? css('--ball') : css('--puddle'), weight: latest ? 5 : 2.5, opacity: latest ? 1 : 0.45, lineCap: 'round',
        }).bindTooltip(`${r.pet} · ${String(r.km).replace('.', ',')} km`, { sticky: true })
          .on('click', () => (location.href = `{{ url('monitor/passeios') }}/${r.id}`))
          .addTo(map);
        const el = line.getElement?.();
        if (latest && el && !reduce) {
            const len = el.getTotalLength();
            el.style.strokeDasharray = len;
            el.style.strokeDashoffset = len;
            el.getBoundingClientRect();
            el.style.transition = 'stroke-dashoffset 2.4s ease-out';
            requestAnimationFrame(() => (el.style.strokeDashoffset = 0));
        }
    });

    Chart.defaults.font.family = 'Manrope';
    Chart.defaults.color = css('--muted');
    new Chart(document.getElementById('activity'), {
        data: {
            labels: chart.labels,
            datasets: [
                { type: 'bar', label: 'Passeios', data: chart.walks, backgroundColor: css('--pine'), borderRadius: 6, yAxisID: 'y', order: 2 },
                { type: 'line', label: 'Km', data: chart.km, borderColor: css('--ball-deep'), backgroundColor: css('--ball'), borderWidth: 3, pointRadius: 3, tension: .35, yAxisID: 'y1', order: 1 },
            ],
        },
        options: {
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: css('--line') } },
                y1: { beginAtZero: true, position: 'right', grid: { display: false } },
            },
        },
    });
</script>
@endpush
