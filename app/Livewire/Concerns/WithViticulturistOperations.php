<?php

namespace App\Livewire\Concerns;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\HarvestDelivery;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Plot;
use App\Models\Subscription;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\ViticulturistInvitationNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait WithViticulturistOperations
{
    public function sendInvitation(int $viticulturistId): void
    {
        $user = Auth::user();

        $canEdit = WineryViticulturist::editableBy($user)
            ->where('viticulturist_id', $viticulturistId)
            ->exists();

        if (! $canEdit) {
            $this->toastError(__('No tienes permiso para gestionar este viticultor.'));

            return;
        }

        $viticulturist = User::find($viticulturistId);

        if (! $viticulturist) {
            $this->toastError(__('Viticultor no encontrado.'));

            return;
        }

        if ($viticulturist->invitation_sent_at !== null) {
            $this->toastError(__('La invitación ya fue enviada anteriormente.'));

            return;
        }

        if ($viticulturist->can_login) {
            $this->toastError(__('Este viticultor ya puede iniciar sesión. No es necesario enviar invitación.'));

            return;
        }

        $plainToken = Str::random(64);
        $viticulturist->update([
            'invitation_token' => hash('sha256', $plainToken),
            'invitation_sent_at' => now(),
            'invitation_expires_at' => now()->addDays(7),
        ]);

        try {
            $viticulturist->notify(new ViticulturistInvitationNotification($user, $plainToken));
            $this->toastSuccess(__('Invitación enviada correctamente por email.'));
        } catch (\Exception $e) {
            Log::error('Error al enviar invitación', [
                'error' => $e->getMessage(),
                'viticulturist_id' => $viticulturistId,
                'user_id' => $user->id,
            ]);
            $this->toastError(__('Error al enviar la invitación. Por favor, intenta de nuevo.'));
        }
    }

    public function deleteViticulturist($viticulturistId): void
    {
        $user = Auth::user();

        $relation = WineryViticulturist::where('viticulturist_id', $viticulturistId)
            ->where('parent_viticulturist_id', $user->id)
            ->first();

        if (! $relation) {
            $this->toastError(__('No tienes permiso para eliminar este viticultor.'));

            return;
        }

        $hasPlots = Plot::where('viticulturist_id', $viticulturistId)->exists();
        $hasCampaigns = Campaign::where('viticulturist_id', $viticulturistId)->exists();
        $hasActivities = AgriculturalActivity::where('viticulturist_id', $viticulturistId)->exists();
        $hasDeliveries = HarvestDelivery::where('viticulturist_id', $viticulturistId)->exists();
        $hasInvoices = Invoice::where('user_id', $viticulturistId)->exists();
        $hasCrews = Crew::where('viticulturist_id', $viticulturistId)->exists();
        $hasSubs = Subscription::where('user_id', $viticulturistId)->exists();
        $hasPayments = Payment::where('user_id', $viticulturistId)->exists();
        $hasSupervisorLinks = SupervisorViticulturist::where('viticulturist_id', $viticulturistId)->exists();
        $hasWineryRelations = WineryViticulturist::where('viticulturist_id', $viticulturistId)
            ->where(function ($q) use ($user) {
                $q->where('source', '!=', WineryViticulturist::SOURCE_VITICULTURIST)
                    ->orWhere('parent_viticulturist_id', '!=', $user->id);
            })
            ->exists();

        if ($hasPlots || $hasCampaigns || $hasActivities || $hasDeliveries || $hasInvoices
            || $hasCrews || $hasSubs || $hasPayments || $hasSupervisorLinks || $hasWineryRelations) {
            $this->toastError(__('No se puede eliminar el viticultor porque tiene datos relacionados.'));

            return;
        }

        $vit = User::find($viticulturistId);
        if (! $vit) {
            $this->toastError(__('Viticultor no encontrado.'));

            return;
        }

        try {
            $vit->delete();
            $this->toastSuccess(__('Viticultor eliminado correctamente.'));
        } catch (\Exception $e) {
            Log::error('Error al eliminar viticultor', [
                'error' => $e->getMessage(),
                'viticulturist_id' => $viticulturistId,
                'user_id' => $user->id,
            ]);
            $this->toastError(__('Error al eliminar el viticultor. Por favor, intenta de nuevo.'));
        }
    }
}
