<?php

namespace App\Livewire\Winery\TastingNotes;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Winery\AbstractEdit;
use App\Models\Oenologist;
use App\Models\Wine;
use App\Models\WineTastingNote;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

/**
 * @property-read mixed $wines
 * @property-read mixed $oenologists
 * @property-read mixed $selectedWine
 */
class Edit extends AbstractEdit
{
    use WithOwnershipRules;

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

    public function updatedWineId(): void
    {
        unset($this->selectedWine);
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

    protected function performUpdate(): void
    {
        Wine::where('user_id', Auth::id())->findOrFail($this->wine_id);

        $this->tastingNote->update([
            'wine_id' => $this->wine_id,
            'oenologist_id' => $this->oenologist_id ?: null,
            'evaluation_date' => $this->evaluation_date,
            'evaluator_name' => $this->evaluator_name ?: null,
            'visual_color' => $this->visual_color ?: null,
            'visual_clarity' => $this->visual_clarity ?: null,
            'visual_intensity' => $this->visual_intensity ?: null,
            'aroma_intensity' => $this->aroma_intensity ?: null,
            'aroma_descriptors' => $this->aroma_descriptors ?: null,
            'palate_acidity' => $this->palate_acidity ?: null,
            'palate_tannins' => $this->palate_tannins ?: null,
            'palate_body' => $this->palate_body ?: null,
            'palate_finish' => $this->palate_finish ?: null,
            'overall_score' => $this->overall_score ?: null,
            'overall_conclusion' => $this->overall_conclusion ?: null,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Nota de cata actualizada correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'winery.tasting-notes.index';
    }

    protected function viewData(): array
    {
        return [
            'visualClarityOptions' => WineTastingNote::visualClarityOptions(),
            'visualIntensityOptions' => WineTastingNote::visualIntensityOptions(),
            'aromaIntensityOptions' => WineTastingNote::aromaIntensityOptions(),
            'palateLevelOptions' => WineTastingNote::palateLevelOptions(),
            'palateBodyOptions' => WineTastingNote::palateBodyOptions(),
            'palateFinishOptions' => WineTastingNote::palateFinishOptions(),
            'wines' => $this->wines,
            'oenologists' => $this->oenologists,
            'selectedWine' => $this->selectedWine,
        ];
    }
}
