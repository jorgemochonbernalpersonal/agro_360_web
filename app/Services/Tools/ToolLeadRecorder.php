<?php

namespace App\Services\Tools;

use App\Models\ToolLead;
use App\Notifications\NewToolLeadNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Guarda un contacto de las herramientas públicas y avisa al equipo.
 */
class ToolLeadRecorder
{
    /**
     * @param array<string, mixed> $context
     */
    public function record(string $email, string $tool, array $context = [], ?string $name = null, ?string $profile = null): ToolLead
    {
        $lead = ToolLead::create([
            'email' => mb_strtolower(trim($email)),
            'name' => $name ?: null,
            'profile' => $profile ?: null,
            'tool' => $tool,
            'context' => $context ?: null,
            'ip_hash' => hash('sha256', (string) request()->ip().config('app.key')),
            'consented_at' => now(),
        ]);

        // El aviso interno no debe romper la experiencia del visitante.
        try {
            Notification::route('mail', config('support.email', config('mail.from.address', 'info@agro365.es')))
                ->notify(new NewToolLeadNotification($lead));
        } catch (\Throwable $e) {
            Log::error('Error al notificar nuevo contacto de herramientas: '.$e->getMessage(), ['lead_id' => $lead->id]);
        }

        return $lead;
    }
}
