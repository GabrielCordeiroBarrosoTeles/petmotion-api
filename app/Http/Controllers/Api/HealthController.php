<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HealthChecker;
use Illuminate\Http\JsonResponse;

/**
 * GET /api/health — verifica se a API e as dependências estão funcionando.
 * 200 quando tudo está ok; 503 quando alguma checagem falha (para monitoramento/uptime).
 */
class HealthController extends Controller
{
    public function __invoke(HealthChecker $health): JsonResponse
    {
        $result = $health->run();

        return response()->json([
            'status' => $result['healthy'] ? 'ok' : 'degraded',
            'app' => config('app.name'),
            'environment' => app()->environment(),
            'time' => now()->toIso8601String(),
            'checks' => $result['checks'],
        ], $result['healthy'] ? 200 : 503);
    }
}
