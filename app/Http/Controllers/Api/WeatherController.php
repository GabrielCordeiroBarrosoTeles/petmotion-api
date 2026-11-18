<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    /**
     * Temperatura atual + estimativa do asfalto (Open-Meteo, sem chave).
     * Regra prática veterinária: com sol, o asfalto fica ~20–30 °C acima do ar.
     */
    public function show(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $key = sprintf('weather:%.2f:%.2f', $data['lat'], $data['lng']);

        $current = Cache::remember($key, now()->addMinutes(10), function () use ($data) {
            $response = Http::timeout(6)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $data['lat'],
                'longitude' => $data['lng'],
                'current' => 'temperature_2m,is_day,cloud_cover,uv_index',
            ]);

            return $response->successful() ? $response->json('current') : null;
        });

        if (! $current) {
            return response()->json(['message' => 'Clima indisponível no momento.'], 503);
        }

        $air = (float) $current['temperature_2m'];
        $sunny = ($current['is_day'] ?? 0) && ($current['cloud_cover'] ?? 100) < 60;
        $asphalt = round($air + ($sunny ? 25 : ($current['is_day'] ? 12 : 3)));

        $level = match (true) {
            $asphalt >= 52 => 'danger',
            $asphalt >= 42 => 'warning',
            default => 'safe',
        };

        $messages = [
            'danger' => 'Asfalto pode queimar as patinhas. Prefira grama ou espere esfriar.',
            'warning' => 'Chão morno. Teste com a mão por 7 segundos antes de sair.',
            'safe' => 'Clima ótimo para passear!',
        ];

        return response()->json([
            'air_c' => $air,
            'asphalt_c' => $asphalt,
            'is_day' => (bool) $current['is_day'],
            'uv_index' => $current['uv_index'] ?? null,
            'level' => $level,
            'message' => $messages[$level],
        ]);
    }
}
