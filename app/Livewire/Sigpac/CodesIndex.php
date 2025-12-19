<?php

namespace App\Livewire\Sigpac;

use App\Models\SigpacCode;
use App\Models\Plot;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class CodesIndex extends Component
{
    use WithPagination;

    public $search = '';

    protected $queryString = ['search'];

    public function render()
    {
        $user = Auth::user();
        
        // Obtener IDs de parcelas que el usuario puede ver
        $plotIds = Plot::forUser($user)->pluck('id');
        
        $codes = SigpacCode::query()
            ->whereHas('plots', function($query) use ($plotIds) {
                $query->whereIn('plots.id', $plotIds);
            })
            ->when($this->search, function($query) {
                $search = '%' . strtolower($this->search) . '%';
                $query->where(function($q) use ($search) {
                    $q->whereRaw('LOWER(code) LIKE ?', [$search])
                      ->orWhereRaw('LOWER(description) LIKE ?', [$search]);
                });
            })
            ->withCount(['plots' => function($query) use ($plotIds) {
                $query->whereIn('plots.id', $plotIds);
            }])
            ->orderBy('code')
            ->paginate(10);

        return view('livewire.sigpac.codes-index', [
            'codes' => $codes,
        ])->layout('layouts.app');
    }
}

