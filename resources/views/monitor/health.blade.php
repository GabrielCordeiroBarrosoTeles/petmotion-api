@extends('monitor.app')
@section('title', 'Saúde')

@php
    $labels = ['vaccine' => 'Vacina', 'vermifuge' => 'Vermífugo', 'medicine' => 'Remédio', 'vet' => 'Consulta'];
@endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Saúde</h1>
        <p>Vacinas, vermífugos e remédios atrasados ou vencendo nos próximos 30 dias.</p>
    </div>
</div>

<section class="kpis" aria-label="Resumo de saúde">
    <div class="kpi {{ $late->isNotEmpty() ? 'alert' : '' }}"><span>Atrasados</span><strong>{{ $late->count() }}</strong></div>
    <div class="kpi"><span>Próximos 7 dias</span><strong>{{ $soon->filter(fn ($r) => $r->next_date->lte($today->copy()->addDays(7)))->count() }}</strong></div>
    <div class="kpi"><span>Próximos 30 dias</span><strong>{{ $soon->count() }}</strong></div>
    <div class="kpi"><span>Pets envolvidos</span><strong>{{ $late->merge($soon)->pluck('pet_id')->unique()->count() }}</strong></div>
</section>

@foreach (['Atrasados' => $late, 'Vencendo em breve' => $soon] as $title => $records)
    <section class="panel">
        <div class="panel-head"><h2>{{ $title }}</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Cuidado</th><th>Pet</th><th>Tutor</th><th>Próxima dose</th><th>Situação</th></tr></thead>
                <tbody>
                @forelse ($records as $record)
                    @php $days = (int) $today->diffInDays($record->next_date, false); @endphp
                    <tr class="link" data-href="{{ route('monitor.tutors.show', $record->pet->user) }}">
                        <td><b>{{ $record->title }}</b><br><small class="muted">{{ $labels[$record->type] }}</small></td>
                        <td>{{ $record->pet->name }}</td>
                        <td><a href="{{ route('monitor.tutors.show', $record->pet->user) }}">{{ $record->pet->user->name }}</a></td>
                        <td class="num">{{ $record->next_date->format('d/m/Y') }}</td>
                        <td>
                            @if ($days < 0)
                                <span class="badge late"><span class="dot"></span>{{ -$days }} {{ $days === -1 ? 'dia' : 'dias' }} de atraso</span>
                            @elseif ($days === 0)
                                <span class="badge soon">Hoje</span>
                            @elseif ($days <= 7)
                                <span class="badge soon">Em {{ $days }} {{ $days === 1 ? 'dia' : 'dias' }}</span>
                            @else
                                <span class="badge later">Em {{ $days }} dias</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty"><b>{{ $title === 'Atrasados' ? 'Nada atrasado.' : 'Nada vencendo.' }}</b>{{ $title === 'Atrasados' ? 'Todos os pets estão com os cuidados em dia.' : 'Nenhuma dose nos próximos 30 dias.' }}</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endforeach
@endsection
