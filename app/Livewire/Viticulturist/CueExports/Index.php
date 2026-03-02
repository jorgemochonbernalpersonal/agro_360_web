<?php

namespace App\Livewire\Viticulturist\CueExports;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\CueExport;
use App\Models\Exploitation;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public function markAsGenerated(int $id): void
    {
        $export = $this->findOwned(CueExport::class, $id);

        if ($export->status !== 'draft') {
            $this->toastError('Solo se pueden marcar como generadas las exportaciones en borrador.');
            return;
        }

        $export->update(['status' => 'generated', 'generated_at' => now()]);
        $this->toastSuccess('Exportación marcada como generada.');
    }

    public function markAsSent(int $id): void
    {
        $export = $this->findOwned(CueExport::class, $id);

        if ($export->status !== 'generated') {
            $this->toastError('Solo se pueden marcar como enviadas las exportaciones generadas.');
            return;
        }

        $export->update(['status' => 'sent', 'sent_at' => now()]);
        $this->toastSuccess('Exportación marcada como enviada al MAPA.');
    }

    public function delete(int $id): void
    {
        $export = $this->findOwned(CueExport::class, $id);

        if ($export->status !== 'draft') {
            $this->toastError('Solo se pueden eliminar exportaciones en estado Borrador.');
            return;
        }

        $export->delete();
        $this->toastSuccess('Exportación eliminada.');
    }

    protected function baseQuery(): Builder
    {
        return CueExport::where('viticulturist_id', $this->viticulturistId())
            ->with('exploitation');
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('campaign_year')->orderByDesc('created_at');
    }

    protected function defaultOrderBy(): array { return ['campaign_year', 'desc']; }

    protected function viewData(mixed $entries): array
    {
        $userId = $this->viticulturistId();

        $statusCounts = CueExport::where('viticulturist_id', $userId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $exploitations = Exploitation::where('viticulturist_id', $userId)
            ->where('active', true)
            ->orderBy('exploitation_name')
            ->get();

        return [
            'exports'       => $entries,
            'exploitations' => $exploitations,
            'statusCounts'  => $statusCounts,
        ];
    }
}
