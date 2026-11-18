<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Models\Walk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MonitorController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::user()?->is_admin) {
            return redirect()->route('monitor');
        }

        // Só totais agregados (o site público já mostra números parecidos): nada de dado de pai/mãe de pet.
        return view('monitor.login', [
            'pulse' => [
                'walksToday' => Walk::whereDate('started_at', Carbon::today())->count(),
                'kmWeek' => round(Walk::where('started_at', '>=', Carbon::today()->subDays(6))->sum('distance_meters') / 1000),
            ],
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember')) || ! Auth::user()->is_admin) {
            Auth::logout();

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Acesso negado. Use uma conta de administrador.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('monitor'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('monitor.login');
    }
}
