<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Monitor') · PetMotion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    @stack('head')
    <style>
        :root {
            --fog: #EEF2EC;
            --surface: #FAFCF8;
            --ball: #C9F24B;
            --ball-deep: #A9D12A;
            --pine: #0F2A24;
            --pine-2: #1C3D35;
            --tongue: #FF7A8A;
            --puddle: #7FB8E6;
            --sun: #FFC24D;
            --muted: #5B6B64;
            --line: #D9E2DA;
            --danger: #FF3B30;
            --r-lg: 28px;
            --r-md: 18px;
            --r-sm: 10px;
            --display: 'Bricolage Grotesque', 'Avenir Next', system-ui, sans-serif;
            --body: 'Manrope', system-ui, -apple-system, sans-serif;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; }
        body {
            background: var(--fog);
            color: var(--pine);
            font: 500 15px/1.55 var(--body);
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; }
        :focus-visible { outline: 3px solid var(--pine); outline-offset: 2px; border-radius: 6px; }
        h1, h2, h3 { font-family: var(--display); margin: 0; letter-spacing: -0.02em; line-height: 1.1; }
        .wrap { max-width: 1280px; margin: 0 auto; padding-inline: clamp(16px, 4vw, 40px); }
        .brand { display: inline-flex; align-items: center; gap: 10px; font: 800 20px/1 var(--display); text-decoration: none; }
        .brand-ball {
            width: 26px; height: 26px; border-radius: 50%;
            background: var(--ball);
            background-image:
                radial-gradient(circle at -20% 50%, transparent 58%, #fff 59%, #fff 64%, transparent 65%),
                radial-gradient(circle at 120% 50%, transparent 58%, #fff 59%, #fff 64%, transparent 65%);
            box-shadow: inset -3px -3px 0 rgba(15, 42, 36, .12);
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('body')
    @stack('scripts')
</body>
</html>
