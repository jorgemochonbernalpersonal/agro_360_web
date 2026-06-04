<?php

namespace App\Livewire\Winery\Coupage;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\WineTransfer;
use App\Services\WineContainerStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use WithToastNotifications;

    #[Url(except: '')]
    public string $vintageFilter = '';

    public function updatingVintageFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $userId = Auth::id();
        $transfer = WineTransfer::whereHas('wine', fn ($q) => $q->where('user_id', $userId))
            ->where('transfer_type', 'blending')
            ->findOrFail($id);

        $service = app(WineContainerStockService::class);

        DB::transaction(function () use ($transfer, $service) {
            $service->revertTransfer($transfer);
            $transfer->delete();
        });

        $this->toastSuccess(__('Coupage eliminado correctamente.'));
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $userId = Auth::id();

        $blends = WineTransfer::with(['wine', 'sourceWine', 'fromContainer', 'toContainer', 'oenologist'])
            ->whereHas('wine', fn ($q) => $q->where('user_id', $userId))
            ->where('transfer_type', 'blending')
            ->when($this->vintageFilter, fn ($q) => $q->whereHas('wine', fn ($wq) => $wq->where('vintage', $this->vintageFilter)))
            ->orderByDesc('transfer_date')
            ->orderByDesc('id')
            ->paginate(20);

        $availableVintages = WineTransfer::whereHas('wine', fn ($q) => $q->where('user_id', $userId))
            ->where('transfer_type', 'blending')
            ->join('wines', 'wine_transfers.wine_id', '=', 'wines.id')
            ->distinct()
            ->orderByDesc('wines.vintage')
            ->pluck('wines.vintage');

        return view('livewire.winery.coupage.index', [
            'blends' => $blends,
            'availableVintages' => $availableVintages,
        ]);
    }
}
