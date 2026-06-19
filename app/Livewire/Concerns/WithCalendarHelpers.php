<?php

namespace App\Livewire\Concerns;

trait WithCalendarHelpers
{
    public function getActivityTypeColor($type): string
    {
        return match ($type) {
            'phytosanitary' => 'bg-red-100 text-red-700 border-red-300',
            'fertilization' => 'bg-blue-100 text-blue-700 border-blue-300',
            'irrigation' => 'bg-cyan-100 text-cyan-700 border-cyan-300',
            'cultural' => 'bg-yellow-100 text-yellow-700 border-yellow-300',
            'observation' => 'bg-zinc-100 text-zinc-700 border-zinc-300',
            'pruning' => 'bg-lime-100 text-lime-700 border-lime-300',
            'harvest' => 'bg-amber-100 text-amber-700 border-amber-300',
            'post_harvest' => 'bg-purple-100 text-purple-700 border-purple-300',
            default => 'bg-zinc-100 text-zinc-700 border-zinc-300',
        };
    }

    public function getActivityTypeLabel($type): string
    {
        return match ($type) {
            'phytosanitary' => __('Tratamiento'),
            'fertilization' => __('Fertilización'),
            'irrigation' => __('Riego'),
            'cultural' => __('Labor'),
            'observation' => __('Observación'),
            'pruning' => __('Poda'),
            'harvest' => __('Vendimia'),
            'post_harvest' => __('Post-vendimia'),
            default => __('Actividad'),
        };
    }

    public function getEventColor($type, $urgency = 'normal'): string
    {
        if (in_array($type, ['alert_ropo', 'alert_itb', 'alert_authorization'])) {
            return match ($urgency) {
                'danger' => 'bg-red-100 text-red-800 border-red-400',
                'warning' => 'bg-orange-100 text-orange-800 border-orange-400',
                default => 'bg-orange-50 text-orange-700 border-orange-300',
            };
        }

        return match ($type) {
            'residue_analysis' => 'bg-purple-100 text-purple-700 border-purple-300',
            'residue_management' => 'bg-slate-100 text-slate-700 border-slate-300',
            'energy' => 'bg-amber-100 text-amber-700 border-amber-300',
            'pac_declaration' => 'bg-amber-100 text-amber-800 border-amber-400',
            'pac_payment' => 'bg-teal-100 text-teal-700 border-teal-300',
            'campaign_milestone' => 'bg-green-100 text-green-800 border-green-400',
            default => 'bg-zinc-100 text-zinc-700 border-zinc-300',
        };
    }

    public function getEventLabel($type): string
    {
        return match ($type) {
            'alert_ropo' => __('ROPO vence'),
            'alert_itb' => __('Inspección ITB'),
            'alert_authorization' => __('Autorización vence'),
            'residue_analysis' => __('Análisis residuos'),
            'residue_management' => __('Gestión residuos'),
            'energy' => __('Consumo energético'),
            'pac_declaration' => __('Declaración PAC'),
            'pac_payment' => __('Pago PAC'),
            'campaign_milestone' => __('Hito campaña'),
            default => __('Evento'),
        };
    }
}
