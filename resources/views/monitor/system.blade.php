@extends('monitor.app')
@section('title', 'Sistema')

@php
    $names = ['database' => 'Banco de dados', 'cache' => 'Cache', 'storage' => 'Armazenamento de fotos'];
@endphp

@push('styles')
<style>
    .status-hero { display: flex; align-items: center; gap: 18px; padding: 22px; border-radius: var(--r-md); }
    .status-hero.ok { background: var(--pine); color: var(--fog); }
    .status-hero.fail { background: #FFE3E6; color: #7A1222; }
    .status-hero .ring { width: 54px; height: 54px; border-radius: 50%; display: grid; place-items: center; flex: none; }
    .status-hero.ok .ring { background: var(--ball); color: var(--pine); }
    .status-hero.fail .ring { background: var(--tongue); color: var(--pine); }
    .status-hero h2 { font-size: 24px; }
    .status-hero p { margin: 4px 0 0; opacity: .8; }
    .checks { display: grid; gap: 10px; }
    .check { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; border-radius: var(--r-sm); background: var(--fog); }
    .check b { display: block; }
    .check small { color: var(--muted); }
    .facts div { display: flex; justify-content: space-between; gap: 12px; padding: 11px 0; border-bottom: 1px solid var(--line); }
    .facts div:last-child { border-bottom: 0; }
    .facts dt { color: var(--muted); font-weight: 700; }
    .facts dd { margin: 0; font-weight: 800; text-align: right; overflow-wrap: anywhere; }
    .facts { margin: 0; }
    code { font-size: 13px; background: var(--fog); padding: 2px 6px; border-radius: 6px; }
</style>
@endpush

@section('content')
<div class="page-head">
    <div>
        <h1>Sistema</h1>
        <p>Saúde da API e informações do ambiente. A mesma checagem responde em <code>GET /api/health</code>.</p>
    </div>
</div>

<section class="status-hero {{ $health['healthy'] ? 'ok' : 'fail' }}" aria-live="polite">
    <span class="ring"><x-monitor.icon :name="$health['healthy'] ? 'check' : 'alert'" :size="26" /></span>
    <div>
        <h2>{{ $health['healthy'] ? 'Tudo funcionando' : 'Atenção: algo falhou' }}</h2>
        <p>Verificado agora, {{ now()->format('H:i:s') }}. Último passeio salvo {{ $lastWalk ? \Illuminate\Support\Carbon::parse($lastWalk)->diffForHumans() : 'nunca' }}.</p>
    </div>
</section>

<div class="grid grid-1-1">
    <section class="panel">
        <div class="panel-head"><h2>Checagens</h2></div>
        <div class="checks">
            @foreach ($health['checks'] as $key => $check)
                <div class="check">
                    <div><b>{{ $names[$key] }}</b><small>{{ $check['ms'] }} ms{{ isset($check['error']) ? ' · '.$check['error'] : '' }}</small></div>
                    <span class="badge {{ $check['status'] === 'ok' ? 'ok' : 'fail' }}"><span class="dot"></span>{{ $check['status'] === 'ok' ? 'OK' : 'Falhou' }}</span>
                </div>
            @endforeach
        </div>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Ambiente</h2></div>
        <dl class="facts">
            @foreach ($info as $label => $value)
                <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
            @endforeach
        </dl>
    </section>
</div>

<section class="panel">
    <div class="panel-head"><h2>Dados</h2></div>
    <dl class="facts">
        @foreach ($counts as $label => $value)
            <div><dt>{{ $label }}</dt><dd>{{ number_format($value, 0, ',', '.') }}</dd></div>
        @endforeach
    </dl>
</section>
@endsection
