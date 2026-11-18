@extends('monitor.app')
@section('title', 'Pets')

@section('content')
<div class="page-head">
    <div>
        <h1>Pets</h1>
        <p>{{ $pets->total() }} {{ $pets->total() === 1 ? 'pet cadastrado' : 'pets cadastrados' }}. Atividade dos últimos 7 dias comparada com a meta.</p>
    </div>
</div>

<form class="filters" method="GET" action="{{ route('monitor.pets') }}">
    <label class="search">
        <x-monitor.icon name="search" :size="18" />
        <input type="search" name="q" value="{{ $search }}" placeholder="Buscar por nome ou raça" aria-label="Buscar por nome ou raça">
    </label>
    <input type="hidden" name="species" value="{{ $species }}">
    <nav class="seg" aria-label="Espécie">
        @foreach ($speciesOptions as $key => $label)
            <a href="{{ route('monitor.pets', array_filter(['species' => $key, 'q' => $search])) }}" @if ($species === $key) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
    </nav>
</form>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Pet</th><th>Pai/mãe de pet</th><th>Raça</th><th>Peso</th><th>Km na semana</th><th>Meta da semana</th></tr></thead>
            <tbody>
            @forelse ($pets as $pet)
                @php
                    $weekMinutes = intdiv((int) ($pet->week_seconds ?? 0), 60);
                    $goal = $pet->daily_goal_minutes * 7;
                    $pct = $goal > 0 ? min(100, (int) round($weekMinutes / $goal * 100)) : 0;
                @endphp
                <tr class="link" data-href="{{ route('monitor.tutors.show', $pet->user) }}">
                    <td>
                        <div class="who">
                            <span class="avatar {{ $pet->species === 'cat' ? 'cat' : '' }}">{{ mb_substr($pet->name, 0, 1) }}</span>
                            <div><b>{{ $pet->name }}</b><br><small class="muted">{{ $pet->species === 'cat' ? 'Gato' : 'Cachorro' }}</small></div>
                        </div>
                    </td>
                    <td><a href="{{ route('monitor.tutors.show', $pet->user) }}">{{ $pet->user->name }}</a></td>
                    <td class="muted">{{ $pet->breed ?? '—' }}</td>
                    <td class="num">{{ $pet->weight_kg ? number_format($pet->weight_kg, 1, ',', '.').' kg' : '—' }}</td>
                    <td class="num">{{ number_format(($pet->week_meters ?? 0) / 1000, 1, ',', '.') }}</td>
                    <td>
                        <span class="badge {{ $pct >= 100 ? 'ok' : ($pct >= 50 ? 'soon' : 'later') }}">{{ $pct }}% de {{ $goal }} min</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty"><b>Nenhum pet encontrado.</b>Tente outra busca ou espécie.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $pets->links('monitor.pagination') }}
</section>
@endsection
