<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invite ? 'Convite para passear com '.$invite->pet->name : 'Convite' }} | PetMotion</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0F2A24">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --fog: #EEF2EC; --surface: #FAFCF8; --ball: #C9F24B; --ball-deep: #A9D12A;
            --pine: #0F2A24; --pine-2: #1C3D35; --on-pine: #EEF2EC; --on-pine-muted: #A9BDB3; --sun-soft: #FFF1C9; --blush-soft: #FFE3E6;
            --display: 'Bricolage Grotesque', 'Avenir Next', system-ui, sans-serif;
            --body: 'Manrope', system-ui, -apple-system, sans-serif;
            --gutter: clamp(20px, 5vw, 64px);
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: var(--pine); color: var(--on-pine); font: 500 17px/1.6 var(--body); -webkit-font-smoothing: antialiased; display: flex; flex-direction: column; }
        .wrap { width: 100%; max-width: 560px; margin: 0 auto; padding-inline: var(--gutter); }
        header .wrap { padding-block: 24px; }
        .brand { display: inline-flex; align-items: center; gap: 10px; font: 800 20px/1 var(--display); color: var(--on-pine); text-decoration: none; }
        .tennis { position: relative; width: 24px; height: 24px; border-radius: 50%; background: var(--ball); overflow: hidden; }
        .tennis::before, .tennis::after { content: ''; position: absolute; top: -10%; width: 95%; height: 120%; border: 2.5px solid #fff; border-radius: 50%; }
        .tennis::before { left: -62%; } .tennis::after { right: -62%; }
        main { flex: 1; display: flex; align-items: center; }
        .card { background: var(--pine-2); border-radius: 28px; padding: clamp(24px, 6vw, 40px); text-align: center; }
        .avatar { width: 120px; height: 120px; border-radius: 50%; margin: 0 auto 20px; display: grid; place-items: center; overflow: hidden; background: var(--sun-soft); color: var(--pine); border: 4px solid var(--ball); font: 800 52px/1 var(--display); }
        .avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .avatar.off { background: var(--on-pine-muted); border-color: var(--on-pine-muted); }
        h1 { font: 800 clamp(30px, 8vw, 42px)/1.05 var(--display); letter-spacing: -.03em; margin: 0 0 12px; }
        p { margin: 0 0 24px; color: var(--on-pine-muted); font-weight: 600; }
        .btn { display: block; width: 100%; background: var(--ball); color: var(--pine); font: 800 17px/1 var(--body); text-decoration: none; padding: 19px 24px; border-radius: 999px; box-shadow: 0 4px 0 var(--ball-deep); transition: transform .12s, box-shadow .12s; }
        .btn:active { transform: translateY(4px); box-shadow: 0 0 0 var(--ball-deep); }
        .btn:focus-visible { outline: 3px solid var(--on-pine); outline-offset: 3px; }
        .hint { margin: 20px 0 0; font-size: 14px; }
        .hint a { color: var(--ball); font-weight: 700; }
        .code { display: inline-block; margin-top: 4px; font: 800 20px/1 var(--display); letter-spacing: .18em; color: var(--on-pine); }
        footer .wrap { padding-block: 24px; font-size: 13px; color: var(--on-pine-muted); text-align: center; }
        footer a { color: var(--on-pine-muted); }
        @media (prefers-reduced-motion: reduce) { .btn { transition: none; } }
    </style>
</head>
<body>
<header><div class="wrap"><a class="brand" href="{{ url('/') }}"><span class="tennis" aria-hidden="true"></span>PetMotion</a></div></header>

<main>
    <div class="wrap">
        <section class="card">
            @if ($invite)
                <div class="avatar" aria-hidden="{{ $invite->pet->photo_url ? 'false' : 'true' }}">
                    @if ($invite->pet->photo_url)
                        <img src="{{ $invite->pet->photo_url }}" alt="Foto de {{ $invite->pet->name }}">
                    @else
                        {{ mb_strtoupper(mb_substr($invite->pet->name, 0, 1)) }}
                    @endif
                </div>
                <h1>{{ $ownerFirstName }} chamou você para passear com {{ $invite->pet->name }}.</h1>
                <p>Como passeador, você registra os passeios e o pai ou a mãe de pet acompanha o trajeto.</p>
                <a class="btn" href="petmotion://convite/{{ $invite->code }}">Abrir no PetMotion</a>
                <p class="hint">Ainda não tem o app? Instale o PetMotion e toque no link de novo. Ou digite o código no app:<br><span class="code">{{ $invite->code }}</span></p>
            @else
                <div class="avatar off" aria-hidden="true">?</div>
                <h1>Esse convite não vale mais.</h1>
                <p>Ele pode ter vencido ou já foi usado. Peça um convite novo para quem chamou você.</p>
                <a class="btn" href="{{ url('/') }}">Conhecer o PetMotion</a>
            @endif
        </section>
    </div>
</main>

<footer><div class="wrap"><a href="{{ route('legal.terms') }}">Termos de uso</a> · <a href="{{ route('legal.privacy') }}">Política de privacidade</a></div></footer>
</body>
</html>
