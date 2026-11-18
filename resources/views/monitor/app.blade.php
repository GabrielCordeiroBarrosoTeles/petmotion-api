@extends('monitor.layout')

@php
    $navItems = [
        ['route' => 'monitor', 'match' => 'monitor', 'icon' => 'home', 'label' => 'Visão geral'],
        ['route' => 'monitor.walks', 'match' => 'monitor.walks*', 'icon' => 'route', 'label' => 'Passeios', 'badge' => $nav['walksToday'], 'badgeTitle' => 'passeios hoje'],
        ['route' => 'monitor.tutors', 'match' => 'monitor.tutors*', 'icon' => 'users', 'label' => 'Pais de pet'],
        ['route' => 'monitor.pets', 'match' => 'monitor.pets*', 'icon' => 'paw', 'label' => 'Pets'],
        ['route' => 'monitor.health', 'match' => 'monitor.health', 'icon' => 'heart', 'label' => 'Saúde', 'badge' => $nav['lateHealth'], 'badgeTitle' => 'cuidados atrasados', 'alert' => true],
        ['route' => 'monitor.system', 'match' => 'monitor.system', 'icon' => 'server', 'label' => 'Sistema'],
    ];
    $me = auth()->user();
    $initials = collect(explode(' ', $me->name))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
@endphp

@push('styles')
<style>
    :root { --sidebar: 268px; }

    /* ---------- Sidebar ---------- */
    .sidebar {
        position: fixed; inset: 0 auto 0 0; width: var(--sidebar); z-index: 40;
        background: var(--pine); color: var(--fog);
        display: flex; flex-direction: column; gap: 28px;
        padding: 26px 16px 18px;
        transition: transform .3s cubic-bezier(.2, .8, .2, 1);
    }
    .sidebar .brand { color: var(--fog); padding: 0 10px; }
    .sidebar .brand small { font: 600 13px var(--body); color: #A9BDB3; margin-left: 2px; }
    .nav { display: grid; gap: 4px; }
    .nav a {
        position: relative; display: flex; align-items: center; gap: 12px;
        padding: 11px 12px; border-radius: 12px;
        color: #A9BDB3; text-decoration: none; font-weight: 700; font-size: 15px;
        transition: background-color .15s, color .15s;
    }
    .nav a:hover { background: rgba(255, 255, 255, .05); color: var(--fog); }
    .nav a[aria-current=page] { background: var(--pine-2); color: var(--fog); }
    .nav a[aria-current=page]::before {
        content: ''; position: absolute; left: -16px; top: 9px; bottom: 9px; width: 4px;
        border-radius: 0 4px 4px 0; background: var(--ball);
    }
    .nav a[aria-current=page] .icon { color: var(--ball); }
    .nav .count {
        margin-left: auto; min-width: 24px; padding: 2px 8px; border-radius: 999px; text-align: center;
        font: 800 12px/18px var(--body); background: var(--pine-2); color: var(--fog);
    }
    .nav a[aria-current=page] .count:not(.alert) { background: var(--pine); }
    .nav .count.alert { background: var(--tongue); color: var(--pine); }

    .sidebar-foot { margin-top: auto; display: grid; gap: 10px; }
    .site-link { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 12px; color: #A9BDB3; text-decoration: none; font-weight: 700; font-size: 14px; }
    .site-link:hover { color: var(--fog); background: rgba(255, 255, 255, .05); }
    .me { display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 16px; background: var(--pine-2); }
    .me .avatar { background: var(--ball); color: var(--pine); }
    .me-text { min-width: 0; flex: 1; }
    .me-text b { display: block; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .me-text small { display: block; font-size: 12px; color: #A9BDB3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .icon-btn {
        display: grid; place-items: center; width: 38px; height: 38px; border-radius: 10px; border: 0; cursor: pointer;
        background: transparent; color: #A9BDB3; flex: none;
    }
    .icon-btn:hover { background: rgba(255, 255, 255, .08); color: var(--fog); }

    /* ---------- Conteúdo ---------- */
    .main { margin-left: var(--sidebar); min-height: 100vh; }
    .topbar { display: none; }
    .page { padding: 36px clamp(20px, 3vw, 48px) 64px; max-width: 1640px; display: grid; gap: 22px; }
    .page-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 4px; }
    .page-head h1 { font-size: clamp(28px, 3vw, 38px); font-weight: 800; letter-spacing: -.03em; }
    .page-head p { margin: 6px 0 0; color: var(--muted); }
    .back { display: inline-flex; align-items: center; gap: 6px; color: var(--muted); font-weight: 700; font-size: 14px; text-decoration: none; margin-bottom: 10px; }
    .back:hover { color: var(--pine); }

    /* ---------- Peças compartilhadas ---------- */
    .panel { background: var(--surface); border-radius: var(--r-md); padding: 22px; border: 1px solid var(--line); min-width: 0; }
    .panel-head { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
    .panel-head h2 { font-size: 19px; letter-spacing: -.01em; }
    .panel-head a { font-weight: 700; font-size: 14px; color: var(--muted); text-decoration: none; }
    .panel-head a:hover { color: var(--pine); }
    .grid { display: grid; gap: 22px; }
    .grid-2-1 { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
    .grid-1-1 { grid-template-columns: repeat(2, minmax(0, 1fr)); }

    .kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
    .kpi { padding: 18px 20px; border-radius: var(--r-md); background: var(--surface); border: 1px solid var(--line); display: grid; gap: 6px; }
    .kpi span { color: var(--muted); font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px; }
    .kpi strong { font: 800 34px/1 var(--display); letter-spacing: -.03em; }
    .kpi small { font-weight: 700; font-size: 13px; color: var(--muted); }
    .kpi.hot { background: var(--pine); border-color: var(--pine); color: var(--fog); }
    .kpi.hot span, .kpi.hot small { color: #A9BDB3; }
    .kpi.hot strong { color: var(--ball); }
    .trend-up { color: #2F7D4E !important; }
    .trend-down { color: #B4231A !important; }
    .kpi-link { text-decoration: none; color: inherit; transition: transform .15s; }
    .kpi-link:hover { transform: translateY(-2px); }
    .kpi.alert { border-color: var(--tongue); box-shadow: inset 4px 0 0 var(--tongue); }

    .table-wrap { overflow-x: auto; margin: 0 -22px -22px; }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th { text-align: left; color: var(--muted); font-weight: 700; padding: 12px 22px; border-bottom: 1px solid var(--line); white-space: nowrap; background: #F3F6F1; }
    td { padding: 13px 22px; border-bottom: 1px solid var(--line); white-space: nowrap; }
    tbody tr:last-child td { border-bottom: 0; }
    tbody tr.link { cursor: pointer; transition: background-color .12s; }
    tbody tr.link:hover { background: #F3F6F1; }
    td.num { font-variant-numeric: tabular-nums; font-weight: 700; }
    td a { text-decoration: none; font-weight: 800; }
    td a:hover { text-decoration: underline; text-decoration-color: var(--ball-deep); text-decoration-thickness: 2px; }
    .muted { color: var(--muted); }

    .who { display: flex; align-items: center; gap: 12px; }
    .avatar {
        width: 36px; height: 36px; border-radius: 50%; flex: none; display: grid; place-items: center;
        font: 800 14px var(--display); background: #FFF1C9; color: var(--pine);
    }
    .avatar.cat { background: #FFE3E6; }
    .avatar.lg { width: 64px; height: 64px; font-size: 26px; }

    .badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; font-weight: 800; font-size: 12px; white-space: nowrap; }
    .badge.ok { background: #E3F4DC; color: #24613C; }
    .badge.late { background: var(--tongue); color: var(--pine); }
    .badge.soon { background: var(--sun); color: var(--pine); }
    .badge.later { background: var(--fog); color: var(--pine); }
    .badge.fail { background: #FFE3E6; color: #9B1C2C; }
    .badge.pack { background: var(--pine); color: var(--ball); margin-left: 6px; vertical-align: 1px; }
    .dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; }

    .filters { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
    .search {
        flex: 1; min-width: 220px; max-width: 420px; display: flex; align-items: center; gap: 10px;
        padding: 0 14px; border-radius: 12px; border: 2px solid var(--line); background: var(--surface); color: var(--muted);
    }
    .search:focus-within { border-color: var(--pine); color: var(--pine); }
    .search input { flex: 1; border: 0; outline: none; background: transparent; padding: 12px 0; font: 600 15px var(--body); color: var(--pine); }
    .seg { display: inline-flex; padding: 4px; border-radius: 12px; background: #E3EAE2; }
    .seg a { padding: 8px 14px; border-radius: 9px; font-weight: 800; font-size: 14px; color: var(--muted); text-decoration: none; }
    .seg a[aria-current=true] { background: var(--pine); color: var(--fog); }

    .empty { padding: 40px 12px; text-align: center; color: var(--muted); font-weight: 600; }
    .empty b { display: block; color: var(--pine); font: 800 18px var(--display); margin-bottom: 4px; }

    .pagination { display: flex; gap: 6px; justify-content: flex-end; padding-top: 16px; flex-wrap: wrap; }
    .pagination a, .pagination span {
        min-width: 38px; padding: 8px 12px; border-radius: 10px; text-align: center; font-weight: 800; font-size: 14px;
        text-decoration: none; color: var(--pine); background: var(--surface); border: 1px solid var(--line);
    }
    .pagination [aria-current] { background: var(--pine); color: var(--fog); border-color: var(--pine); }
    .pagination .disabled { color: #B7C2BB; }

    .map { border-radius: var(--r-md); overflow: hidden; background: var(--pine-2); }
    .map .leaflet-tile-pane { filter: grayscale(1) invert(1) brightness(.62) contrast(1.1) sepia(.35) hue-rotate(110deg) saturate(1.4); }
    .map .leaflet-control-attribution { background: rgba(15, 42, 36, .7); color: #A9BDB3; }
    .map .leaflet-control-attribution a { color: var(--ball); }

    /* ---------- Celular: sidebar vira gaveta ---------- */
    .scrim { display: none; }
    @media (max-width: 1100px) {
        .kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-2-1, .grid-1-1 { grid-template-columns: 1fr; }
    }
    @media (max-width: 960px) {
        .sidebar { transform: translateX(-100%); box-shadow: 20px 0 60px rgba(0, 0, 0, .3); }
        body.nav-open .sidebar { transform: none; }
        .scrim { display: block; position: fixed; inset: 0; z-index: 30; background: rgba(15, 42, 36, .5); opacity: 0; pointer-events: none; transition: opacity .3s; }
        body.nav-open .scrim { opacity: 1; pointer-events: auto; }
        .main { margin-left: 0; }
        .topbar { display: flex; align-items: center; gap: 10px; padding: 14px 16px; background: var(--pine); color: var(--fog); position: sticky; top: 0; z-index: 20; }
        .topbar .brand { color: var(--fog); }
        .topbar .icon-btn { color: var(--fog); }
        .page { padding-top: 24px; }
    }
    @media (max-width: 560px) {
        .kpis { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('body')
<aside class="sidebar" id="sidebar" aria-label="Menu do monitor">
    <a class="brand" href="{{ route('monitor') }}"><span class="brand-ball"></span>PetMotion <small>monitor</small></a>

    <nav class="nav">
        @foreach ($navItems as $item)
            <a href="{{ route($item['route']) }}" @if (request()->routeIs($item['match'])) aria-current="page" @endif>
                <x-monitor.icon :name="$item['icon']" />
                {{ $item['label'] }}
                @if (! empty($item['badge']))
                    <span class="count {{ ! empty($item['alert']) ? 'alert' : '' }}" title="{{ $item['badge'] }} {{ $item['badgeTitle'] }}">{{ $item['badge'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    <div class="sidebar-foot">
        <a class="site-link" href="{{ route('landing') }}" target="_blank" rel="noopener"><x-monitor.icon name="globe" :size="18" />Ver o site</a>
        <div class="me">
            <span class="avatar">{{ $initials }}</span>
            <div class="me-text"><b>{{ $me->name }}</b><small>{{ $me->email }}</small></div>
            <form method="POST" action="{{ route('monitor.logout') }}">
                @csrf
                <button class="icon-btn" type="submit" aria-label="Sair" title="Sair"><x-monitor.icon name="logout" :size="18" /></button>
            </form>
        </div>
    </div>
</aside>
<div class="scrim" data-close-nav></div>

<div class="main">
    <header class="topbar">
        <button class="icon-btn" type="button" aria-label="Abrir menu" aria-controls="sidebar" data-open-nav><x-monitor.icon name="menu" /></button>
        <a class="brand" href="{{ route('monitor') }}"><span class="brand-ball"></span>PetMotion</a>
    </header>

    <main class="page">
        @yield('content')
    </main>
</div>
@endsection

@push('scripts')
<script>
    document.querySelector('[data-open-nav]').addEventListener('click', () => document.body.classList.add('nav-open'));
    document.querySelector('[data-close-nav]').addEventListener('click', () => document.body.classList.remove('nav-open'));
    addEventListener('keydown', (e) => e.key === 'Escape' && document.body.classList.remove('nav-open'));
    // linhas clicáveis das tabelas
    document.querySelectorAll('tr[data-href]').forEach((tr) => tr.addEventListener('click', (e) => {
        if (!e.target.closest('a')) location.href = tr.dataset.href;
    }));
</script>
@endpush
