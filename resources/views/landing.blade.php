<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $seoTitle = 'PetMotion: app de passeio para cachorro e gato';
        $seoDesc = 'Registre os passeios do seu cachorro ou gato com mapa, tempo e passos, receba lembretes de vacina e saiba quando o asfalto está quente demais. Sem coleira GPS.';
        $canonical = route('landing');
        $ogImage = asset('og.png');
        $schema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => $canonical.'#org',
                    'name' => 'PetMotion',
                    'url' => $canonical,
                    'logo' => asset('icon.png'),
                    'areaServed' => 'BR',
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $canonical.'#site',
                    'url' => $canonical,
                    'name' => 'PetMotion',
                    'inLanguage' => 'pt-BR',
                    'publisher' => ['@id' => $canonical.'#org'],
                ],
                [
                    '@type' => 'MobileApplication',
                    'name' => 'PetMotion',
                    'operatingSystem' => 'Android, iOS',
                    'applicationCategory' => 'LifestyleApplication',
                    'inLanguage' => 'pt-BR',
                    'description' => $seoDesc,
                    'image' => $ogImage,
                    'publisher' => ['@id' => $canonical.'#org'],
                    'featureList' => [
                        'Registro de passeios com mapa, tempo e passos',
                        'Marcação de xixi, cocô e água durante o passeio',
                        'Alerta de asfalto quente para as patas',
                        'Lembretes de vacina, vermífugo e remédio',
                        'Conquistas e sequência de dias passeando',
                        'Vários pets na mesma conta, cachorro ou gato',
                    ],
                ],
                [
                    '@type' => 'FAQPage',
                    'mainEntity' => array_map(fn ($f) => [
                        '@type' => 'Question',
                        'name' => $f['q'],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
                    ], $faq),
                ],
            ],
        ];
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDesc }}">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <link rel="canonical" href="{{ $canonical }}">
    <link rel="alternate" hreflang="pt-BR" href="{{ $canonical }}">
    <link rel="alternate" hreflang="x-default" href="{{ $canonical }}">
    <meta name="theme-color" content="#0F2A24">
    <meta name="geo.region" content="BR">
    <meta name="geo.placename" content="Brasil">
    <meta http-equiv="content-language" content="pt-BR">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:site_name" content="PetMotion">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDesc }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="PetMotion: cada passeio vira um mapa">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon.png') }}">
    <link rel="alternate" type="text/plain" title="Resumo para assistentes de IA" href="{{ url('/llms.txt') }}">
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --fog: #EEF2EC;
            --surface: #FAFCF8;
            --surface-alt: #E3EAE2;
            --ball: #C9F24B;
            --ball-deep: #A9D12A;
            --pine: #0F2A24;
            --pine-2: #1C3D35;
            --pine-3: #2C4F46;
            --on-pine: #EEF2EC;
            --on-pine-muted: #A9BDB3;
            --tongue: #FF7A8A;
            --tongue-soft: #FFE3E6;
            --puddle: #7FB8E6;
            --puddle-soft: #DCEEFA;
            --sun: #FFC24D;
            --sun-soft: #FFF1C9;
            --earth: #B98B5E;
            --muted: #5B6B64;
            --line: #D9E2DA;
            --display: 'Bricolage Grotesque', 'Avenir Next', system-ui, sans-serif;
            --body: 'Manrope', system-ui, -apple-system, sans-serif;
            --gutter: clamp(20px, 5vw, 64px);
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            background: var(--fog);
            color: var(--pine);
            font: 500 17px/1.6 var(--body);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }
        a { color: inherit; }
        :focus-visible { outline: 3px solid var(--ball); outline-offset: 3px; border-radius: 8px; }
        h1, h2, h3 { font-family: var(--display); margin: 0; letter-spacing: -0.035em; line-height: 0.98; font-weight: 800; }
        p { margin: 0; }
        .wrap { max-width: 1240px; margin: 0 auto; padding-inline: var(--gutter); }

        /* ---------- Botões ---------- */
        .btn {
            display: inline-flex; align-items: center; gap: 10px;
            font: 800 16px/1 var(--body); text-decoration: none;
            padding: 18px 26px; border-radius: 999px; border: 0; cursor: pointer;
            transition: transform .14s ease, box-shadow .14s ease, background-color .2s;
        }
        .btn-ball { background: var(--ball); color: var(--pine); box-shadow: 0 5px 0 var(--ball-deep); }
        .btn-ball:hover { transform: translateY(-2px); box-shadow: 0 7px 0 var(--ball-deep); }
        .btn-ball:active { transform: translateY(4px); box-shadow: 0 1px 0 var(--ball-deep); }
        .btn-ghost { color: var(--on-pine); box-shadow: inset 0 0 0 2px var(--pine-3); background: transparent; }
        .btn-ghost:hover { box-shadow: inset 0 0 0 2px var(--ball); color: var(--ball); }

        /* ---------- Marca ---------- */
        .brand { display: inline-flex; align-items: center; gap: 10px; font: 800 21px/1 var(--display); text-decoration: none; letter-spacing: -0.02em; }
        .tennis {
            position: relative; display: inline-block; border-radius: 50%;
            background: var(--ball); overflow: hidden; flex: none;
            box-shadow: inset -3px -4px 0 rgba(15, 42, 36, .14);
        }
        .tennis::before, .tennis::after {
            content: ''; position: absolute; top: -10%; width: 95%; height: 120%;
            border: 2.5px solid #fff; border-radius: 50%;
        }
        .tennis::before { left: -62%; }
        .tennis::after { right: -62%; }
        .brand .tennis { width: 26px; height: 26px; }

        /* ---------- Nav ---------- */
        .nav { position: absolute; inset: 0 0 auto 0; z-index: 10; color: var(--on-pine); }
        .nav .wrap { display: flex; align-items: center; justify-content: space-between; padding-block: 26px; gap: 20px; }
        .nav-links { display: flex; align-items: center; gap: 30px; font-weight: 700; font-size: 15px; }
        .nav-links a { text-decoration: none; color: var(--on-pine-muted); transition: color .2s; }
        .nav-links a:hover { color: var(--on-pine); }
        .nav-links .btn { padding: 12px 20px; font-size: 14px; color: var(--pine); }

        /* ---------- Hero ---------- */
        .hero {
            position: relative; background: var(--pine); color: var(--on-pine);
            padding: 150px 0 120px; overflow: hidden;
            border-radius: 0 0 clamp(32px, 6vw, 72px) clamp(32px, 6vw, 72px);
        }
        .hero-grid { display: grid; grid-template-columns: 1.15fr .85fr; gap: 48px; align-items: center; }
        .hero h1 { font-size: clamp(46px, 6.4vw, 96px); color: var(--on-pine); }
        .hero h1 .line { display: block; overflow: hidden; padding-bottom: .08em; white-space: nowrap; }
        .hero h1 .line > span { display: inline-block; }
        .hero h1 .ball-word { color: var(--ball); }
        .hero-sub { max-width: 46ch; font-size: clamp(17px, 1.5vw, 20px); color: var(--on-pine-muted); margin: 32px 0 40px; }
        .hero-ctas { display: flex; flex-wrap: wrap; gap: 14px; }
        .hero-trace { position: absolute; left: -5%; bottom: -40px; width: 110%; pointer-events: none; opacity: .5; }
        .hero-trace path { stroke-dasharray: 2 18; stroke-linecap: round; }

        /* ---------- Celular (demo ao vivo) ---------- */
        .phone-stage { display: flex; justify-content: center; perspective: 1400px; }
        .phone {
            width: 304px; height: 624px; border-radius: 48px; padding: 10px;
            background: #071613; box-shadow: 0 40px 80px -30px rgba(0,0,0,.6), inset 0 0 0 2px var(--pine-3);
            transform: rotate(4deg);
        }
        .screen { position: relative; height: 100%; border-radius: 39px; overflow: hidden; background: #E4EBE2; display: flex; flex-direction: column; }
        .notch { position: absolute; top: 10px; left: 50%; translate: -50% 0; width: 92px; height: 26px; border-radius: 20px; background: #071613; z-index: 3; }
        .p-map { position: relative; flex: 1; }
        .p-map svg { position: absolute; inset: 0; width: 100%; height: 100%; }
        .p-chip {
            position: absolute; top: 46px; right: 14px; z-index: 2;
            font: 800 11px/1 var(--body); color: var(--pine); background: var(--surface);
            padding: 7px 10px; border-radius: 999px;
        }
        .p-sheet { background: var(--pine); color: var(--on-pine); padding: 16px 18px 20px; border-radius: 26px 26px 0 0; margin-top: -24px; position: relative; }
        .p-grab { width: 34px; height: 4px; border-radius: 4px; background: var(--pine-3); margin: 0 auto 8px; }
        .p-timer { font: 800 50px/1 var(--display); text-align: center; letter-spacing: -.04em; font-variant-numeric: tabular-nums; }
        .p-caption { text-align: center; font-size: 11px; font-weight: 700; color: var(--on-pine-muted); margin-top: 4px; }
        .p-stats { display: grid; grid-template-columns: repeat(3, 1fr); margin: 12px 0; padding: 10px 0; border-block: 1px solid var(--pine-2); text-align: center; }
        .p-stats b { display: block; font: 800 20px/1.1 var(--display); color: var(--ball); font-variant-numeric: tabular-nums; }
        .p-stats span { font-size: 10px; font-weight: 700; color: var(--on-pine-muted); }
        .p-actions { display: flex; justify-content: space-around; }
        .p-act { display: grid; justify-items: center; gap: 4px; background: none; border: 0; padding: 0; cursor: pointer; font: 700 10px var(--body); color: var(--on-pine-muted); }
        .p-act i {
            position: relative; display: grid; place-items: center; width: 46px; height: 46px; border-radius: 50%;
            transition: transform .15s cubic-bezier(.3, 1.6, .5, 1);
        }
        .p-act:active i { transform: scale(.85); }
        .p-act i::after { content: ''; position: absolute; inset: 0; border-radius: 50%; border: 3px solid currentColor; opacity: 0; }
        .p-act.burst i::after { animation: ripple .55s ease-out; }
        @keyframes ripple { from { opacity: .9; transform: scale(1); } to { opacity: 0; transform: scale(1.9); } }
        .p-act svg { width: 22px; height: 22px; }
        .pee i { background: var(--sun); color: var(--sun); }
        .poop i { background: var(--earth); color: var(--earth); }
        .water i { background: var(--puddle); color: var(--puddle); }
        .p-act i svg { color: var(--pine); }
        .p-hint { position: absolute; left: 50%; bottom: -46px; translate: -50% 0; white-space: nowrap; font-size: 14px; font-weight: 700; color: var(--on-pine-muted); }

        /* ---------- Faixa de números reais ---------- */
        .live { padding-top: 56px; }
        .live p { font: 700 clamp(20px, 2.4vw, 30px)/1.35 var(--display); letter-spacing: -.02em; max-width: 30ch; }
        .live strong { color: var(--pine); background: linear-gradient(transparent 62%, var(--ball) 62%); padding: 0 .1em; }
        .live small { display: block; margin-top: 10px; font: 600 14px var(--body); color: var(--muted); letter-spacing: 0; }

        /* ---------- Trilha que se desenha com o scroll ---------- */
        main { position: relative; }
        .trail { position: absolute; inset: 0; pointer-events: none; z-index: 0; }
        .trail svg { position: absolute; inset: 0; overflow: visible; }
        .trail .ghost { stroke: var(--line); stroke-width: 3; stroke-dasharray: 2 12; stroke-linecap: round; fill: none; }
        .trail .drawn { stroke: var(--pine); stroke-width: 6; stroke-linecap: round; stroke-linejoin: round; fill: none; }
        .trail .drawn-ball { stroke: var(--ball); stroke-width: 2.5; stroke-linecap: round; fill: none; }
        .pin {
            position: absolute; width: 34px; height: 34px; margin: -17px 0 0 -17px; border-radius: 50%;
            border: 4px solid var(--surface); box-shadow: 0 6px 14px -6px rgba(15, 42, 36, .5);
            transform: scale(0); transition: transform .45s cubic-bezier(.3, 1.7, .5, 1);
        }
        .pin.on { transform: scale(1); }
        .roller { position: absolute; width: 34px; height: 34px; margin: -17px 0 0 -17px; will-change: transform; z-index: 2; }
        .roller .tennis { width: 34px; height: 34px; }

        section.block { position: relative; z-index: 1; padding-block: clamp(90px, 13vw, 170px); }
        .split { display: grid; grid-template-columns: 1fr 1fr; gap: clamp(32px, 6vw, 96px); align-items: center; }
        .split.flip > :first-child { order: 2; }
        .block h2 { font-size: clamp(42px, 5.6vw, 78px); max-width: 12ch; }
        .block .lede { margin-top: 22px; max-width: 44ch; color: var(--muted); font-size: clamp(17px, 1.4vw, 19px); }

        /* Passo a passo (é sequência de verdade) */
        .steps { list-style: none; padding: 0; margin: 48px 0 0; display: grid; gap: 34px; counter-reset: s; }
        .steps li { display: grid; grid-template-columns: 60px 1fr; gap: 18px; align-items: start; }
        .steps li::before {
            counter-increment: s; content: counter(s);
            width: 60px; height: 60px; border-radius: 50%; display: grid; place-items: center;
            font: 800 26px/1 var(--display); background: var(--surface); box-shadow: inset 0 0 0 2px var(--line);
        }
        .steps li:first-child::before { background: var(--ball); box-shadow: none; }
        .steps h3 { font-size: 26px; letter-spacing: -.02em; line-height: 1.1; margin: 4px 0 6px; }
        .steps p { color: var(--muted); max-width: 40ch; }

        /* Card de compartilhar */
        .share-card {
            margin: 0;
            background: var(--pine); color: var(--on-pine); border-radius: 30px; padding: 26px;
            max-width: 420px; justify-self: center; width: 100%;
            box-shadow: 0 30px 60px -30px rgba(15, 42, 36, .55); transform: rotate(-3deg);
            transition: transform .5s cubic-bezier(.3, 1.4, .5, 1);
        }
        .share-card:hover { transform: rotate(0deg) scale(1.02); }
        .sc-head { display: flex; gap: 12px; align-items: center; }
        .sc-avatar { width: 48px; height: 48px; border-radius: 50%; background: var(--sun-soft); display: grid; place-items: center; font: 800 22px var(--display); color: var(--pine); box-shadow: 0 0 0 3px var(--ball); }
        .sc-head b { display: block; font: 800 21px/1.1 var(--display); }
        .sc-head small { color: var(--on-pine-muted); font-size: 12px; font-weight: 600; }
        .sc-route { margin: 18px 0; background: var(--pine-2); border-radius: 18px; }
        .sc-route svg { display: block; width: 100%; height: auto; }
        .sc-nums { display: flex; justify-content: space-between; }
        .sc-nums b { display: block; font: 800 30px/1 var(--display); color: var(--ball); letter-spacing: -.03em; }
        .sc-nums span { font-size: 12px; font-weight: 700; color: var(--on-pine-muted); }

        .split-top { align-items: start; }
        .sprite { position: absolute; }
        .heat-range-label { display: block; margin-top: 26px; }

        /* Asfalto */
        .heat { background: var(--surface); border-radius: 34px; padding: clamp(26px, 4vw, 44px); box-shadow: inset 0 0 0 1px var(--line); }
        .heat-top { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap; }
        .heat-asphalt { font: 800 clamp(84px, 11vw, 150px)/.85 var(--display); letter-spacing: -.05em; transition: color .3s; font-variant-numeric: tabular-nums; }
        .heat-asphalt sup { font-size: .35em; vertical-align: top; position: relative; top: .25em; }
        .heat-label { font-weight: 700; color: var(--muted); }
        .heat-air { text-align: right; }
        .heat-air b { display: block; font: 800 34px/1 var(--display); }
        .heat input[type=range] { width: 100%; margin: 30px 0 22px; accent-color: var(--pine); height: 28px; cursor: pointer; }
        .heat-msg { margin-top: 18px; display: flex; gap: 14px; align-items: center; padding: 16px 18px; border-radius: 18px; font-weight: 700; transition: background-color .3s; }
        .heat-msg .dot { flex: none; width: 14px; height: 14px; border-radius: 50%; background: currentColor; }
        .heat[data-level=safe] .heat-asphalt { color: var(--pine); }
        .heat[data-level=safe] .heat-msg { background: var(--puddle-soft); }
        .heat[data-level=warning] .heat-asphalt { color: #B07A00; }
        .heat[data-level=warning] .heat-msg { background: var(--sun-soft); }
        .heat[data-level=danger] .heat-asphalt { color: #D0364A; }
        .heat[data-level=danger] .heat-msg { background: var(--tongue-soft); }
        .heat-sun { display: flex; gap: 8px; margin-top: 14px; }
        .heat-sun button {
            font: 700 14px var(--body); padding: 9px 14px; border-radius: 999px; border: 0; cursor: pointer;
            background: var(--surface-alt); color: var(--pine);
        }
        .heat-sun button[aria-pressed=true] { background: var(--pine); color: var(--ball); }

        /* Saúde */
        .doses { display: grid; gap: 12px; max-width: 460px; justify-self: center; width: 100%; }
        .dose { display: grid; grid-template-columns: 84px 1fr; align-items: center; gap: 16px; padding: 16px; border-radius: 22px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--line); }
        .dose-days { border-radius: 16px; padding: 12px 0; text-align: center; }
        .dose-days b { display: block; font: 800 34px/1 var(--display); letter-spacing: -.04em; }
        .dose-days span { font-size: 11px; font-weight: 800; }
        .dose h3 { font-size: 20px; letter-spacing: -.01em; line-height: 1.15; }
        .dose p { font-size: 14px; color: var(--muted); font-weight: 600; }
        .dose.late .dose-days { background: var(--tongue); }
        .dose.soon .dose-days { background: var(--sun); }
        .dose.later .dose-days { background: var(--fog); }
        .dose.bell { position: relative; }
        .dose.bell::after {
            content: 'Lembrete enviado'; position: absolute; right: 14px; top: -12px;
            font: 800 11px var(--body); padding: 6px 10px; border-radius: 999px; background: var(--pine); color: var(--ball);
        }

        /* Conquistas */
        .medals { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 44px; }
        .medal {
            width: 132px; padding: 18px 12px; border-radius: 22px; background: var(--surface); text-align: center;
            box-shadow: inset 0 0 0 1px var(--line); transition: transform .4s cubic-bezier(.3, 1.6, .5, 1);
        }
        .medal:hover { transform: translateY(-6px) rotate(-4deg); }
        .medal i { display: grid; place-items: center; width: 64px; height: 64px; margin: 0 auto 10px; border-radius: 50%; background: var(--sun); box-shadow: inset 0 0 0 3px var(--pine); }
        .medal.locked i { background: var(--surface-alt); box-shadow: none; }
        .medal svg { width: 28px; height: 28px; }
        .medal b { display: block; font-size: 15px; line-height: 1.2; }
        .medal small { font-size: 12px; color: var(--muted); font-weight: 600; }
        .medal.locked { color: var(--muted); }
        .streak { display: flex; gap: 8px; margin-top: 30px; }
        .streak span { flex: 1; height: 56px; border-radius: 14px; background: var(--surface-alt); display: grid; place-items: end center; padding-bottom: 8px; font: 800 12px var(--body); color: var(--muted); }
        .streak span.hit { background: var(--ball); color: var(--pine); }
        .streak span.today { box-shadow: inset 0 0 0 3px var(--pine); }

        /* Por dentro (para a banca) */
        .inside { background: var(--pine); color: var(--on-pine); border-radius: clamp(32px, 6vw, 72px); max-width: 1240px; margin-inline: auto; padding-inline: clamp(24px, 5vw, 72px); }
        .inside-wrap { padding-inline: var(--gutter); }
        .inside .lede { color: var(--on-pine-muted); }
        .inside h2 { color: var(--on-pine); }
        .sensors { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0 48px; margin: 0; }
        .sensors div { padding: 22px 0; border-top: 1px solid var(--pine-2); }
        .sensors dt { font: 800 22px/1.2 var(--display); color: var(--ball); letter-spacing: -.01em; }
        .sensors dd { margin: 6px 0 0; color: var(--on-pine-muted); font-size: 15px; }
        .stack { margin-top: 56px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        .stack div { padding: 22px; border-radius: 22px; background: var(--pine-2); }
        .stack b { display: block; font: 800 20px var(--display); margin-bottom: 4px; }
        .stack p { color: var(--on-pine-muted); font-size: 15px; }

        /* CTA final */
        .final { text-align: center; padding-block: clamp(110px, 15vw, 190px) 80px; position: relative; z-index: 1; }
        .final h2 { font-size: clamp(60px, 10vw, 150px); max-width: none; }
        .final .lede { margin: 22px auto 40px; max-width: 46ch; }
        .final .btn-ghost { color: var(--pine); box-shadow: inset 0 0 0 2px var(--pine); }
        .final .btn-ghost:hover { background: var(--pine); color: var(--ball); }
        .final-ctas { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; }
        .bounce { height: 150px; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; margin-bottom: 26px; }
        .bounce .tennis { width: 66px; height: 66px; animation: bounce 1.04s cubic-bezier(.5, 0, .5, 1) infinite alternate; transform-origin: 50% 100%; }
        .bounce .shadow { width: 60px; height: 10px; border-radius: 50%; background: var(--pine); opacity: .15; margin-top: 6px; animation: shadow 1.04s cubic-bezier(.5, 0, .5, 1) infinite alternate; }
        @keyframes bounce { 0% { transform: translateY(-84px) scale(1, 1); } 92% { transform: translateY(0) scale(1, 1); } 100% { transform: translateY(0) scale(1.14, .84); } }
        @keyframes shadow { 0% { transform: scale(.45); opacity: .07; } 100% { transform: scale(1); opacity: .2; } }

        footer { padding: 36px 0 48px; color: var(--muted); font-size: 14px; font-weight: 600; position: relative; z-index: 1; }
        .foot-links { display: flex; gap: 22px; flex-wrap: wrap; }
        footer .wrap { display: flex; justify-content: space-between; gap: 20px; flex-wrap: wrap; border-top: 1px solid var(--line); padding-top: 28px; }

        /* Dúvidas */
        .faq { display: grid; gap: 10px; }
        .faq details { background: var(--surface); border-radius: 22px; box-shadow: inset 0 0 0 1px var(--line); transition: background-color .2s; }
        .faq details[open] { background: var(--surface); box-shadow: inset 0 0 0 2px var(--pine); }
        .faq summary {
            list-style: none; cursor: pointer; padding: 20px 56px 20px 22px; position: relative;
            font: 800 19px/1.3 var(--display); letter-spacing: -.01em;
        }
        .faq summary::-webkit-details-marker { display: none; }
        .faq summary::after {
            content: ''; position: absolute; right: 20px; top: 50%; width: 22px; height: 22px; margin-top: -11px;
            border-radius: 50%; background: var(--ball);
            background-image: linear-gradient(var(--pine), var(--pine)), linear-gradient(var(--pine), var(--pine));
            background-size: 10px 2.5px, 2.5px 10px; background-position: center; background-repeat: no-repeat;
            transition: transform .3s cubic-bezier(.3, 1.6, .5, 1);
        }
        .faq details[open] summary::after { transform: rotate(45deg); }
        .faq details p { padding: 0 22px 22px; color: var(--muted); max-width: 60ch; }

        /* Responsivo */
        .hero-grid > *, .split > * { min-width: 0; }
        @media (max-width: 960px) {
            .hero { padding-top: 120px; }
            .hero h1 .line { white-space: normal; }
            .phone { width: 272px; height: 560px; }
            .p-timer { font-size: 44px; }
            .hero-grid, .split { grid-template-columns: 1fr; }
            .split.flip > :first-child { order: 0; }
            .phone-stage { margin-top: 30px; }
            .nav-links a:not(.btn) { display: none; }
            .sensors, .stack { grid-template-columns: 1fr; }
            section.block:not(.inside), .final, .live { padding-left: 34px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
            html { scroll-behavior: auto; }
            .pin { transform: scale(1); }
        }
    </style>
</head>
<body>
    <svg class="sprite" width="0" height="0" aria-hidden="true">
        <symbol id="i-drop" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></symbol>
        <symbol id="i-leaf" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></symbol>
        <symbol id="i-drops" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M7 16.3c2.2 0 4-1.83 4-4.05 0-1.16-.57-2.26-1.71-3.19S7.29 6.75 7 5.3c-.29 1.45-1.14 2.84-2.29 3.76S3 11.1 3 12.25c0 2.22 1.8 4.05 4 4.05z"/><path d="M12.56 6.6A10.97 10.97 0 0 0 14 3.02c.5 2.5 2 4.9 4 6.5s3 3.5 3 5.5a6.98 6.98 0 0 1-11.91 4.97"/></symbol>
        <symbol id="i-flame" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.07-2.14-.22-4.05 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.15.43-2.29 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></symbol>
        <symbol id="i-map" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M14.1 6.3 9.9 4.2a2 2 0 0 0-1.8 0L3.6 6.5A1 1 0 0 0 3 7.4v12a1 1 0 0 0 1.4.9l3.7-1.9a2 2 0 0 1 1.8 0l4.2 2.1a2 2 0 0 0 1.8 0l4.5-2.3a1 1 0 0 0 .6-.9v-12a1 1 0 0 0-1.4-.9l-3.7 1.9a2 2 0 0 1-1.8 0z"/><path d="M15 5.8v15M9 3.2v15"/></symbol>
        <symbol id="i-sunrise" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v8M4.9 10.9l1.4 1.4M2 18h2M20 18h2M19.1 10.9l-1.4 1.4M22 22H2M8 6l4-4 4 4M16 18a4 4 0 0 0-8 0"/></symbol>
        <symbol id="i-trophy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6M18 9h1.5a2.5 2.5 0 0 0 0-5H18M4 22h16M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22M18 2H6v7a6 6 0 0 0 12 0V2Z"/></symbol>
        <symbol id="i-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></symbol>
    </svg>

    <header class="nav">
        <div class="wrap">
            <a class="brand" href="{{ route('landing') }}"><span class="tennis"></span>PetMotion</a>
            <nav class="nav-links" aria-label="Principal">
                <a href="#como-funciona">Como funciona</a>
                <a href="#saude">Saúde</a>
                <a href="#por-que">Por que usar</a>
                <a href="#duvidas">Dúvidas</a>
                <a class="btn btn-ball" href="#baixar">Baixar o app</a>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="wrap hero-grid">
            <div>
                <h1 aria-label="Cada passeio vira um mapa.">
                    <span class="line"><span>Cada passeio</span></span>
                    <span class="line"><span>vira um <span class="ball-word">mapa.</span></span></span>
                </h1>
                <p class="hero-sub" data-hero-fade>O PetMotion é o app que guarda cada passeio do seu cachorro ou gato: o caminho no mapa, o tempo, os passos e até onde ele fez xixi. Ainda lembra das vacinas e comemora cada conquista. Tudo pelo seu celular, sem coleira nem aparelho extra.</p>
                <div class="hero-ctas" data-hero-fade>
                    <a class="btn btn-ball" href="#baixar">Baixar o app</a>
                    <a class="btn btn-ghost" href="#como-funciona">Ver como funciona</a>
                </div>
            </div>

            <div class="phone-stage">
                <div class="phone" id="phone" role="img" aria-label="Tela do app PetMotion mostrando um passeio em andamento com o Thor: mapa do caminho, tempo, distância, passos e botões de xixi, cocô e água">
                    <div class="screen">
                        <div class="notch"></div>
                        <div class="p-map">
                            <span class="p-chip">23 °C</span>
                            <svg viewBox="0 0 284 330" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                                <rect width="284" height="330" fill="#E4EBE2"/>
                                <path d="M-10 70 L300 40 M-10 190 L300 160 M60 -10 L90 340 M190 -10 L220 340 M-10 290 L300 270" stroke="#FAFCF8" stroke-width="14"/>
                                <path d="M100 110 Q150 80 175 120 L182 175 Q140 215 104 178 Z" fill="#CFE3C3"/>
                                <path d="M-10 250 Q60 230 120 255 T300 240 L300 330 L-10 330 Z" fill="#9CC9E6" opacity=".7"/>
                                <path id="p-route" d="M70 250 C70 200, 40 160, 80 130 S 150 70, 190 100 S 240 170, 200 200 S 120 220, 140 165" fill="none" stroke="#0F2A24" stroke-width="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path id="p-route-top" d="M70 250 C70 200, 40 160, 80 130 S 150 70, 190 100 S 240 170, 200 200 S 120 220, 140 165" fill="none" stroke="#C9F24B" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="70" cy="250" r="7" fill="#C9F24B" stroke="#0F2A24" stroke-width="3"/>
                                <g id="p-pins"></g>
                                <circle id="p-me" r="9" fill="#0F2A24" stroke="#fff" stroke-width="4"/>
                            </svg>
                        </div>
                        <div class="p-sheet">
                            <div class="p-grab"></div>
                            <div class="p-timer" id="p-timer">00:00</div>
                            <div class="p-caption">Passeando com Thor</div>
                            <div class="p-stats">
                                <div><b id="p-km">0,00</b><span>km</span></div>
                                <div><b id="p-steps">0</b><span>passos</span></div>
                                <div><b id="p-pace">--</b><span>min/km</span></div>
                            </div>
                            <div class="p-actions">
                                <button class="p-act pee" data-type="pee" aria-label="Marcar xixi no mapa"><i><svg><use href="#i-drop"/></svg></i>Xixi</button>
                                <button class="p-act poop" data-type="poop" aria-label="Marcar cocô no mapa"><i><svg><use href="#i-leaf"/></svg></i>Cocô</button>
                                <button class="p-act water" data-type="water" aria-label="Marcar água no mapa"><i><svg><use href="#i-drops"/></svg></i>Água</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <svg class="hero-trace" viewBox="0 0 1400 200" fill="none" aria-hidden="true">
            <path d="M0 150 C 200 60, 340 190, 560 120 S 900 30, 1080 110 S 1300 170, 1400 90" stroke="#C9F24B" stroke-width="4"/>
        </svg>
    </section>

    <main id="main">
        <div class="trail" aria-hidden="true">
            <svg id="trail-svg">
                <path class="ghost" id="trail-ghost"/>
                <path class="drawn" id="trail-drawn"/>
                <path class="drawn-ball" id="trail-ball"/>
            </svg>
            <div id="trail-pins"></div>
            <div class="roller" id="roller"><span class="tennis"></span></div>
        </div>

        <div class="wrap live" data-stop="left" data-color="var(--ball)">
            <p>Na comunidade PetMotion, <strong>{{ number_format($stats['pets'], 0, ',', '.') }} pets</strong> já fizeram <strong>{{ number_format($stats['walks'], 0, ',', '.') }} passeios</strong> e andaram <strong>{{ number_format($stats['km'], 0, ',', '.') }} km</strong>.</p>
            <small>Contagem atualizada a cada passeio salvo.</small>
        </div>

        <section class="block" id="como-funciona">
            <div class="wrap split">
                <div>
                    <h2>Três toques e o passeio está salvo.</h2>
                    <p class="lede">Você não precisa anotar nada. É só sair com a guia numa mão e o celular na outra.</p>
                    <ol class="steps">
                        <li data-stop="left" data-color="var(--ball)">
                            <div><h3>Aperte começar</h3><p>Uma contagem de 3 segundos e o mapa começa a desenhar o caminho de vocês.</p></div>
                        </li>
                        <li data-stop="left" data-color="var(--sun)">
                            <div><h3>Marque no caminho</h3><p>Xixi, cocô ou água com um toque, no ponto exato onde aconteceu. O veterinário agradece.</p></div>
                        </li>
                        <li data-stop="left" data-color="var(--tongue)">
                            <div><h3>Compartilhe o card</h3><p>No fim, o passeio vira uma imagem pronta para o Instagram ou para o grupo da família.</p></div>
                        </li>
                    </ol>
                </div>
                <figure class="share-card" aria-label="Exemplo do card de passeio para compartilhar">
                    <div class="sc-head">
                        <span class="sc-avatar">T</span>
                        <div><b>Passeio do Thor</b><small>17 de novembro, 18:42</small></div>
                    </div>
                    <div class="sc-route">
                        <svg viewBox="0 0 360 190" aria-hidden="true">
                            <path id="sc-path" d="M40 150 C 30 90, 90 60, 130 80 S 190 150, 240 120 S 300 30, 330 60 S 300 160, 250 165" fill="none" stroke="#C9F24B" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="40" cy="150" r="7" fill="#EEF2EC"/>
                        </svg>
                    </div>
                    <div class="sc-nums">
                        <div><b>3,42</b><span>km</span></div>
                        <div><b>41min</b><span>duração</span></div>
                        <div><b>4.617</b><span>passos</span></div>
                    </div>
                </figure>
            </div>
        </section>

        <section class="block" id="asfalto">
            <div class="wrap split flip">
                <div data-stop="right" data-color="var(--tongue)">
                    <h2>O chão está quente demais?</h2>
                    <p class="lede">Num dia de sol, o asfalto fica bem mais quente que o ar e pode queimar as patinhas. Antes de cada passeio, o app olha a temperatura da sua região e avisa se é melhor esperar.</p>
                </div>
                <div class="heat" id="heat" data-level="safe">
                    <div class="heat-top">
                        <div>
                            <div class="heat-label">Asfalto estimado</div>
                            <div class="heat-asphalt"><span id="heat-asphalt">45</span><sup>°C</sup></div>
                        </div>
                        <div class="heat-air">
                            <div class="heat-label">Ar</div>
                            <b><span id="heat-air">20</span> °C</b>
                        </div>
                    </div>
                    <label class="heat-label heat-range-label" for="heat-range">Arraste para mudar a temperatura do ar</label>
                    <input type="range" id="heat-range" min="12" max="38" value="24">
                    <div class="heat-sun" role="group" aria-label="Condição do céu">
                        <button type="button" data-sun="sun" aria-pressed="true">Sol</button>
                        <button type="button" data-sun="cloud" aria-pressed="false">Nublado</button>
                        <button type="button" data-sun="night" aria-pressed="false">Noite</button>
                    </div>
                    <div class="heat-msg" aria-live="polite"><span class="dot"></span><span id="heat-msg"></span></div>
                </div>
            </div>
        </section>

        <section class="block" id="saude">
            <div class="wrap split">
                <div data-stop="left" data-color="var(--puddle)">
                    <h2>A carteirinha que avisa antes.</h2>
                    <p class="lede">Vacinas, vermífugos, remédios e consultas num só lugar. Um dia antes da próxima dose, o celular te avisa. Chega de procurar a carteirinha de papel na gaveta.</p>
                </div>
                <div class="doses">
                    <div class="dose late"><div class="dose-days"><b>2</b><span>dias atrasado</span></div><div><h3>Drontal Plus</h3><p>Vermífugo da Mel</p></div></div>
                    <div class="dose soon bell"><div class="dose-days"><b>1</b><span>dia</span></div><div><h3>V10 (polivalente)</h3><p>Vacina do Thor</p></div></div>
                    <div class="dose later"><div class="dose-days"><b>24</b><span>dias</span></div><div><h3>Antirrábica</h3><p>Vacina do Thor</p></div></div>
                </div>
            </div>
        </section>

        <section class="block" id="conquistas">
            <div class="wrap">
                <div data-stop="right" data-color="var(--sun)">
                    <h2>Um motivo para sair todo dia.</h2>
                    <p class="lede">Cada dia de passeio aumenta a sua sequência, e cada marca nova vira uma medalha. É um empurrãozinho para não pular o passeio, e o seu pet agradece.</p>
                </div>
                <div class="streak" aria-label="Sequência de 6 dias">
                    <span class="hit">seg</span><span class="hit">ter</span><span class="hit">qua</span><span class="hit">qui</span><span class="hit">sex</span><span class="hit today">sáb</span><span>dom</span>
                </div>
                <div class="medals">
                    <div class="medal"><i><svg><use href="#i-flame"/></svg></i><b>Embalado</b><small>3 dias seguidos</small></div>
                    <div class="medal"><i><svg><use href="#i-map"/></svg></i><b>Explorador</b><small>Um passeio de 3 km</small></div>
                    <div class="medal"><i><svg><use href="#i-sunrise"/></svg></i><b>Madrugador</b><small>Antes das 7h</small></div>
                    <div class="medal locked"><i><svg><use href="#i-lock"/></svg></i><b>Maratonista</b><small>25 km somados</small></div>
                    <div class="medal locked"><i><svg><use href="#i-lock"/></svg></i><b>Semana perfeita</b><small>7 dias seguidos</small></div>
                </div>
            </div>
        </section>

        <div class="inside-wrap">
        <section class="block inside" id="por-que">
            <div class="split split-top">
                <div data-stop="left" data-color="var(--ball)">
                    <h2>Sem coleira. Sem aparelho. Só o seu celular.</h2>
                    <p class="lede">Nada de comprar rastreador ou pagar mensalidade de aparelho. Tudo o que o PetMotion faz, ele faz com o celular que já está no seu bolso durante o passeio.</p>
                </div>
                <dl class="sensors">
                    <div><dt>O caminho no mapa</dt><dd>Enquanto vocês andam, o trajeto vai sendo desenhado. Dá para rever cada passeio depois.</dd></div>
                    <div><dt>Passos contados sozinhos</dt><dd>Você não anota nada. Distância, tempo e passos ficam salvos.</dd></div>
                    <div><dt>A foto do seu pet</dt><dd>Tire na hora ou escolha da galeria. É ela que aparece no card do passeio.</dd></div>
                    <div><dt>Lembretes na hora certa</dt><dd>Vacina, vermífugo e a hora do passeio. O celular avisa, mesmo com o app fechado.</dd></div>
                    <div><dt>Uma comemoração na mão</dt><dd>O celular vibra de leve a cada marcação e a cada conquista nova.</dd></div>
                    <div><dt>Sua conta protegida</dt><dd>Seus dados ficam guardados com segurança. Ninguém além de você vê os passeios do seu pet.</dd></div>
                </dl>
            </div>
            <div class="stack">
                <div><b>Cachorro ou gato</b><p>Cadastre quantos pets tiver e troque entre eles com um toque.</p></div>
                <div><b>Leve para o veterinário</b><p>Histórico de passeios, xixi, cocô e vacinas organizado para mostrar na consulta.</p></div>
                <div><b>Android e iPhone</b><p>Funciona nos dois. Sem cadastro complicado: nome, e-mail e senha.</p></div>
            </div>
        </section>
        </div>

        <section class="block" id="duvidas">
            <div class="wrap split split-top">
                <div data-stop="right" data-color="var(--puddle)">
                    <h2>Perguntas de quem tem pet.</h2>
                    <p class="lede">Ficou alguma dúvida? As respostas mais comuns estão aqui.</p>
                </div>
                <div class="faq">
                    @foreach ($faq as $i => $item)
                        <details @if ($i === 0) open @endif>
                            <summary>{{ $item['q'] }}</summary>
                            <p>{{ $item['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
    </main>

    <section class="final" id="baixar">
        <div class="wrap" data-stop="center" data-color="var(--ball)">
            <div class="bounce" aria-hidden="true"><span class="tennis"></span><span class="shadow"></span></div>
            <h2>Bora passear?</h2>
            <p class="lede">O PetMotion chega em breve para Android e iPhone. Enquanto isso, dá uma olhada em como ele funciona e prepara a guia.</p>
            <div class="final-ctas">
                <a class="btn btn-ball" href="#como-funciona">Ver como funciona</a>
                <a class="btn btn-ghost" href="#duvidas">Tirar uma dúvida</a>
            </div>
        </div>
    </section>

    <footer>
        <div class="wrap">
            <span>PetMotion, feito no Brasil para quem ama passear com o seu pet. 2026.</span>
            <nav class="foot-links" aria-label="Rodapé">
                <a href="{{ route('legal.privacy') }}">Privacidade</a>
                <a href="{{ route('legal.terms') }}">Termos de uso</a>
                <a href="{{ route('monitor') }}">Área da equipe</a>
            </nav>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>
    (() => {
        const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
        const $ = (s, el = document) => el.querySelector(s);

        /* ---------- 1. Entrada do hero (o único momento automático da página) ---------- */
        if (!reduce && window.gsap) {
            const tl = gsap.timeline({ defaults: { ease: 'expo.out' } });
            tl.from('.hero h1 .line > span', { yPercent: 110, duration: 1.1, stagger: 0.12 })
              .from('[data-hero-fade]', { y: 24, opacity: 0, duration: 0.9, stagger: 0.1 }, '-=0.7')
              .from('#phone', { y: 140, rotate: 14, opacity: 0, duration: 1.3, ease: 'back.out(1.2)' }, '-=1.0')
              .from('.hero-trace path', { strokeDashoffset: 400, duration: 2.4, ease: 'power2.out' }, 0.2);
            // garantia: o conteúdo nunca fica escondido se a animação travar
            setTimeout(() => tl.progress(1), 4000);
        }

        /* ---------- 2. Celular: passeio rodando de verdade ---------- */
        const route = $('#p-route');
        const routeTop = $('#p-route-top');
        const me = $('#p-me');
        const pins = $('#p-pins');
        const total = route.getTotalLength();
        [route, routeTop].forEach((p) => { p.style.strokeDasharray = total; p.style.strokeDashoffset = total; });
        let seconds = 0;
        let drawn = 0;
        const fmt = (s) => `${String(Math.floor(s / 60)).padStart(2, '0')}:${String(s % 60).padStart(2, '0')}`;

        function tickPhone() {
            seconds += 1;
            drawn = Math.min(total, drawn + total / 70);
            if (drawn >= total) { drawn = 0; pins.innerHTML = ''; }
            const meters = seconds * 1.4 * 3;
            [route, routeTop].forEach((p) => (p.style.strokeDashoffset = total - drawn));
            const pt = route.getPointAtLength(drawn);
            me.setAttribute('cx', pt.x);
            me.setAttribute('cy', pt.y);
            $('#p-timer').textContent = fmt(seconds);
            $('#p-km').textContent = (meters / 1000).toFixed(2).replace('.', ',');
            $('#p-steps').textContent = Math.round(meters * 1.35).toLocaleString('pt-BR');
            if (meters > 50) {
                const pace = seconds / (meters / 1000);
                $('#p-pace').textContent = `${Math.floor(pace / 60)}'${String(Math.round(pace % 60)).padStart(2, '0')}"`;
            }
        }
        tickPhone();
        setInterval(tickPhone, 1000);

        const pinColor = { pee: '#FFC24D', poop: '#B98B5E', water: '#7FB8E6' };
        document.querySelectorAll('.p-act').forEach((btn) => {
            btn.addEventListener('click', () => {
                btn.classList.remove('burst');
                void btn.offsetWidth;
                btn.classList.add('burst');
                if (navigator.vibrate) navigator.vibrate(18);
                const pt = route.getPointAtLength(Math.max(1, drawn));
                const c = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                c.setAttribute('cx', pt.x);
                c.setAttribute('cy', pt.y);
                c.setAttribute('r', 0);
                c.setAttribute('fill', pinColor[btn.dataset.type]);
                c.setAttribute('stroke', '#fff');
                c.setAttribute('stroke-width', 3);
                pins.appendChild(c);
                if (window.gsap && !reduce) gsap.to(c, { attr: { r: 9 }, duration: 0.5, ease: 'back.out(3)' });
                else c.setAttribute('r', 9);
            });
        });

        /* ---------- 3. Trilha que se desenha conforme o scroll ---------- */
        const trail = $('.trail');
        const svg = $('#trail-svg');
        const ghost = $('#trail-ghost');
        const drawnPath = $('#trail-drawn');
        const ballPath = $('#trail-ball');
        const roller = $('#roller');
        const pinLayer = $('#trail-pins');
        let length = 0;
        let stops = [];
        let current = 0;

        // Catmull-Rom -> Bézier: curva suave passando por todos os pontos
        function smooth(pts) {
            let d = `M${pts[0][0]},${pts[0][1]}`;
            for (let i = 0; i < pts.length - 1; i++) {
                const p0 = pts[i - 1] || pts[i];
                const p1 = pts[i];
                const p2 = pts[i + 1];
                const p3 = pts[i + 2] || p2;
                const c1 = [p1[0] + (p2[0] - p0[0]) / 6, p1[1] + (p2[1] - p0[1]) / 6];
                const c2 = [p2[0] - (p3[0] - p1[0]) / 6, p2[1] - (p3[1] - p1[1]) / 6];
                d += ` C${c1[0]},${c1[1]} ${c2[0]},${c2[1]} ${p2[0]},${p2[1]}`;
            }
            return d;
        }

        // comprimento da trilha em que ela atinge a altura y (a trilha sempre desce)
        function lengthAtY(y) {
            let lo = 0;
            let hi = length;
            for (let i = 0; i < 22; i++) {
                const mid = (lo + hi) / 2;
                if (ghost.getPointAtLength(mid).y < y) lo = mid; else hi = mid;
            }
            return lo;
        }

        function build() {
            const box = trail.getBoundingClientRect();
            const top = box.top + scrollY;
            const w = box.width;
            const narrow = w < 960;
            svg.setAttribute('width', w);
            svg.setAttribute('height', box.height);
            svg.setAttribute('viewBox', `0 0 ${w} ${box.height}`);

            const edge = narrow ? 16 : Math.max(28, (w - 1240) / 2 - 10);
            const xFor = (side) => (narrow ? 16 : side === 'left' ? edge : side === 'right' ? w - edge : w / 2);

            const marks = [...document.querySelectorAll('[data-stop]')];
            const pts = [[narrow ? 16 : w * 0.5, 0]];
            marks.forEach((el) => {
                const r = el.getBoundingClientRect();
                const y = Math.min(box.height - 40, r.top + scrollY - top + Math.min(r.height / 2, 90));
                pts.push([xFor(el.dataset.stop), y]);
            });
            pts.sort((a, b) => a[1] - b[1]);
            const d = smooth(pts);
            [ghost, drawnPath, ballPath].forEach((p) => p.setAttribute('d', d));
            length = ghost.getTotalLength();
            [drawnPath, ballPath].forEach((p) => (p.style.strokeDasharray = length));

            pinLayer.innerHTML = '';
            stops = marks.map((el, i) => {
                const [x, y] = pts[i + 1];
                const pin = document.createElement('span');
                pin.className = 'pin';
                pin.style.left = `${x}px`;
                pin.style.top = `${y}px`;
                pin.style.background = el.dataset.color;
                pinLayer.appendChild(pin);
                return { pin, at: lengthAtY(y) };
            });
            current = Math.min(current, length);
            if (reduce) {
                [drawnPath, ballPath].forEach((p) => (p.style.strokeDashoffset = 0));
                stops.forEach((s) => s.pin.classList.add('on'));
                roller.style.display = 'none';
            }
        }

        function frame() {
            if (!reduce && length) {
                const box = trail.getBoundingClientRect();
                const target = lengthAtY(innerHeight * 0.55 - box.top);
                current += (target - current) * 0.12;
                const pt = ghost.getPointAtLength(current);
                drawnPath.style.strokeDashoffset = length - current;
                ballPath.style.strokeDashoffset = length - current;
                // a bola gira conforme anda: ângulo = distância / raio
                roller.style.transform = `translate(${pt.x}px, ${pt.y}px) rotate(${(current / 17) * 57.3}deg)`;
                roller.style.opacity = current < 2 ? 0 : 1;
                stops.forEach((s) => s.pin.classList.toggle('on', current >= s.at - 4));
            }
            requestAnimationFrame(frame);
        }

        /* ---------- 4. Simulador de asfalto (mesma regra do app) ---------- */
        const heat = $('#heat');
        const range = $('#heat-range');
        let sky = 'sun';
        const messages = {
            safe: 'Clima ótimo para passear!',
            warning: 'Chão morno. Teste com a mão por 7 segundos antes de sair.',
            danger: 'Asfalto pode queimar as patinhas. Prefira grama ou espere esfriar.',
        };
        function updateHeat() {
            const air = Number(range.value);
            const asphalt = Math.round(air + ({ sun: 25, cloud: 12, night: 3 })[sky]);
            const level = asphalt >= 52 ? 'danger' : asphalt >= 42 ? 'warning' : 'safe';
            $('#heat-air').textContent = air;
            $('#heat-asphalt').textContent = asphalt;
            $('#heat-msg').textContent = messages[level];
            heat.dataset.level = level;
        }
        range.addEventListener('input', updateHeat);
        document.querySelectorAll('[data-sun]').forEach((b) =>
            b.addEventListener('click', () => {
                sky = b.dataset.sun;
                document.querySelectorAll('[data-sun]').forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
                updateHeat();
            }),
        );
        updateHeat();

        /* ---------- Inicialização ---------- */
        const start = () => { build(); requestAnimationFrame(frame); };
        document.fonts?.ready ? document.fonts.ready.then(start) : addEventListener('load', start);
        let t;
        addEventListener('resize', () => { clearTimeout(t); t = setTimeout(build, 150); });
    })();
    </script>
</body>
</html>
