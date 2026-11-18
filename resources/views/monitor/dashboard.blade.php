@extends('monitor.layout')
@section('title', 'Visão geral')

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
@endpush

@push('styles')
<style>
    .top { background: var(--pine); color: var(--fog); }
    .top .wrap { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding-block: 18px; flex-wrap: wrap; }
    .top .brand { color: var(--fog); }
    .top .who { display: flex; align-items: center; gap: 14px; font-weight: 600; color: #B8C9C0; }
    .ghost { font: 700 14px var(--body); background: transparent; color: var(--fog); border: 2px solid var(--pine-2); padding: 9px 16px; border-radius: 999px; cursor: pointer; }
    .ghost:hover { border-color: var(--ball); color: var(--ball); }

    /* Herói: o mapa com todos os trajetos é o elemento principal */
    .hero { background: var(--pine); padding-bottom: 40px; }
    .hero-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; }
    .map .leaflet-tile-pane { filter: grayscale(1) invert(1) brightness(.62) contrast(1.1) sepia(.35) hue-rotate(110deg) saturate(1.4); }
    .map .leaflet-control-attribution { background: rgba(15, 42, 36, .7); color: #A9BDB3; }
    .map .leaflet-control-attribution a { color: var(--ball); }
    .map { height: 440px; border-radius: var(--r-lg); overflow: hidden; background: var(--pine-2); }
    .hero-side { display: grid; gap: 12px; align-content: start; color: var(--fog); }
    .hero-side h1 { font-size: 34px; font-weight: 800; color: var(--fog); margin: 4px 0 6px; }
    .hero-side .lede { color: #B8C9C0; margin: 0 0 8px; }
    .kpi { display: flex; align-items: baseline; justify-content: space-between; padding: 14px 0; border-top: 1px solid var(--pine-2); }
    .kpi span { color: #B8C9C0; font-weight: 600; }
    .kpi strong { font: 800 34px/1 var(--display); color: var(--fog); letter-spacing: -0.03em; }
    .kpi.hot strong { color: var(--ball); }

    main.wrap { padding-block: 32px 64px; display: grid; gap: 24px; }
    .row { display: grid; gap: 24px; grid-template-columns: 2fr 1fr; }
    .panel { background: var(--surface); border-radius: var(--r-md); padding: 24px; border: 1px solid var(--line); min-width: 0; }
    .panel h2 { font-size: 22px; margin-bottom: 4px; }
    .panel .sub { color: var(--muted); margin: 0 0 18px; font-size: 14px; }
    .chart-box { position: relative; height: 260px; }

    .rank { list-style: none; margin: 0; padding: 0; display: grid; gap: 4px; counter-reset: r; }
    .rank li { display: grid; grid-template-columns: 34px 1fr auto; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--line); }
    .rank li:last-child { border-bottom: 0; }
    .rank li::before { counter-increment: r; content: counter(r); font: 800 18px var(--display); width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; background: var(--fog); }
    .rank li:first-child::before { background: var(--ball); }
    .rank b { display: block; }
    .rank small { color: var(--muted); }
    .rank .km { font: 800 20px var(--display); }

    .events { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .event { border-radius: var(--r-sm); padding: 16px; }
    .event strong { display: block; font: 800 32px/1 var(--display); }
    .event span { font-weight: 700; font-size: 14px; }
    .event.pee { background: #FFF1C9; }
    .event.poop { background: #F1E4D6; }
    .event.water { background: #DCEEFA; }

    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th { text-align: left; color: var(--muted); font-weight: 600; padding: 0 12px 10px 0; border-bottom: 2px solid var(--line); }
    td { padding: 12px 12px 12px 0; border-bottom: 1px solid var(--line); white-space: nowrap; }
    tr:last-child td { border-bottom: 0; }
    td.num { font-variant-numeric: tabular-nums; font-weight: 700; }
    .table-scroll { overflow-x: auto; }

    .due { list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; }
    .due li { display: flex; justify-content: space-between; gap: 12px; padding: 12px 14px; border-radius: var(--r-sm); background: var(--fog); }
    .due li.late { background: #FFE3E6; }
    .due b { display: block; }
    .due small { color: var(--muted); }
    .pill { align-self: center; font-weight: 800; font-size: 13px; padding: 5px 10px; border-radius: 999px; background: var(--surface); white-space: nowrap; }
    .late .pill { background: var(--tongue); color: var(--pine); }
    .empty { color: var(--muted); padding: 12px 0; }

    @media (max-width: 980px) {
        .hero-grid, .row { grid-template-columns: 1fr; }
        .map { height: 340px; }
    }
</style>
@endpush

@section('body')
<header class="top">
    <div class="wrap">
        <a class="brand" href="{{ route('monitor') }}"><span class="brand-ball"></span>PetMotion <span style="font-weight:500;color:#B8C9C0">monitor</span></a>
        <div class="who">
            {{ auth()->user()->name }}
            <form method="POST" action="{{ route('monitor.logout') }}">@csrf<button class="ghost" type="submit">Sair</button></form>
        </div>
    </div>
</header>

<section class="hero">
    <div class="wrap hero-grid">
        <div id="map" class="map" role="img" aria-label="Mapa com os {{ count($routes) }} passeios mais recentes"></div>
        <div class="hero-side">
            <h1>Últimos passeios</h1>
            <p class="lede">Os {{ count($routes) }} trajetos mais recentes registrados pelo app.</p>
            <div class="kpi hot"><span>Passeios hoje</span><strong>{{ $kpis['walksToday'] }}</strong></div>
            <div class="kpi"><span>Km nos últimos 7 dias</span><strong>{{ number_format($kpis['kmWeek'], 1, ',', '.') }}</strong></div>
            <div class="kpi"><span>Tutores</span><strong>{{ $kpis['tutors'] }}</strong></div>
            <div class="kpi"><span>Pets</span><strong>{{ $kpis['pets'] }}</strong></div>
        </div>
    </div>
</section>

<main class="wrap">
    <div class="row">
        <section class="panel">
            <h2>Atividade em 14 dias</h2>
            <p class="sub">Passeios por dia e quilômetros percorridos.</p>
            <div class="chart-box"><canvas id="activity" aria-label="Gráfico de passeios por dia"></canvas></div>
        </section>
        <section class="panel">
            <h2>Ranking da semana</h2>
            <p class="sub">Pets que mais andaram nos últimos 7 dias.</p>
            <ol class="rank">
                @forelse ($ranking as $pet)
                    <li>
                        <div><b>{{ $pet->name }}</b><small>{{ $pet->user->name }} · {{ $pet->week_walks }} passeios</small></div>
                        <span class="km">{{ number_format(($pet->week_meters ?? 0) / 1000, 1, ',', '.') }} km</span>
                    </li>
                @empty
                    <li class="empty">Nenhum passeio nesta semana.</li>
                @endforelse
            </ol>
        </section>
    </div>

    <div class="row">
        <section class="panel">
            <h2>Passeios recentes</h2>
            <p class="sub">Os 10 últimos registros do app.</p>
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Pet</th><th>Tutor</th><th>Quando</th><th>Duração</th><th>Distância</th><th>Passos</th><th>Marcações</th></tr></thead>
                    <tbody>
                    @forelse ($recentWalks as $walk)
                        <tr>
                            <td><b>{{ $walk->pet->name }}</b></td>
                            <td>{{ $walk->user->name }}</td>
                            <td>{{ $walk->started_at->diffForHumans() }}</td>
                            <td class="num">{{ intdiv($walk->duration_seconds, 60) }} min</td>
                            <td class="num">{{ number_format($walk->distance_meters / 1000, 2, ',', '.') }} km</td>
                            <td class="num">{{ number_format($walk->steps, 0, ',', '.') }}</td>
                            <td class="num">{{ $walk->events_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">Nenhum passeio registrado ainda.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <section class="panel">
            <h2>Saúde em dia</h2>
            <p class="sub">Vacinas e remédios vencendo nos próximos 14 dias.</p>
            <ul class="due">
                @forelse ($dueHealth as $record)
                    @php $late = $record->next_date->isPast() && ! $record->next_date->isToday(); @endphp
                    <li class="{{ $late ? 'late' : '' }}">
                        <div><b>{{ $record->title }}</b><small>{{ $record->pet->name }} · {{ $record->pet->user->name }}</small></div>
                        <span class="pill">{{ $late ? 'Atrasada' : ($record->next_date->isToday() ? 'Hoje' : $record->next_date->format('d/m')) }}</span>
                    </li>
                @empty
                    <li class="empty">Nada vencendo nas próximas duas semanas.</li>
                @endforelse
            </ul>
        </section>
    </div>

    <div class="row">
        <section class="panel">
            <h2>Tutores</h2>
            <p class="sub">Cadastros mais recentes.</p>
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Nome</th><th>E-mail</th><th>Pets</th><th>Passeios</th><th>Desde</th></tr></thead>
                    <tbody>
                    @foreach ($tutors as $tutor)
                        <tr>
                            <td><b>{{ $tutor->name }}</b></td>
                            <td>{{ $tutor->email }}</td>
                            <td class="num">{{ $tutor->pets_count }}</td>
                            <td class="num">{{ $tutor->walks_count }}</td>
                            <td>{{ $tutor->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        <section class="panel">
            <h2>Marcações da semana</h2>
            <p class="sub">Registradas com um toque durante os passeios.</p>
            <div class="events">
                <div class="event pee"><strong>{{ $eventsWeek['pee'] ?? 0 }}</strong><span>Xixi</span></div>
                <div class="event poop"><strong>{{ $eventsWeek['poop'] ?? 0 }}</strong><span>Cocô</span></div>
                <div class="event water"><strong>{{ $eventsWeek['water'] ?? 0 }}</strong><span>Água</span></div>
            </div>
        </section>
    </div>
</main>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
    const routes = @json($routes);
    const chart = @json($chart);
    const css = (v) => getComputedStyle(document.documentElement).getPropertyValue(v).trim();

    // Mapa: tiles escuros + trajetos na cor da bola de tênis
    const map = L.map('map', { zoomControl: false, attributionControl: true, scrollWheelZoom: false });
    L.control.zoom({ position: 'bottomright' }).addTo(map);
    // Tiles do OSM (sem chave) escurecidos via CSS para o tom pinheiro da marca
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap', maxZoom: 19,
    }).addTo(map);

    // A view precisa existir antes das linhas, senão o Leaflet não cria o SVG delas
    const bounds = routes.flatMap((r) => r.points ?? []);
    bounds.length ? map.fitBounds(bounds, { padding: [30, 30] }) : map.setView([-23.5874, -46.6576], 14);

    const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
    // desenha do mais antigo para o mais recente, para o destaque ficar por cima
    [...routes].reverse().forEach((r, idx) => {
        const latest = idx === routes.length - 1;
        if (!r.points?.length) return;
        const line = L.polyline(r.points, {
            color: latest ? css('--ball') : css('--puddle'),
            weight: latest ? 5 : 2.5,
            opacity: latest ? 1 : 0.45,
            lineCap: 'round',
        }).bindTooltip(`${r.pet} · ${String(r.km).replace('.', ',')} km`, { sticky: true }).addTo(map);

        const el = line.getElement?.();
        if (latest && el && !reduce) {
            // anima o passeio mais recente sendo "desenhado"
            const len = el.getTotalLength();
            el.style.strokeDasharray = len;
            el.style.strokeDashoffset = len;
            el.getBoundingClientRect();
            el.style.transition = 'stroke-dashoffset 2.4s ease-out';
            requestAnimationFrame(() => (el.style.strokeDashoffset = 0));
        }
    });

    // Gráfico
    Chart.defaults.font.family = css('--body').split(',')[0].replace(/'/g, '');
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
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: css('--line') }, title: { display: true, text: 'Passeios' } },
                y1: { beginAtZero: true, position: 'right', grid: { display: false }, title: { display: true, text: 'Km' } },
            },
        },
    });
</script>
@endpush
