@extends('monitor.app')
@section('title', 'Passeio de '.$walk->pet->name)

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
@endpush

@push('styles')
<style>
    .walk-map { height: 460px; }
    .facts { display: grid; gap: 0; }
    .facts div { display: flex; justify-content: space-between; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--line); }
    .facts div:last-child { border-bottom: 0; }
    .facts dt { color: var(--muted); font-weight: 700; }
    .facts dd { margin: 0; font-weight: 800; font-variant-numeric: tabular-nums; text-align: right; }
    .legend { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 12px; font-weight: 700; font-size: 13px; color: var(--muted); }
    .lg-route { background: var(--ball); } .lg-pee { background: var(--sun); } .lg-poop { background: #B98B5E; } .lg-water { background: var(--puddle); }
    .legend i { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 6px; vertical-align: -1px; border: 2px solid #fff; box-shadow: 0 0 0 1px var(--line); }
</style>
@endpush

@section('content')
<div class="page-head">
    <div>
        <a class="back" href="{{ route('monitor.walks') }}"><x-monitor.icon name="arrow-left" :size="16" />Passeios</a>
        <h1>{{ $walk->pet->name }}, {{ number_format($walk->distance_meters / 1000, 2, ',', '.') }} km</h1>
        <p>{{ $walk->started_at->translatedFormat('j \d\e F \d\e Y, H:i') }} com <a href="{{ route('monitor.tutors.show', $walk->user) }}">{{ $walk->user->name }}</a></p>
    </div>
</div>

<div class="grid grid-2-1">
    <section class="panel">
        <div id="walk-map" class="map walk-map" role="img" aria-label="Trajeto do passeio com {{ $walk->events->count() }} marcações"></div>
        <div class="legend">
            <span><i class="lg-route"></i>Trajeto</span>
            <span><i class="lg-pee"></i>Xixi</span>
            <span><i class="lg-poop"></i>Cocô</span>
            <span><i class="lg-water"></i>Água</span>
        </div>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Resumo</h2></div>
        <dl class="facts">
            <div><dt>Duração</dt><dd>{{ intdiv($walk->duration_seconds, 60) }} min</dd></div>
            <div><dt>Distância</dt><dd>{{ number_format($walk->distance_meters / 1000, 2, ',', '.') }} km</dd></div>
            <div><dt>Passos</dt><dd>{{ number_format($walk->steps, 0, ',', '.') }}</dd></div>
            <div><dt>Ritmo</dt><dd>{{ $walk->distance_meters >= 50 ? gmdate('i:s', (int) ($walk->duration_seconds / ($walk->distance_meters / 1000))).' min/km' : '—' }}</dd></div>
            <div><dt>Temperatura</dt><dd>{{ $walk->temperature_c !== null ? round($walk->temperature_c).' °C' : '—' }}</dd></div>
            <div><dt>Xixi · Cocô · Água</dt><dd>{{ $walk->events->where('type', 'pee')->count() }} · {{ $walk->events->where('type', 'poop')->count() }} · {{ $walk->events->where('type', 'water')->count() }}</dd></div>
            <div><dt>Pontos de GPS</dt><dd>{{ count($walk->points) }}</dd></div>
        </dl>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
    const points = @json($walk->points);
    const events = @json($walk->events->map->only(['type', 'lat', 'lng']));
    const colors = { pee: '#FFC24D', poop: '#B98B5E', water: '#7FB8E6' };

    const map = L.map('walk-map', { scrollWheelZoom: false });
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(map);
    points.length ? map.fitBounds(points, { padding: [40, 40] }) : map.setView([-23.5874, -46.6576], 14);
    if (points.length) {
        L.polyline(points, { color: '#0F2A24', weight: 10, opacity: .9, lineCap: 'round' }).addTo(map);
        L.polyline(points, { color: '#C9F24B', weight: 5, lineCap: 'round' }).addTo(map);
        L.circleMarker(points[0], { radius: 7, color: '#0F2A24', weight: 3, fillColor: '#EEF2EC', fillOpacity: 1 }).bindTooltip('Início').addTo(map);
    }
    events.forEach((e) => L.circleMarker([e.lat, e.lng], { radius: 8, color: '#FAFCF8', weight: 3, fillColor: colors[e.type], fillOpacity: 1 }).addTo(map));
</script>
@endpush
