<?php

namespace App\Livewire\Admin\Leads;

use App\Http\Controllers\ToolsController;
use App\Models\ToolLead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Contactos captados por las herramientas públicas (/herramientas).
 */
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterTool = '';

    public string $filterProfile = '';

    /** '' = todos, 'pending' = sin contactar, 'contacted' = ya contactados */
    public string $filterStatus = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterTool' => ['except' => '', 'as' => 'tool'],
        'filterProfile' => ['except' => '', 'as' => 'profile'],
        'filterStatus' => ['except' => '', 'as' => 'status'],
    ];

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'filterTool', 'filterProfile', 'filterStatus'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterTool', 'filterProfile', 'filterStatus']);
        $this->resetPage();
    }

    public function toggleContacted(int $leadId): void
    {
        abort_if(Auth::user()?->isReadOnlyAdmin(), 403, 'Administrador de solo lectura.');

        $lead = ToolLead::findOrFail($leadId);
        $lead->update(['contacted_at' => $lead->contacted_at ? null : now()]);
    }

    public function export(): StreamedResponse
    {
        $query = $this->baseQuery()->orderByDesc('created_at');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel lea bien los acentos
            fputcsv($out, ['Fecha', 'Email', 'Nombre', 'Perfil', 'Herramienta', 'Nota test', 'Contactado'], ';');

            $query->chunk(500, function ($leads) use ($out) {
                foreach ($leads as $lead) {
                    fputcsv($out, [
                        $lead->created_at?->format('Y-m-d H:i'),
                        $lead->email,
                        $lead->name,
                        $lead->profileLabel(),
                        $this->toolTitle($lead->tool),
                        $lead->context['score'] ?? '',
                        $lead->contacted_at?->format('Y-m-d') ?? '',
                    ], ';');
                }
            });

            fclose($out);
        }, 'contactos-herramientas-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function render()
    {
        return view('livewire.admin.leads.index', [
            'leads' => $this->baseQuery()->orderByDesc('created_at')->paginate(50),
            'stats' => [
                'total' => ToolLead::count(),
                'pending' => ToolLead::pending()->count(),
                'last7' => ToolLead::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'tools' => $this->toolOptions(),
            'profiles' => ToolLead::PROFILES,
        ])->layout('layouts.app', [
            'title' => 'Contactos de herramientas - Admin - Agro365',
            'description' => 'Contactos captados en las herramientas públicas gratuitas',
        ]);
    }

    public function toolTitle(string $tool): string
    {
        return $this->toolOptions()[$tool] ?? $tool;
    }

    /** @return array<string, string> */
    private function toolOptions(): array
    {
        return ['tools-index' => 'Índice de herramientas']
            + array_map(fn (array $tool) => $tool['title'], ToolsController::TOOLS);
    }

    /** @return Builder<ToolLead> */
    private function baseQuery(): Builder
    {
        $query = ToolLead::query();

        if ($this->filterTool !== '') {
            $query->where('tool', $this->filterTool);
        }
        if ($this->filterProfile !== '') {
            $query->where('profile', $this->filterProfile);
        }
        if ($this->filterStatus === 'pending') {
            $query->whereNull('contacted_at');
        } elseif ($this->filterStatus === 'contacted') {
            $query->whereNotNull('contacted_at');
        }
        if ($this->search !== '') {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('email', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%"));
        }

        return $query;
    }
}
