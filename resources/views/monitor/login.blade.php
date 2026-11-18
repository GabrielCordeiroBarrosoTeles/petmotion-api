@extends('monitor.layout')
@section('title', 'Entrar')

@push('styles')
<style>
    .login { min-height: 100vh; display: grid; grid-template-columns: 1.1fr 1fr; }
    .login-art {
        background: var(--pine); color: var(--fog);
        padding: clamp(28px, 5vw, 64px);
        display: grid; grid-template-rows: auto 1fr auto; gap: 28px;
        position: relative; overflow: hidden;
    }
    .login-art .brand { color: var(--fog); }

    /* Cena: mapa da marca com um passeio acontecendo */
    .scene { position: relative; align-self: center; justify-self: center; width: min(620px, 92%); }
    .map-card {
        position: relative; border-radius: var(--r-lg); overflow: hidden;
        background: #12312A; box-shadow: 0 40px 80px -40px rgba(0, 0, 0, .6), inset 0 0 0 1px var(--pine-2);
    }
    .map-card svg { display: block; width: 100%; height: auto; }
    .route-line { stroke-dasharray: 1; stroke-dashoffset: 1; animation: draw 2.6s cubic-bezier(.6, 0, .2, 1) .3s forwards; }
    @keyframes draw { to { stroke-dashoffset: 0; } }
    .map-pin { transform-box: fill-box; transform-origin: center; transform: scale(0); animation: pop .5s cubic-bezier(.3, 1.8, .5, 1) forwards; }
    @keyframes pop { to { transform: scale(1); } }
    .walker-halo { transform-box: fill-box; transform-origin: center; animation: halo 1.6s ease-out infinite; }
    @keyframes halo { from { transform: scale(1); opacity: .55; } to { transform: scale(2.6); opacity: 0; } }

    .chip {
        position: absolute; display: flex; align-items: center; gap: 10px;
        padding: 12px 16px; border-radius: 16px;
        background: var(--surface); color: var(--pine);
        box-shadow: 0 18px 40px -18px rgba(0, 0, 0, .55);
        opacity: 0; transform: translateY(14px);
        animation: rise .7s cubic-bezier(.2, .9, .3, 1.2) forwards;
    }
    @keyframes rise { to { opacity: 1; transform: none; } }
    .chip b { font: 800 26px/1 var(--display); letter-spacing: -.03em; }
    .chip span { font-weight: 700; font-size: 13px; line-height: 1.25; color: var(--muted); }
    .chip-walks { left: -24px; top: 14%; animation-delay: 1.4s; }
    .chip-km { right: -24px; bottom: -26px; animation-delay: 1.7s; background: var(--ball); }
    .chip-km span { color: var(--pine-2); }
    .chip-icon { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; background: var(--pine); flex: none; }
    .live {
        position: absolute; top: 16px; right: 16px; display: inline-flex; align-items: center; gap: 8px;
        padding: 7px 12px; border-radius: 999px; background: rgba(15, 42, 36, .78);
        font: 800 12px var(--body); color: var(--fog);
    }
    .live i { width: 8px; height: 8px; border-radius: 50%; background: var(--ball); box-shadow: 0 0 0 0 rgba(201, 242, 75, .7); animation: beat 1.6s ease-out infinite; }
    @keyframes beat { to { box-shadow: 0 0 0 9px rgba(201, 242, 75, 0); } }

    .login-art h1 { font-size: clamp(38px, 4.4vw, 64px); font-weight: 800; color: var(--fog); margin-bottom: 14px; }
    .login-art p { max-width: 42ch; color: #B8C9C0; font-size: 17px; margin: 0; }

    @media (prefers-reduced-motion: reduce) {
        .route-line { stroke-dashoffset: 0; }
        .map-pin { transform: scale(1); }
        .chip { opacity: 1; transform: none; }
        .walker { display: none; }
    }
    .login-form { display: grid; place-items: center; padding: 32px 16px; }
    form { width: min(380px, 100%); display: grid; gap: 18px; }
    form h2 { font-size: 30px; }
    form .sub { color: var(--muted); margin: -10px 0 8px; }
    label { display: grid; gap: 6px; font-weight: 700; font-size: 14px; }
    input[type=email], input[type=password] {
        font: inherit; padding: 14px 16px; border-radius: var(--r-sm);
        border: 2px solid var(--line); background: var(--surface); color: var(--pine);
        transition: border-color .15s;
    }
    input:focus { outline: none; border-color: var(--pine); }
    .check { display: flex; align-items: center; gap: 10px; font-weight: 600; }
    .check input { width: 18px; height: 18px; accent-color: var(--pine); }
    .error { color: #B4231A; font-weight: 600; font-size: 14px; }
    .btn {
        font: 700 16px/1 var(--body); padding: 16px; border: 0; border-radius: 999px; cursor: pointer;
        background: var(--ball); color: var(--pine);
        box-shadow: 0 4px 0 var(--ball-deep);
        transition: transform .12s, box-shadow .12s;
    }
    .btn:hover { transform: translateY(-1px); box-shadow: 0 5px 0 var(--ball-deep); }
    .btn:active { transform: translateY(3px); box-shadow: 0 1px 0 var(--ball-deep); }
    .hint { color: var(--muted); font-size: 13px; text-align: center; }
    @media (max-width: 980px) {
        .chip-walks { left: 8px; }
        .chip-km { right: 8px; }
    }
    @media (max-width: 820px) {
        .login { grid-template-columns: 1fr; }
        .scene { display: none; }
    }
</style>
@endpush

@section('body')
<main class="login">
    <section class="login-art">
        <a class="brand" href="{{ route('landing') }}"><span class="brand-ball"></span>PetMotion</a>

        <div class="scene" aria-hidden="true">
            <div class="map-card">
                <svg viewBox="0 0 480 320" fill="none">
                    <!-- quarteirões, parque e lago -->
                    <path d="M150 0 L170 320 M300 0 L285 320 M0 120 L480 105 M0 230 L480 215" stroke="#1C3D35" stroke-width="16"/>
                    <path d="M60 0 L75 320 M400 0 L390 320 M0 40 L480 30 M0 290 L480 282" stroke="#183A32" stroke-width="7"/>
                    <path d="M190 128 C 230 118, 270 130, 268 160 C 266 196, 214 204, 196 186 C 180 170, 172 136, 190 128 Z" fill="#1A4436"/>
                    <path d="M330 232 C 370 222, 430 230, 452 252 C 470 272, 440 300, 396 298 C 350 296, 312 270, 330 232 Z" fill="#7FB8E6" fill-opacity=".16"/>

                    <!-- trajeto do passeio: contorno escuro + bola de tênis -->
                    <path d="M58 268 C 40 210, 70 170, 128 172 S 214 214, 250 168 S 262 86, 326 78 S 430 104, 440 160 S 400 252, 352 246" stroke="#0F2A24" stroke-width="13" stroke-linecap="round" stroke-linejoin="round"/>
                    <path class="route-line" pathLength="1" d="M58 268 C 40 210, 70 170, 128 172 S 214 214, 250 168 S 262 86, 326 78 S 430 104, 440 160 S 400 252, 352 246" stroke="#C9F24B" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="58" cy="268" r="7" fill="#EEF2EC" stroke="#0F2A24" stroke-width="3"/>

                    <!-- marcações: xixi, água, cocô -->
                    <circle class="map-pin" style="animation-delay:1.1s" cx="128" cy="172" r="9" fill="#FFC24D" stroke="#FAFCF8" stroke-width="3"/>
                    <circle class="map-pin" style="animation-delay:1.6s" cx="326" cy="78" r="9" fill="#7FB8E6" stroke="#FAFCF8" stroke-width="3"/>
                    <circle class="map-pin" style="animation-delay:2.1s" cx="440" cy="160" r="9" fill="#B98B5E" stroke="#FAFCF8" stroke-width="3"/>

                    <!-- pet andando pelo trajeto -->
                    <g class="walker">
                        <circle class="walker-halo" r="9" fill="#C9F24B"/>
                        <circle r="8" fill="#0F2A24" stroke="#FAFCF8" stroke-width="3.5"/>
                        <animateMotion dur="9s" begin="2.9s" repeatCount="indefinite" path="M58 268 C 40 210, 70 170, 128 172 S 214 214, 250 168 S 262 86, 326 78 S 430 104, 440 160 S 400 252, 352 246" fill="freeze"/>
                    </g>
                </svg>
                <span class="live"><i></i>Ao vivo</span>
            </div>

            <div class="chip chip-walks">
                <span class="chip-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#C9F24B" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 16v-2.38C4 11.5 2.97 10.5 3 8c.03-2.72 1.49-6 4.5-6C9.37 2 10 3.8 10 5.5c0 3.11-2 5.66-2 8.68V16a2 2 0 1 1-4 0Z"/><path d="M20 20v-2.38c0-2.12 1.03-3.12 1-5.62-.03-2.72-1.49-6-4.5-6C14.63 6 14 7.8 14 9.5c0 3.11 2 5.66 2 8.68V20a2 2 0 1 0 4 0Z"/></svg>
                </span>
                <div><b>{{ $pulse['walksToday'] }}</b><br><span>{{ $pulse['walksToday'] === 1 ? 'passeio hoje' : 'passeios hoje' }}</span></div>
            </div>
            <div class="chip chip-km">
                <div><b>{{ number_format($pulse['kmWeek'], 0, ',', '.') }} km</b><br><span>andados nos últimos 7 dias</span></div>
            </div>
        </div>

        <div>
            <h1>Cada passeio, em tempo real.</h1>
            <p>Acompanhe tutores, pets, trajetos e cuidados de saúde de toda a comunidade PetMotion.</p>
        </div>
    </section>

    <section class="login-form">
        <form method="POST" action="{{ url('/monitor/login') }}">
            @csrf
            <h2>Entrar no monitor</h2>
            <p class="sub">Área restrita a administradores.</p>
            <label>E-mail
                <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            </label>
            <label>Senha
                <input type="password" name="password" autocomplete="current-password" required>
            </label>
            @error('email') <p class="error" role="alert">{{ $message }}</p> @enderror
            <label class="check"><input type="checkbox" name="remember"> Manter conectado</label>
            <button class="btn" type="submit">Entrar</button>
            @if (app()->isLocal())
                <p class="hint">Demo: admin@petmotion.app / petmotion</p>
            @endif
        </form>
    </section>
</main>
@endsection
