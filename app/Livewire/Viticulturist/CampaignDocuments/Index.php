<?php

namespace App\Livewire\Viticulturist\CampaignDocuments;

use App\Models\Campaign;
use App\Models\CampaignDocument;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithViticulturistValidation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithToastNotifications, WithFileUploads, WithViticulturistValidation;

    // Filters
    public $filterCampaign = '';
    public $filterType = '';

    // Form
    public bool $showModal = false;
    public ?int $editingId = null;

    public $campaign_id = '';
    public $name = '';
    public $document_type = 'other';
    public $notes = '';
    public $uploadedFile = null;

    public function mount()
    {
        $user = Auth::user();
        $campaign = Campaign::getOrCreateActiveForYear($user->id);
        $this->filterCampaign = $campaign?->id ?? '';
        $this->campaign_id = $campaign?->id ?? '';
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $entry = CampaignDocument::where('viticulturist_id', Auth::id())->findOrFail($id);
        $this->editingId = $id;
        $this->campaign_id = $entry->campaign_id;
        $this->name = $entry->name;
        $this->document_type = $entry->document_type;
        $this->notes = $entry->notes ?? '';
        $this->showModal = true;
    }

    public function save()
    {
        $rules = $this->rules();
        $this->validate($rules);
        $user = Auth::user();

        $data = [
            'campaign_id'      => $this->campaign_id,
            'viticulturist_id' => $user->id,
            'name'             => $this->name,
            'document_type'    => $this->document_type,
            'notes'            => $this->notes ?: null,
        ];

        if ($this->uploadedFile) {
            $path = $this->uploadedFile->store(
                "campaign-documents/{$user->id}",
                'private'
            );
            $data['file_path'] = $path;
            $data['original_filename'] = $this->uploadedFile->getClientOriginalName();
            $data['mime_type'] = $this->uploadedFile->getMimeType();
            $data['file_size_kb'] = (int) ceil($this->uploadedFile->getSize() / 1024);
        }

        if ($this->editingId) {
            CampaignDocument::where('viticulturist_id', $user->id)
                ->findOrFail($this->editingId)
                ->update($data);
            $this->toastSuccess('Documento actualizado correctamente.');
        } else {
            CampaignDocument::create($data);
            $this->toastSuccess('Documento subido correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id)
    {
        $doc = CampaignDocument::where('viticulturist_id', Auth::id())->findOrFail($id);
        if ($doc->file_path) {
            Storage::disk('private')->delete($doc->file_path);
        }
        $doc->delete();
        $this->toastSuccess('Documento eliminado.');
    }

    protected function rules(): array
    {
        $fileRule = $this->editingId ? 'nullable' : 'required';
        return [
            'campaign_id'    => $this->campaignOwnershipRule(),
            'name'           => 'required|string|max:255',
            'document_type'  => 'required|in:' . implode(',', array_keys(CampaignDocument::DOCUMENT_TYPES)),
            'notes'          => 'nullable|string',
            'uploadedFile'   => "{$fileRule}|file|max:20480|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx",
        ];
    }

    protected function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->document_type = 'other';
        $this->notes = '';
        $this->uploadedFile = null;
        $this->resetValidation();
    }

    public function render()
    {
        $user = Auth::user();

        $query = CampaignDocument::where('viticulturist_id', $user->id);

        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
        if ($this->filterType) {
            $query->where('document_type', $this->filterType);
        }

        $entries = $query->orderByDesc('created_at')->paginate(15);
        $campaigns = Campaign::forViticulturist($user->id)->orderByDesc('year')->get();

        $base = CampaignDocument::where('viticulturist_id', $user->id);
        $stats = [
            'total'        => (clone $base)->count(),
            'this_campaign'=> $this->filterCampaign ? (clone $base)->where('campaign_id', $this->filterCampaign)->count() : (clone $base)->count(),
        ];

        return view('livewire.viticulturist.campaign-documents.index', [
            'entries'       => $entries,
            'campaigns'     => $campaigns,
            'documentTypes' => CampaignDocument::DOCUMENT_TYPES,
            'stats'         => $stats,
        ]);
    }
}
