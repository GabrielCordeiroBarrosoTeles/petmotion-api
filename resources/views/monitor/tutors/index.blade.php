@extends('monitor.app')
@section('title', 'Tutores')

@section('content')
<div class="page-head">
    <div>
        <h1>Tutores</h1>
        <p>{{ $tutors->total() }} {{ $tutors->total() === 1 ? 'pessoa cadastrada' : 'pessoas cadastradas' }} no app.</p>
    </div>
</div>

<form class="filters" method="GET" action="{{ route('monitor.tutors') }}">
    <label class="search">
        <x-monitor.icon name="search" :size="18" />
        <input type="search" name="q" value="{{ $search }}" placeholder="Buscar por nome ou e-mail" aria-label="Buscar por nome ou e-mail">
    </label>
</form>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Tutor</th><th>Pets</th><th>Passeios</th><th>Km no total</th><th>Último passeio</th><th>Desde</th></tr></thead>
            <tbody>
            @forelse ($tutors as $tutor)
                <tr class="link" data-href="{{ route('monitor.tutors.show', $tutor) }}">
                    <td>
                        <div class="who">
                            <span class="avatar">{{ mb_strtoupper(mb_substr($tutor->name, 0, 1)) }}</span>
                            <div><a href="{{ route('monitor.tutors.show', $tutor) }}">{{ $tutor->name }}</a>@if ($tutor->is_premium) <span class="badge pack">Matilha</span>@endif<br><small class="muted">{{ $tutor->email }}</small></div>
                        </div>
                    </td>
                    <td class="num">{{ $tutor->pets_count }}</td>
                    <td class="num">{{ $tutor->walks_count }}</td>
                    <td class="num">{{ number_format(($tutor->walks_sum_distance_meters ?? 0) / 1000, 1, ',', '.') }}</td>
                    <td class="muted">{{ $tutor->walks_max_started_at ? \Illuminate\Support\Carbon::parse($tutor->walks_max_started_at)->diffForHumans() : 'Nunca passeou' }}</td>
                    <td class="muted">{{ $tutor->created_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty"><b>Ninguém encontrado.</b>Confira o nome ou o e-mail buscado.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $tutors->links('monitor.pagination') }}
</section>
@endsection
