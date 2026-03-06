<?php

namespace App\Livewire\Winery\Clients;

use App\Livewire\Winery\AbstractIndex;
use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Index extends AbstractIndex
{
    public string $search       = '';
    public string $typeFilter   = '';
    public string $statusFilter = 'active';

    protected $queryString = [
        'search'       => ['except' => ''],
        'typeFilter'   => ['except' => ''],
        'statusFilter' => ['except' => 'active'],
    ];

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingTypeFilter(): void   { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return ['search' => '', 'typeFilter' => '', 'statusFilter' => 'active'];
    }

    public function toggleActive(int $clientId): void
    {
        $client = Client::where('user_id', Auth::id())->findOrFail($clientId);
        $client->update(['active' => !$client->active]);
        $label = $client->active ? 'reactivado' : 'desactivado';
        $this->toastSuccess("Cliente «{$client->full_name}» {$label}.");
    }

    public function delete(int $clientId): void
    {
        $client = Client::where('user_id', Auth::id())
            ->withCount('invoices')
            ->findOrFail($clientId);

        if ($client->invoices_count > 0) {
            $this->toastError('No se puede eliminar un cliente con facturas asociadas.');
            return;
        }

        $client->delete();
        $this->toastSuccess('Cliente eliminado.');
    }

    protected function baseQuery(): Builder
    {
        return Client::where('user_id', $this->wineryId());
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(IFNULL(first_name,\'\')) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(last_name,\'\')) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(company_name,\'\')) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(email,\'\')) LIKE ?', [$term]);
            });
        }

        if ($this->typeFilter) {
            $query->where('client_type', $this->typeFilter);
        }

        match ($this->statusFilter) {
            'active'   => $query->where('active', true),
            'inactive' => $query->where('active', false),
            default    => null,
        };
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderBy('first_name')->orderBy('company_name');
    }

    protected function defaultOrderBy(): array { return ['first_name', 'asc']; }

    protected function perPage(): int { return 15; }

    protected function viewData(mixed $entries): array
    {
        return ['clients' => $entries];
    }
}
