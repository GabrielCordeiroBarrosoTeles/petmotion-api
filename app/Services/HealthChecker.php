<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Checagens de saúde da aplicação: usadas pelo GET /api/health e pela tela Sistema do monitor.
 */
class HealthChecker
{
    /** @return array{healthy: bool, checks: array<string, array{status: string, ms: float, error?: string}>} */
    public function run(): array
    {
        $checks = [
            'database' => $this->check(fn () => DB::select('select 1')),
            'cache' => $this->check(function () {
                $key = 'health:'.Str::random(8);
                Cache::put($key, 'ok', 10);
                if (Cache::pull($key) !== 'ok') {
                    throw new RuntimeException('Cache não devolveu o valor gravado.');
                }
            }),
            'storage' => $this->check(function () {
                $file = 'health/'.Str::random(8).'.txt';
                Storage::disk('public')->put($file, 'ok');
                Storage::disk('public')->delete($file);
            }),
        ];

        return [
            'healthy' => collect($checks)->every(fn ($c) => $c['status'] === 'ok'),
            'checks' => $checks,
        ];
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
