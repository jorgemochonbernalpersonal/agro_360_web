<?php

namespace App\Livewire\Viticulturist\Support;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\SupportTicket;
use App\Notifications\SupportTicketCreatedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateTicket extends Component
{
    use WithToastNotifications, WithFileUploads;

    public $title = '';
    public $description = '';
    public $image;
    public $image_preview;
    public $type = 'question';
    public $priority = 'medium';

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'image' => 'nullable|image|max:5120',  // Max 5MB
            'type' => 'required|in:bug,feature,improvement,question',
            'priority' => 'required|in:low,medium,high,urgent',
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede tener más de 255 caracteres.',
            'description.required' => 'La descripción es obligatoria.',
            'description.min' => 'La descripción debe tener al menos 10 caracteres.',
            'image.image' => 'El archivo debe ser una imagen válida.',
            'image.max' => 'La imagen no puede ser mayor a 5MB.',
            'type.required' => 'Debes seleccionar un tipo de ticket.',
            'type.in' => 'El tipo de ticket seleccionado no es válido.',
            'priority.required' => 'Debes seleccionar una prioridad.',
            'priority.in' => 'La prioridad seleccionada no es válida.',
        ];
    }

    public function updatedImage()
    {
        $this->validateOnly('image', [
            'image' => 'nullable|image|max:5120',
        ]);

        if ($this->image) {
            try {
                // Intentar usar temporaryUrl() para la previsualización
                $this->image_preview = $this->image->temporaryUrl();
            } catch (\Exception $e) {
                // Si falla temporaryUrl (común en producción), intentar crear una URL local
                try {
                    $path = $this->image->store('temp', 'public');
                    $this->image_preview = Storage::disk('public')->url($path);
                } catch (\Exception $e2) {
                    // Si también falla, no mostrar error - el preview de JavaScript funcionará
                    // El usuario podrá ver el preview usando FileReader en el cliente
                    $this->image_preview = null;
                }
            }
        } else {
            $this->image_preview = null;
        }
    }

    public function save()
    {
        $this->validate();

        $imagePath = null;

        // Guardar imagen si existe
        if ($this->image) {
            $imagePath = $this->image->store('support-tickets', 'public');
        }

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'title' => $this->title,
            'description' => $this->description,
            'image' => $imagePath,
            'type' => $this->type,
            'priority' => $this->priority,
            'status' => 'open',
        ]);

        // Cargar la relación del usuario para la notificación
        $ticket->load('user');

        // Enviar notificación por email a la dirección configurada
        try {
            $supportEmail = env('SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS', 'info@agro365.es'));

            // Crear un usuario temporal para la notificación (solo necesita email y name)
            $supportUser = new \App\Models\User();
            $supportUser->email = $supportEmail;
            $supportUser->name = 'Equipo de Soporte';

            $supportUser->notify(new SupportTicketCreatedNotification($ticket));
        } catch (\Exception $e) {
            // Log del error pero no fallar la creación del ticket
            $supportEmail = env('SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS', 'info@agro365.es'));
            \Log::error('Error al enviar notificación de ticket de soporte: ' . $e->getMessage(), [
                'ticket_id' => $ticket->id,
                'support_email' => $supportEmail,
                'error' => $e->getTraceAsString()
            ]);
        }

        $this->toastSuccess('Ticket creado exitosamente. Te contactaremos pronto.');

        return redirect()->route('viticulturist.support.index');
    }

    public function render()
    {
        return view('livewire.viticulturist.support.create-ticket')
            ->layout('layouts.app');
    }
}
