<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Collection;

class AuditService
{
    /**
     * Get audit logs for a model
     */
    public function getModelAuditLogs(string $modelType, int $modelId, int $limit = 50): Collection
    {
        return AuditLog::forModel($modelType, $modelId)
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get user activity
     */
    public function getUserActivity(User $user, int $limit = 100): Collection
    {
        return AuditLog::byUser($user->id)
            ->with('auditable')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent activity across all models
     */
    public function getRecentActivity(int $limit = 50): Collection
    {
        return AuditLog::with(['user', 'auditable'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get activity by date range
     */
    public function getActivityByDateRange(\DateTime $start, \DateTime $end): Collection
    {
        return AuditLog::whereBetween('created_at', [$start, $end])
            ->with(['user', 'auditable'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get activity statistics
     */
    public function getActivityStats(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $query = AuditLog::query();

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        $stats = $query->selectRaw('
            event,
            auditable_type,
            COUNT(*) as count
        ')
        ->groupBy('event', 'auditable_type')
        ->get();

        return [
            'total' => $stats->sum('count'),
            'by_event' => $stats->groupBy('event')->map->sum('count'),
            'by_model' => $stats->groupBy('auditable_type')->map->sum('count'),
            'details' => $stats,
        ];
    }

    /**
     * Get compliance report for official inspections
     */
    public function getComplianceReport(User $user, \DateTime $start, \DateTime $end): array
    {
        $criticalModels = [
            \App\Models\Plot::class,
            \App\Models\AgriculturalActivity::class,
            \App\Models\PhytosanitaryTreatment::class,
            \App\Models\Container::class,
        ];

        $logs = AuditLog::query()
            ->where('user_id', $user->id)
            ->whereIn('auditable_type', $criticalModels)
            ->whereBetween('created_at', [$start, $end])
            ->with('auditable')
            ->orderBy('created_at')
            ->get();

        return [
            'period' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'total_actions' => $logs->count(),
            'by_model' => $logs->groupBy('auditable_type')->map->count(),
            'by_event' => $logs->groupBy('event')->map->count(),
            'logs' => $logs->map(fn($log) => [
                'date' => $log->created_at->format('Y-m-d H:i:s'),
                'action' => $log->event,
                'model' => class_basename($log->auditable_type),
                'model_id' => $log->auditable_id,
                'description' => $log->getDescription(),
                'ip_address' => $log->ip_address,
            ]),
        ];
    }

    /**
     * Clean old audit logs
     */
    public function cleanOldLogs(int $daysToKeep = 365): int
    {
        if ($daysToKeep < 30) {
            throw new \InvalidArgumentException(__('No se pueden eliminar logs con menos de 30 días de antigüedad.'));
        }

        $cutoffDate = now()->subDays($daysToKeep);

        return AuditLog::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Export audit logs to CSV
     */
    public function exportToCsv(Collection $logs): string
    {
        $csv = [];
        $csv[] = ['Fecha', 'Usuario', 'Acción', 'Modelo', 'ID', 'IP', 'Descripción'];

        foreach ($logs as $log) {
            $csv[] = [
                $log->created_at->format('Y-m-d H:i:s'),
                $log->user?->name ?? 'Sistema',
                $log->event,
                class_basename($log->auditable_type),
                $log->auditable_id,
                $log->ip_address,
                $log->getDescription(),
            ];
        }

        return implode("\n", array_map(
            fn($row) => implode(';', array_map(
                fn($field) => '"' . str_replace('"', '""', (string) $field) . '"',
                $row
            )),
            $csv
        ));
    }
}
