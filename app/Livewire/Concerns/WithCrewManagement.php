<?php

namespace App\Livewire\Concerns;

use App\Models\Crew;
use App\Models\CrewMember;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait WithCrewManagement
{
    public $assignToCrewId = '';

    public function assignToCrew(int $viticulturistId): void
    {
        if (empty($viticulturistId) || empty($this->assignToCrewId)) {
            $this->toastError(__('Debes seleccionar un equipo.'));

            return;
        }

        $user = Auth::user();

        $canEdit = WineryViticulturist::editableBy($user)
            ->where('viticulturist_id', $viticulturistId)
            ->exists();

        if (! $canEdit) {
            $this->toastError(__('No tienes permiso para gestionar este viticultor.'));

            return;
        }

        $crew = Crew::forViticulturist($user->id)
            ->where('id', $this->assignToCrewId)
            ->first();

        if (! $crew) {
            $this->toastError(__('No tienes permiso para gestionar este equipo.'));

            return;
        }

        $member = CrewMember::where('viticulturist_id', $viticulturistId)->first();

        if ($member && $member->crew_id === $crew->id) {
            $this->toastError(__('Este viticultor ya forma parte de este equipo.'));

            return;
        }

        try {
            if (! $member) {
                CrewMember::create([
                    'viticulturist_id' => $viticulturistId,
                    'crew_id' => $crew->id,
                    'assigned_by' => $user->id,
                ]);
            } else {
                $member->update([
                    'crew_id' => $crew->id,
                    'assigned_by' => $user->id,
                ]);
            }

            $this->assignToCrewId = '';
            $this->toastSuccess(__('Viticultor asignado al equipo correctamente.'));
        } catch (\Exception $e) {
            Log::error('Error al asignar viticultor a equipo', [
                'error' => $e->getMessage(),
                'viticulturist_id' => $viticulturistId,
                'crew_id' => $this->assignToCrewId,
                'user_id' => $user->id,
            ]);
            $this->toastError(__('Error al asignar el viticultor al equipo. Por favor, intenta de nuevo.'));
        }
    }

    public function makeIndividual(int $viticulturistId): void
    {
        $user = Auth::user();

        $canEdit = WineryViticulturist::editableBy($user)
            ->where('viticulturist_id', $viticulturistId)
            ->exists();

        if (! $canEdit) {
            $this->toastError(__('No tienes permiso para gestionar este viticultor.'));

            return;
        }

        $member = CrewMember::where('viticulturist_id', $viticulturistId)->first();

        if (! $member) {
            CrewMember::create([
                'viticulturist_id' => $viticulturistId,
                'crew_id' => null,
                'assigned_by' => $user->id,
            ]);
            $this->toastSuccess(__('Viticultor marcado como sin equipo.'));
        } else {
            $member->update(['crew_id' => null]);
            $this->toastSuccess(__('Viticultor convertido a sin equipo.'));
        }
    }

    public function deleteCrew(Crew $crew): void
    {
        if (! Auth::user()->can('delete', $crew)) {
            $this->toastError(__('No tienes permiso para eliminar este equipo.'));

            return;
        }

        if ($crew->activities()->exists()) {
            $this->toastError(__('No se puede eliminar un equipo con actividades asociadas.'));

            return;
        }

        try {
            DB::transaction(function () use ($crew) {
                $crew->members()->delete();
                $crew->delete();
            });
            $this->toastSuccess(__('Equipo eliminado correctamente.'));
        } catch (\Exception $e) {
            Log::error('Error deleting crew', [
                'crew_id' => $crew->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            $this->toastError(__('Hubo un error al eliminar el equipo. Por favor, inténtalo de nuevo.'));
        }
    }
}
