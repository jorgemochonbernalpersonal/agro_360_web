<?php

namespace App\Livewire\Admin\FailedJobs;

use App\Livewire\Concerns\WithReadOnlyGuard;
use App\Livewire\Concerns\WithToastNotifications;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithReadOnlyGuard, WithToastNotifications;

    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function retryJob(int $id): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $job = DB::table('failed_jobs')->where('id', $id)->first();
        if (! $job) {
            $this->toastError(__('Job no encontrado.'));

            return;
        }

        try {
            // Re-insert into jobs queue
            DB::table('jobs')->insert([
                'queue' => $job->queue,
                'payload' => $job->payload,
                'attempts' => 0,
                'reserved_at' => null,
                'available_at' => now()->timestamp,
                'created_at' => now()->timestamp,
            ]);
            DB::table('failed_jobs')->where('id', $id)->delete();

            SecurityLogger::logSecurityEvent('failed_job_retried', [
                'admin_id' => Auth::id(),
                'job_id' => $id,
                'queue' => $job->queue,
            ]);

            $this->toastSuccess(__('Job reencolado correctamente.'));
        } catch (\Throwable $e) {
            $this->toastError(__('Error al reintentar el job.'));
        }
    }

    public function deleteJob(int $id): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $deleted = DB::table('failed_jobs')->where('id', $id)->delete();
        if ($deleted) {
            $this->toastSuccess(__('Job eliminado.'));
        } else {
            $this->toastError(__('Job no encontrado.'));
        }
    }

    public function retryAll(): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $jobs = DB::table('failed_jobs')->get();

        if ($jobs->isEmpty()) {
            $this->toastError(__('No hay jobs fallidos que reintentar.'));

            return;
        }

        $inserts = $jobs->map(fn ($j) => [
            'queue' => $j->queue,
            'payload' => $j->payload,
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp,
        ])->toArray();

        DB::table('jobs')->insert($inserts);
        DB::table('failed_jobs')->truncate();

        SecurityLogger::logSecurityEvent('failed_jobs_retry_all', [
            'admin_id' => Auth::id(),
            'count' => $jobs->count(),
        ]);

        $this->toastSuccess("{$jobs->count()} job(s) reencolados.");
    }

    public function deleteAll(): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $count = DB::table('failed_jobs')->count();

        if ($count === 0) {
            $this->toastError(__('No hay jobs fallidos.'));

            return;
        }

        DB::table('failed_jobs')->truncate();

        SecurityLogger::logSecurityEvent('failed_jobs_flush', [
            'admin_id' => Auth::id(),
            'count' => $count,
        ]);

        $this->toastSuccess("{$count} job(s) eliminados permanentemente.");
    }

    public function render()
    {
        $query = DB::table('failed_jobs')->orderByDesc('failed_at');

        if ($this->search) {
            $s = '%'.$this->search.'%';
            $query->where(function ($q) use ($s) {
                $q->where('queue', 'like', $s)
                    ->orWhere('payload', 'like', $s)
                    ->orWhere('exception', 'like', $s);
            });
        }

        $jobs = $query->paginate(20);
        $totalCount = DB::table('failed_jobs')->count();

        return view('livewire.admin.failed-jobs.index', compact('jobs', 'totalCount'))
            ->layout('layouts.app', [
                'title' => __('Jobs Fallidos - Admin - Agro365'),
                'description' => __('Monitor y gestión de jobs fallidos de la cola de trabajos'),
            ]);
    }
}
