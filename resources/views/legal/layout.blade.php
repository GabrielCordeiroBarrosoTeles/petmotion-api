<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | PetMotion</title>
    <meta name="description" content="@yield('description')">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#0F2A24">
    <meta property="og:type" content="article">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:site_name" content="PetMotion">
    <meta property="og:title" content="@yield('title') | PetMotion">
    <meta property="og:description" content="@yield('description')">
    <meta property="og:image" content="{{ asset('og.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --fog: #EEF2EC; --surface: #FAFCF8; --surface-alt: #E3EAE2; --ball: #C9F24B; --ball-deep: #A9D12A;
            --pine: #0F2A24; --pine-2: #1C3D35; --on-pine: #EEF2EC; --on-pine-muted: #A9BDB3;
            --muted: #5B6B64; --line: #D9E2DA; --sun-soft: #FFF1C9;
            --display: 'Bricolage Grotesque', 'Avenir Next', system-ui, sans-serif;
            --body: 'Manrope', system-ui, -apple-system, sans-serif;
            --gutter: clamp(20px, 5vw, 64px);
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; scroll-padding-top: 24px; }
        body { margin: 0; background: var(--fog); color: var(--pine); font: 500 17px/1.7 var(--body); -webkit-font-smoothing: antialiased; }
        a { color: var(--pine); text-decoration-color: var(--ball-deep); text-decoration-thickness: 2px; text-underline-offset: 3px; }
        :focus-visible { outline: 3px solid var(--pine); outline-offset: 3px; border-radius: 6px; }
        .wrap { max-width: 1180px; margin: 0 auto; padding-inline: var(--gutter); }

        .top { background: var(--pine); color: var(--on-pine); }
        .top .wrap { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding-block: 22px; }
        .brand { display: inline-flex; align-items: center; gap: 10px; font: 800 20px/1 var(--display); color: var(--on-pine); text-decoration: none; }
        .tennis { position: relative; width: 24px; height: 24px; border-radius: 50%; background: var(--ball); overflow: hidden; }
        .tennis::before, .tennis::after { content: ''; position: absolute; top: -10%; width: 95%; height: 120%; border: 2.5px solid #fff; border-radius: 50%; }
        .tennis::before { left: -62%; } .tennis::after { right: -62%; }
        .top nav { display: flex; gap: 22px; font-weight: 700; font-size: 15px; }
        .top nav a { color: var(--on-pine-muted); text-decoration: none; }
        .top nav a:hover, .top nav a[aria-current=page] { color: var(--ball); }

        .head { background: var(--pine); color: var(--on-pine); padding: 40px 0 72px; border-radius: 0 0 40px 40px; }
        .head h1 { font: 800 clamp(44px, 6.5vw, 84px)/0.98 var(--display); letter-spacing: -.035em; margin: 0; max-width: 14ch; }
        .head p { margin: 18px 0 0; color: var(--on-pine-muted); font-weight: 600; }

        .layout { display: grid; grid-template-columns: 240px minmax(0, 1fr); gap: clamp(32px, 5vw, 80px); padding-block: 56px 96px; }
        .toc { position: sticky; top: 24px; align-self: start; font-size: 14px; }
        .toc b { display: block; font: 800 16px var(--display); margin-bottom: 10px; }
        .toc ol { list-style: none; margin: 0; padding: 0; display: grid; gap: 2px; counter-reset: t; }
        .toc li { counter-increment: t; }
        .toc a { display: grid; grid-template-columns: 26px 1fr; padding: 6px 10px 6px 8px; border-radius: 10px; color: var(--muted); text-decoration: none; font-weight: 700; line-height: 1.35; }
        .toc a::before { content: counter(t) '.'; color: var(--pine); }
        .toc a:hover, .toc a.active { background: var(--surface); color: var(--pine); }
        .toc a.active { box-shadow: inset 3px 0 0 var(--ball-deep); }

        article { max-width: 68ch; counter-reset: s; }
        .summary { background: var(--surface); border-radius: 24px; padding: 26px 28px; box-shadow: inset 0 0 0 2px var(--pine); margin-bottom: 48px; }
        .summary h2 { font: 800 24px/1.2 var(--display); margin: 0 0 12px; letter-spacing: -.01em; }
        .summary ul { margin: 0; padding-left: 20px; display: grid; gap: 8px; }
        .summary li::marker { color: var(--ball-deep); font-size: 1.2em; }
        article section { counter-increment: s; padding-top: 8px; margin-bottom: 40px; }
        article section > h2 { font: 800 clamp(26px, 3vw, 32px)/1.15 var(--display); letter-spacing: -.02em; margin: 0 0 14px; display: grid; grid-template-columns: auto 1fr; gap: 12px; align-items: baseline; }
        article section > h2::before { content: counter(s); display: grid; place-items: center; width: 38px; height: 38px; border-radius: 50%; background: var(--ball); font-size: 18px; transform: translateY(-3px); }
        article h3 { font: 800 19px/1.3 var(--display); margin: 26px 0 8px; }
        article p, article li { color: #22362F; }
        article ul { padding-left: 22px; }
        article li { margin-bottom: 6px; }
        article li::marker { color: var(--ball-deep); }
        .table { overflow-x: auto; margin: 18px 0; border-radius: 18px; box-shadow: inset 0 0 0 1px var(--line); background: var(--surface); }
        table { width: 100%; border-collapse: collapse; font-size: 15px; line-height: 1.5; }
        th { text-align: left; font-weight: 800; padding: 14px 16px; border-bottom: 2px solid var(--line); white-space: nowrap; }
        td { padding: 14px 16px; border-bottom: 1px solid var(--line); vertical-align: top; }
        tr:last-child td { border-bottom: 0; }
        .note { background: var(--sun-soft); border-radius: 18px; padding: 18px 20px; margin: 18px 0; font-weight: 600; }
        mark.todo { background: #FFE3E6; color: #9B1C2C; padding: 1px 6px; border-radius: 6px; font-weight: 800; }

        footer { border-top: 1px solid var(--line); }
        footer .wrap { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding-block: 28px 44px; color: var(--muted); font-size: 14px; font-weight: 600; }
        footer nav { display: flex; gap: 20px; }

        @media (max-width: 900px) {
            .layout { grid-template-columns: 1fr; }
            .toc { position: static; background: var(--surface); border-radius: 20px; padding: 18px; }
            .top nav a:not([aria-current]) { display: none; }
        }
    </style>
</head>
<body>
    <header class="top">
        <div class="wrap">
            <a class="brand" href="{{ route('landing') }}"><span class="tennis"></span>PetMotion</a>
            <nav aria-label="Documentos">
                <a href="{{ route('landing') }}">Início</a>
                <a href="{{ route('legal.privacy') }}" @if (request()->routeIs('legal.privacy')) aria-current="page" @endif>Privacidade</a>
                <a href="{{ route('legal.terms') }}" @if (request()->routeIs('legal.terms')) aria-current="page" @endif>Termos de uso</a>
            </nav>
        </div>
    </header>

    <div class="head">
        <div class="wrap">
            <h1>@yield('title')</h1>
            <p>Atualizado em {{ $updated }}.</p>
        </div>
    </div>

    <div class="wrap layout">
        <nav class="toc" aria-label="Nesta página">
            <b>Nesta página</b>
            <ol id="toc"></ol>
        </nav>
        <article>
            @yield('content')
        </article>
    </div>

    <footer>
        <div class="wrap">
            <span>{!! $fill($legal['name'], 'preencher LEGAL_NAME') !!}. Dúvidas: {!! $contact !!}</span>
            <nav>
                <a href="{{ route('legal.privacy') }}">Privacidade</a>
                <a href="{{ route('legal.terms') }}">Termos de uso</a>
                <a href="{{ route('landing') }}">Site</a>
            </nav>
        </div>
    </footer>

    <script>
        // Índice gerado a partir dos títulos das seções + destaque da seção visível
        const toc = document.getElementById('toc');
        const sections = [...document.querySelectorAll('article section[id]')];
        sections.forEach((s) => {
            const li = document.createElement('li');
            li.innerHTML = `<a href="#${s.id}">${s.querySelector('h2').textContent}</a>`;
            toc.appendChild(li);
        });
        const links = [...toc.querySelectorAll('a')];
        const io = new IntersectionObserver((entries) => {
            entries.forEach((e) => {
                if (!e.isIntersecting) return;
                links.forEach((a) => a.classList.toggle('active', a.getAttribute('href') === `#${e.target.id}`));
            });
        }, { rootMargin: '-20% 0px -70% 0px' });
        sections.forEach((s) => io.observe(s));
    </script>
</body>
</html>
