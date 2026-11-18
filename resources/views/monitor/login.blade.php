@extends('monitor.layout')
@section('title', 'Entrar')

@push('styles')
<style>
    .login { min-height: 100vh; display: grid; grid-template-columns: 1.1fr 1fr; }
    .login-art {
        background: var(--pine); color: var(--fog);
        padding: clamp(32px, 6vw, 72px);
        display: flex; flex-direction: column; justify-content: space-between;
        position: relative; overflow: hidden;
    }
    .login-art .brand { color: var(--fog); }
    .login-art h1 { font-size: clamp(40px, 6vw, 76px); font-weight: 800; max-width: 11ch; color: var(--ball); }
    .login-art p { max-width: 38ch; color: #B8C9C0; font-size: 17px; }
    .trail { position: absolute; right: -40px; bottom: -30px; width: 70%; opacity: .9; pointer-events: none; }
    .trail path { stroke-dasharray: 6 14; animation: walk 14s linear infinite; }
    @keyframes walk { to { stroke-dashoffset: -400; } }
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
    @media (max-width: 820px) {
        .login { grid-template-columns: 1fr; }
        .login-art { min-height: 300px; }
    }
</style>
@endpush

@section('body')
<main class="login">
    <section class="login-art">
        <a class="brand" href="{{ route('monitor.login') }}"><span class="brand-ball"></span>PetMotion</a>
        <div>
            <h1>Cada passeio, em tempo real.</h1>
            <p>Acompanhe tutores, pets, trajetos e cuidados de saúde de toda a comunidade PetMotion.</p>
        </div>
        <svg class="trail" viewBox="0 0 400 300" fill="none" aria-hidden="true">
            <path d="M10 280 C 80 200, 60 120, 150 140 S 260 260, 300 160 S 340 40, 395 30" stroke="#C9F24B" stroke-width="5" stroke-linecap="round"/>
        </svg>
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
            <p class="hint">Demo: admin@petmotion.app / petmotion</p>
        </form>
    </section>
</main>
@endsection
