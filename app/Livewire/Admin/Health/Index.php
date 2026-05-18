<?php

namespace App\Livewire\Admin\Health;

use App\Models\SecurityEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    public array $health = [];

    public function mount(): void
    {
        $this->refresh();
    }

    public function refresh(): void
    {
        $this->health = $this->buildHealth();
    }

    private function buildHealth(): array
    {
        return [
            'queue'    => $this->checkQueue(),
            'database' => $this->checkDatabase(),
            'cache'    => $this->checkCache(),
            'disk'     => $this->checkDisk(),
            'security' => $this->checkSecurity(),
            'app'      => $this->appInfo(),
        ];
    }

    private function checkQueue(): array
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed  = DB::table('failed_jobs')->count();

            $status = match(true) {
                $failed > 0  => 'error',
                $pending > 50 => 'warning',
                default       => 'ok',
            };

            return [
                'status'  => $status,
                'pending' => $pending,
                'failed'  => $failed,
            ];
        } catch (\Throwable) {
            return ['status' => 'error', 'pending' => null, 'failed' => null];
        }
    }

    private function checkDatabase(): array
    {
        try {
            $start  = microtime(true);
            DB::select('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 1);

            $sizeRow = DB::select('
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
            ');
            $sizeMb  = (float) ($sizeRow[0]->size ?? 0);

            $status = match(true) {
                $latency > 500 => 'warning',
                default        => 'ok',
            };

            return [
                'status'   => $status,
                'latency'  => $latency,
                'size_mb'  => $sizeMb,
            ];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'latency' => null, 'size_mb' => null, 'error' => $e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'admin_health_check_' . now()->timestamp;
            Cache::put($key, 'ok', 5);
            $val = Cache::get($key);
            Cache::forget($key);

            return ['status' => $val === 'ok' ? 'ok' : 'error'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }

    private function checkDisk(): array
    {
        $path  = storage_path();
        $free  = disk_free_space($path);
        $total = disk_total_space($path);
        $used  = $total - $free;
        $pct   = $total > 0 ? round($used / $total * 100, 1) : 0;

        $logsSize = $this->dirSize(storage_path('logs'));
        $appSize  = $this->dirSize(storage_path('app'));

        $status = match(true) {
            $pct >= 90 => 'error',
            $pct >= 70 => 'warning',
            default    => 'ok',
        };

        return [
            'status'      => $status,
            'used_gb'     => round($used  / 1024 ** 3, 2),
            'total_gb'    => round($total / 1024 ** 3, 2),
            'free_gb'     => round($free  / 1024 ** 3, 2),
            'used_pct'    => $pct,
            'logs_mb'     => round($logsSize / 1024 ** 2, 2),
            'app_mb'      => round($appSize  / 1024 ** 2, 2),
        ];
    }

    private function dirSize(string $path): int
    {
        $size = 0;
        if (!is_dir($path)) return 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    private function checkSecurity(): array
    {
        try {
            $last24h = SecurityEvent::where('created_at', '>=', now()->subHours(24))
                ->selectRaw("COUNT(*) as total")
                ->selectRaw("SUM(CASE WHEN level IN ('alert','error','critical','emergency') THEN 1 ELSE 0 END) as alerts")
                ->selectRaw("SUM(CASE WHEN level = 'warning' THEN 1 ELSE 0 END) as warnings")
                ->selectRaw("SUM(CASE WHEN event = 'failed_login' THEN 1 ELSE 0 END) as failed_logins")
                ->selectRaw("SUM(CASE WHEN event = 'account_locked' THEN 1 ELSE 0 END) as locked")
                ->first();

            $status = match(true) {
                ($last24h->alerts ?? 0) > 0   => 'error',
                ($last24h->warnings ?? 0) > 10 => 'warning',
                default                        => 'ok',
            };

            return [
                'status'        => $status,
                'total'         => (int) ($last24h->total        ?? 0),
                'alerts'        => (int) ($last24h->alerts       ?? 0),
                'warnings'      => (int) ($last24h->warnings     ?? 0),
                'failed_logins' => (int) ($last24h->failed_logins ?? 0),
                'locked'        => (int) ($last24h->locked        ?? 0),
            ];
        } catch (\Throwable) {
            return ['status' => 'ok', 'total' => 0, 'alerts' => 0, 'warnings' => 0, 'failed_logins' => 0, 'locked' => 0];
        }
    }

    private function appInfo(): array
    {
        return [
            'status'          => 'ok',
            'php_version'     => phpversion(),
            'laravel_version' => app()->version(),
            'environment'     => app()->environment(),
            'debug'           => config('app.debug'),
            'timezone'        => config('app.timezone'),
            'queue_driver'    => config('queue.default'),
            'cache_driver'    => config('cache.default'),
        ];
    }

    public function render()
    {
        return view('livewire.admin.health.index', [
            'health'       => $this->health,
            'refreshedAt'  => now(),
        ])->layout('layouts.app', [
            'title'       => 'Salud del Sistema - Admin - Agro365',
            'description' => 'Estado de los servicios y métricas del sistema',
        ]);
    }
}
