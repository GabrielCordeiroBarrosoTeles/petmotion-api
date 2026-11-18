@extends('monitor.app')
@section('title', $tutor->name)

@push('styles')
<style>
    .profile { display: flex; align-items: center; gap: 18px; }
    .pet-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; }
    .pet-card { padding: 18px; border-radius: var(--r-md); background: var(--fog); display: grid; gap: 10px; }
    .pet-card b { font: 800 19px var(--display); }
    .pet-card .stats { display: flex; gap: 18px; font-weight: 700; color: var(--muted); font-size: 13px; }
    .pet-card .stats strong { display: block; color: var(--pine); font: 800 20px var(--display); }
</style>
@endpush

@section('content')
<div class="page-head">
    <div>
        <a class="back" href="{{ route('monitor.tutors') }}"><x-monitor.icon name="arrow-left" :size="16" />Pais de pet</a>
        <div class="profile">
            <span class="avatar lg">{{ mb_strtoupper(mb_substr($tutor->name, 0, 1)) }}</span>
            <div>
                <h1>{{ $tutor->name }}</h1>
                <p>{{ $tutor->email }} · desde {{ $tutor->created_at->translatedFormat('F \d\e Y') }}</p>
                <p>
                    @if ($tutor->is_premium)
                        <span class="badge pack">Matilha até {{ $tutor->premium_until->format('d/m/Y') }}</span>
                    @else
                        <span class="badge later">Plano grátis</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

<section class="kpis" aria-label="Resumo do pai/mãe de pet">
    <div class="kpi hot"><span>Passeios</span><strong>{{ $totals['walks'] }}</strong></div>
    <div class="kpi"><span>Km no total</span><strong>{{ number_format($totals['km'], 1, ',', '.') }}</strong></div>
    <div class="kpi"><span>Pets</span><strong>{{ $tutor->pets->count() }}</strong></div>
    <div class="kpi"><span>Último passeio</span><strong>{{ $walks->first()?->started_at->format('d/m') ?? '—' }}</strong></div>
</section>

<section class="panel">
    <div class="panel-head"><h2>Pets</h2></div>
    @if ($tutor->pets->isEmpty())
        <div class="empty"><b>Ainda sem pets.</b>A pessoa criou a conta, mas não cadastrou nenhum pet.</div>
    @else
        <div class="pet-cards">
            @foreach ($tutor->pets as $pet)
                <div class="pet-card">
                    <div class="who">
                        <span class="avatar {{ $pet->species === 'cat' ? 'cat' : '' }}">{{ mb_substr($pet->name, 0, 1) }}</span>
                        <div><b>{{ $pet->name }}</b><br><small class="muted">{{ $pet->breed ?? ($pet->species === 'cat' ? 'Gato' : 'Cachorro') }}</small></div>
                    </div>
                    <div class="stats">
                        <span><strong>{{ $pet->walks_count }}</strong>passeios</span>
                        <span><strong>{{ number_format(($pet->walks_sum_distance_meters ?? 0) / 1000, 1, ',', '.') }}</strong>km</span>
                        <span><strong>{{ $pet->daily_goal_minutes }}</strong>min/dia</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

<section class="panel">
    <div class="panel-head"><h2>Passeios recentes</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Pet</th><th>Início</th><th>Duração</th><th>Distância</th><th>Marcações</th></tr></thead>
            <tbody>
            @forelse ($walks as $walk)
                <tr class="link" data-href="{{ route('monitor.walks.show', $walk) }}">
                    <td><a href="{{ route('monitor.walks.show', $walk) }}">{{ $walk->pet->name }}</a></td>
                    <td class="muted">{{ $walk->started_at->format('d/m H:i') }}</td>
                    <td class="num">{{ intdiv($walk->duration_seconds, 60) }} min</td>
                    <td class="num">{{ number_format($walk->distance_meters / 1000, 2, ',', '.') }} km</td>
                    <td class="num">{{ $walk->events_count }}</td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty"><b>Nenhum passeio ainda.</b></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
