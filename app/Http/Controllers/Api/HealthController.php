<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * GET /api/health — verifica se a API e as dependências estão funcionando.
 * 200 quando tudo está ok; 503 quando alguma checagem falha (para monitoramento/uptime).
 */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check(fn () => DB::select('select 1')),
            'cache' => $this->check(function () {
                $key = 'health:'.Str::random(8);
                Cache::put($key, 'ok', 10);
                $ok = Cache::pull($key) === 'ok';
                if (! $ok) {
                    throw new \RuntimeException('Cache não devolveu o valor gravado.');
                }
            }),
            'storage' => $this->check(function () {
                $file = 'health/'.Str::random(8).'.txt';
                Storage::disk('public')->put($file, 'ok');
                Storage::disk('public')->delete($file);
            }),
        ];

        $healthy = collect($checks)->every(fn ($c) => $c['status'] === 'ok');

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'app' => config('app.name'),
            'environment' => app()->environment(),
            'time' => now()->toIso8601String(),
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    /** @return array{status: string, ms: float, error?: string} */
    private function check(callable $probe): array
    {
        $start = microtime(true);
        try {
            $probe();

            return ['status' => 'ok', 'ms' => round((microtime(true) - $start) * 1000, 1)];
        } catch (Throwable $e) {
            report($e);

            return [
                'status' => 'fail',
                'ms' => round((microtime(true) - $start) * 1000, 1),
                'error' => app()->hasDebugModeEnabled() ? $e->getMessage() : 'unavailable',
            ];
        }
    }
}
