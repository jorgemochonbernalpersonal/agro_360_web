<?php

namespace App\Livewire\Admin\CannedResponses;

use App\Livewire\Concerns\WithReadOnlyGuard;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\CannedResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use WithReadOnlyGuard, WithToastNotifications;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $body = '';

    public string $category = '';

    public int $sort_order = 0;

    public function openCreate(): void
    {
        $this->reset(['title', 'body', 'category', 'sort_order', 'editingId']);
        $this->showModal = true;
        $this->resetValidation();
    }

    public function openEdit(int $id): void
    {
        $r = CannedResponse::findOrFail($id);
        $this->editingId = $id;
        $this->title = $r->title;
        $this->body = $r->body;
        $this->category = $r->category ?? '';
        $this->sort_order = $r->sort_order;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $this->validate([
            'title' => 'required|string|max:120',
            'body' => 'required|string',
            'category' => 'nullable|string|max:50',
        ], [
            'title.required' => __('El título es obligatorio.'),
            'body.required' => __('El cuerpo es obligatorio.'),
        ]);

        $data = [
            'title' => $this->title,
            'body' => $this->body,
            'category' => $this->category ?: null,
            'sort_order' => $this->sort_order,
            'admin_id' => Auth::id(),
        ];

        if ($this->editingId) {
            CannedResponse::findOrFail($this->editingId)->update($data);
            $this->toastSuccess(__('Respuesta actualizada.'));
        } else {
            CannedResponse::create($data);
            $this->toastSuccess(__('Respuesta creada.'));
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        CannedResponse::findOrFail($id)->delete();
        $this->toastSuccess(__('Respuesta eliminada.'));
    }

    public function render()
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('canned_responses')) {
            return view('livewire.admin.canned-responses.index', ['responses' => collect(), 'categories' => collect()])
                ->layout('layouts.app', [
                    'title' => __('Respuestas Rápidas - Admin - Agro365'),
                    'description' => __('Gestiona plantillas de respuesta para tickets de soporte'),
                ]);
        }

        $responses = CannedResponse::with('admin:id,name')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $categories = $responses->pluck('category')->filter()->unique()->values();

        return view('livewire.admin.canned-responses.index', compact('responses', 'categories'))
            ->layout('layouts.app', [
                'title' => __('Respuestas Rápidas - Admin - Agro365'),
                'description' => __('Gestiona plantillas de respuesta para tickets de soporte'),
            ]);
    }
}
