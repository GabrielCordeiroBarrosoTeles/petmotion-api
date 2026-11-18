@extends('monitor.app')
@section('title', 'Passeios')

@section('content')
<div class="page-head">
    <div>
        <h1>Passeios</h1>
        <p>{{ $summary->walks }} passeios, {{ number_format($summary->meters / 1000, 1, ',', '.') }} km e {{ intdiv((int) $summary->seconds, 3600) }}h{{ str_pad(intdiv((int) $summary->seconds % 3600, 60), 2, '0', STR_PAD_LEFT) }} de caminhada no período.</p>
    </div>
</div>

<form class="filters" method="GET" action="{{ route('monitor.walks') }}">
    <label class="search">
        <x-monitor.icon name="search" :size="18" />
        <input type="search" name="q" value="{{ $search }}" placeholder="Buscar por pet ou tutor" aria-label="Buscar por pet ou tutor">
    </label>
    <input type="hidden" name="period" value="{{ $period }}">
    <nav class="seg" aria-label="Período">
        @foreach ($periods as $key => $label)
            <a href="{{ route('monitor.walks', array_filter(['period' => $key, 'q' => $search])) }}" @if ($period === $key) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
    </nav>
</form>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Pet</th><th>Tutor</th><th>Início</th><th>Duração</th><th>Distância</th><th>Passos</th><th>Marcações</th></tr></thead>
            <tbody>
            @forelse ($walks as $walk)
                <tr class="link" data-href="{{ route('monitor.walks.show', $walk) }}">
                    <td>
                        <div class="who">
                            <span class="avatar {{ $walk->pet->species === 'cat' ? 'cat' : '' }}">{{ mb_substr($walk->pet->name, 0, 1) }}</span>
                            <a href="{{ route('monitor.walks.show', $walk) }}">{{ $walk->pet->name }}</a>
                        </div>
                    </td>
                    <td><a href="{{ route('monitor.tutors.show', $walk->user) }}">{{ $walk->user->name }}</a></td>
                    <td class="muted">{{ $walk->started_at->format('d/m H:i') }}</td>
                    <td class="num">{{ intdiv($walk->duration_seconds, 60) }} min</td>
                    <td class="num">{{ number_format($walk->distance_meters / 1000, 2, ',', '.') }} km</td>
                    <td class="num">{{ number_format($walk->steps, 0, ',', '.') }}</td>
                    <td class="num">{{ $walk->events_count }}</td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty"><b>Nenhum passeio encontrado.</b>Tente outro período ou outra busca.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $walks->links('monitor.pagination') }}
</section>
@endsection
