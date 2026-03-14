<?php

namespace App\Livewire\Winery\TastingNotes;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Oenologist;
use App\Models\Wine;
use App\Models\WineTastingNote;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $wine_id             = '';
    public string $evaluation_date     = '';
    public string $evaluator_name      = '';
    public string $oenologist_id       = '';

    // Visual
    public string $visual_color     = '';
    public string $visual_clarity   = '';
    public string $visual_intensity = '';

    // Olfativo
    public string $aroma_intensity    = '';
    public string $aroma_descriptors  = '';

    // Gustativo
    public string $palate_acidity = '';
    public string $palate_tannins = '';
    public string $palate_body    = '';
    public string $palate_finish  = '';

    // Valoración
    public string $overall_score      = '';
    public string $overall_conclusion = '';
    public string $notes              = '';

    public function mount(): void
    {
        $this->evaluation_date = now()->toDateString();
    }

    #[Computed]
    public function wines()
    {
        return Wine::where('user_id', Auth::id())->active()->orderBy('name')->get();
    }

    #[Computed]
    public function oenologists()
    {
        return Oenologist::where('user_id', Auth::id())->where('active', true)->orderBy('surname')->get();
    }

    #[Computed]
    public function selectedWine(): ?Wine
    {
        return $this->wine_id ? Wine::find($this->wine_id) : null;
    }

    public function updatedWineId(): void
    {
        unset($this->selectedWine);
    }

    protected function rules(): array
    {
        return [
            'wine_id'             => ['required', 'exists:wines,id'],
            'evaluation_date'     => ['required', 'date'],
            'evaluator_name'      => ['nullable', 'string', 'max:255'],
            'oenologist_id'       => ['nullable', 'exists:oenologists,id'],
            'visual_color'        => ['nullable', 'string', 'max:100'],
            'visual_clarity'      => ['nullable', 'in:' . implode(',', array_keys(WineTastingNote::VISUAL_CLARITY))],
            'visual_intensity'    => ['nullable', 'in:' . implode(',', array_keys(WineTastingNote::VISUAL_INTENSITY))],
            'aroma_intensity'     => ['nullable', 'in:' . implode(',', array_keys(WineTastingNote::AROMA_INTENSITY))],
            'aroma_descriptors'   => ['nullable', 'string'],
            'palate_acidity'      => ['nullable', 'in:' . implode(',', array_keys(WineTastingNote::PALATE_LEVEL))],
            'palate_tannins'      => ['nullable', 'in:' . implode(',', array_keys(WineTastingNote::PALATE_LEVEL))],
            'palate_body'         => ['nullable', 'in:' . implode(',', array_keys(WineTastingNote::PALATE_BODY))],
            'palate_finish'       => ['nullable', 'in:' . implode(',', array_keys(WineTastingNote::PALATE_FINISH))],
            'overall_score'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'overall_conclusion'  => ['nullable', 'string'],
            'notes'               => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        Wine::where('user_id', Auth::id())->findOrFail($data['wine_id']);

        WineTastingNote::create([
            'user_id'            => Auth::id(),
            'wine_id'            => $data['wine_id'],
            'oenologist_id'      => $data['oenologist_id'] ?: null,
            'evaluation_date'    => $data['evaluation_date'],
            'evaluator_name'     => $data['evaluator_name'] ?: null,
            'visual_color'       => $data['visual_color'] ?: null,
            'visual_clarity'     => $data['visual_clarity'] ?: null,
            'visual_intensity'   => $data['visual_intensity'] ?: null,
            'aroma_intensity'    => $data['aroma_intensity'] ?: null,
            'aroma_descriptors'  => $data['aroma_descriptors'] ?: null,
            'palate_acidity'     => $data['palate_acidity'] ?: null,
            'palate_tannins'     => $data['palate_tannins'] ?: null,
            'palate_body'        => $data['palate_body'] ?: null,
            'palate_finish'      => $data['palate_finish'] ?: null,
            'overall_score'      => $data['overall_score'] ?: null,
            'overall_conclusion' => $data['overall_conclusion'] ?: null,
            'notes'              => $data['notes'] ?: null,
            'created_by'         => Auth::id(),
        ]);

        $this->toastSuccess('Nota de cata registrada correctamente.');
        $this->redirect(route('winery.tasting-notes.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.tasting-notes.create', [
            'visualClarityOptions'  => WineTastingNote::VISUAL_CLARITY,
            'visualIntensityOptions'=> WineTastingNote::VISUAL_INTENSITY,
            'aromaIntensityOptions' => WineTastingNote::AROMA_INTENSITY,
            'palateLevelOptions'    => WineTastingNote::PALATE_LEVEL,
            'palateBodyOptions'     => WineTastingNote::PALATE_BODY,
            'palateFinishOptions'   => WineTastingNote::PALATE_FINISH,
            'wines'                 => $this->wines,
            'oenologists'           => $this->oenologists,
            'selectedWine'          => $this->selectedWine,
        ])->layout('layouts.app');
    }
}
