<?php

namespace App\Livewire\Viticulturist\Support;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\SupportTicket;
use App\Notifications\SupportTicketCreatedNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateTicket extends Component
{
    use WithToastNotifications, WithFileUploads;

    public $title = '';
    public $description = '';
    public array $images = [];
    public $type = 'question';
    public $priority = 'medium';

    protected function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'images'      => 'nullable|array|max:5',
            'images.*'    => 'image|max:5120',
            'type'        => 'required|in:bug,feature,improvement,question',
            'priority'    => 'required|in:low,medium,high,urgent',
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required'       => 'El título es obligatorio.',
            'title.max'            => 'El título no puede tener más de 255 caracteres.',
            'description.required' => 'La descripción es obligatoria.',
            'description.min'      => 'La descripción debe tener al menos 10 caracteres.',
            'images.max'           => 'Puedes adjuntar un máximo de 5 imágenes.',
            'images.*.image'       => 'Cada archivo debe ser una imagen válida.',
            'images.*.max'         => 'Cada imagen no puede superar 5MB.',
            'type.required'        => 'Debes seleccionar un tipo de ticket.',
            'type.in'              => 'El tipo de ticket seleccionado no es válido.',
            'priority.required'    => 'Debes seleccionar una prioridad.',
            'priority.in'          => 'La prioridad seleccionada no es válida.',
        ];
    }

    public function updatedImages()
    {
        $this->validateOnly('images', [
            'images'   => 'nullable|array|max:5',
            'images.*' => 'image|max:5120',
        ]);
    }

    public function save()
    {
        $this->validate();

        $imagePaths = [];
        foreach ($this->images as $image) {
            $imagePaths[] = $image->store('support-tickets', 'public');
        }

        $ticket = SupportTicket::create([
            'user_id'     => Auth::id(),
            'title'       => $this->title,
            'description' => $this->description,
            'images'      => $imagePaths ?: null,
            'type'        => $this->type,
            'priority'    => $this->priority,
            'status'      => 'open',
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

        return $this->redirect(route('viticulturist.support.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.viticulturist.support.create-ticket')
            ->layout('layouts.app');
    }
}
