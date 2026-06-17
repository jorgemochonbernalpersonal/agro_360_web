<?php

namespace App\Livewire\Winery\TastingNotes;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Oenologist;
use App\Models\Wine;
use App\Models\WineTastingNote;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read mixed $wines
 * @property-read mixed $oenologists
 * @property-read mixed $selectedWine
 */
class Edit extends Component
{
    use WithOwnershipRules, WithRoleAwareRedirect, WithToastNotifications;

    public WineTastingNote $tastingNote;

    public string $wine_id = '';

    public string $evaluation_date = '';

    public string $evaluator_name = '';

    public string $oenologist_id = '';

    public string $visual_color = '';

    public string $visual_clarity = '';

    public string $visual_intensity = '';

    public string $aroma_intensity = '';

    public string $aroma_descriptors = '';

    public string $palate_acidity = '';

    public string $palate_tannins = '';

    public string $palate_body = '';

    public string $palate_finish = '';

    public string $overall_score = '';

    public string $overall_conclusion = '';

    public string $notes = '';

    public function mount(WineTastingNote $tastingNote): void
    {
        $this->authorize('update', $tastingNote);

        $this->tastingNote = $tastingNote;

        $this->wine_id = (string) $tastingNote->wine_id;
        $this->evaluation_date = $tastingNote->evaluation_date->toDateString();
        $this->evaluator_name = $tastingNote->evaluator_name ?? '';
        $this->oenologist_id = (string) ($tastingNote->oenologist_id ?? '');
        $this->visual_color = $tastingNote->visual_color ?? '';
        $this->visual_clarity = $tastingNote->visual_clarity ?? '';
        $this->visual_intensity = $tastingNote->visual_intensity ?? '';
        $this->aroma_intensity = $tastingNote->aroma_intensity ?? '';
        $this->aroma_descriptors = $tastingNote->aroma_descriptors ?? '';
        $this->palate_acidity = $tastingNote->palate_acidity ?? '';
        $this->palate_tannins = $tastingNote->palate_tannins ?? '';
        $this->palate_body = $tastingNote->palate_body ?? '';
        $this->palate_finish = $tastingNote->palate_finish ?? '';
        $this->overall_score = (string) ($tastingNote->overall_score ?? '');
        $this->overall_conclusion = $tastingNote->overall_conclusion ?? '';
        $this->notes = $tastingNote->notes ?? '';
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

    public function save(): void
    {
        $data = $this->validate();

        Wine::where('user_id', Auth::id())->findOrFail($data['wine_id']);

        $this->tastingNote->update([
            'wine_id' => $data['wine_id'],
            'oenologist_id' => $data['oenologist_id'] ?: null,
            'evaluation_date' => $data['evaluation_date'],
            'evaluator_name' => $data['evaluator_name'] ?: null,
            'visual_color' => $data['visual_color'] ?: null,
            'visual_clarity' => $data['visual_clarity'] ?: null,
            'visual_intensity' => $data['visual_intensity'] ?: null,
            'aroma_intensity' => $data['aroma_intensity'] ?: null,
            'aroma_descriptors' => $data['aroma_descriptors'] ?: null,
            'palate_acidity' => $data['palate_acidity'] ?: null,
            'palate_tannins' => $data['palate_tannins'] ?: null,
            'palate_body' => $data['palate_body'] ?: null,
            'palate_finish' => $data['palate_finish'] ?: null,
            'overall_score' => $data['overall_score'] ?: null,
            'overall_conclusion' => $data['overall_conclusion'] ?: null,
            'notes' => $data['notes'] ?: null,
        ]);

        $this->toastSuccess(__('Nota de cata actualizada correctamente.'));
        $this->roleRedirect('tasting-notes.index');
    }

    public function render()
    {
        return view('livewire.winery.tasting-notes.edit', [
            'visualClarityOptions' => WineTastingNote::visualClarityOptions(),
            'visualIntensityOptions' => WineTastingNote::visualIntensityOptions(),
            'aromaIntensityOptions' => WineTastingNote::aromaIntensityOptions(),
            'palateLevelOptions' => WineTastingNote::palateLevelOptions(),
            'palateBodyOptions' => WineTastingNote::palateBodyOptions(),
            'palateFinishOptions' => WineTastingNote::palateFinishOptions(),
            'wines' => $this->wines,
            'oenologists' => $this->oenologists,
            'selectedWine' => $this->selectedWine,
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        return [
            'wine_id' => $this->ownedWineRule(),
            'evaluation_date' => ['required', 'date'],
            'evaluator_name' => ['nullable', 'string', 'max:255'],
            'oenologist_id' => $this->ownedOenologistRule(),
            'visual_color' => ['nullable', 'string', 'max:100'],
            'visual_clarity' => ['nullable', 'in:'.implode(',', array_keys(WineTastingNote::VISUAL_CLARITY))],
            'visual_intensity' => ['nullable', 'in:'.implode(',', array_keys(WineTastingNote::VISUAL_INTENSITY))],
            'aroma_intensity' => ['nullable', 'in:'.implode(',', array_keys(WineTastingNote::AROMA_INTENSITY))],
            'aroma_descriptors' => ['nullable', 'string'],
            'palate_acidity' => ['nullable', 'in:'.implode(',', array_keys(WineTastingNote::PALATE_LEVEL))],
            'palate_tannins' => ['nullable', 'in:'.implode(',', array_keys(WineTastingNote::PALATE_LEVEL))],
            'palate_body' => ['nullable', 'in:'.implode(',', array_keys(WineTastingNote::PALATE_BODY))],
            'palate_finish' => ['nullable', 'in:'.implode(',', array_keys(WineTastingNote::PALATE_FINISH))],
            'overall_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'overall_conclusion' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
