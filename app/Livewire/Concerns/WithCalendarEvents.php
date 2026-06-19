<?php

namespace App\Livewire\Concerns;

use App\Models\Campaign;
use App\Models\CommercialAuthorization;
use App\Models\EnergyUsage;
use App\Models\FieldApplicator;
use App\Models\FieldEquipment;
use App\Models\PacDeclaration;
use App\Models\PacPayment;
use App\Models\ResidueAnalysis;
use App\Models\ResidueManagement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait WithCalendarEvents
{
    public function getEventsForPeriod(Carbon $start, Carbon $end): \Illuminate\Support\Collection
    {
        $userId = Auth::id();
        $events = collect();
        $today = Carbon::today();

        try {
            FieldApplicator::forViticulturist($userId)
                ->whereNotNull('ropo_expiry_date')
                ->whereBetween('ropo_expiry_date', [$start, $end])
                ->get()
                ->each(function ($item) use (&$events, $today) {
                    $expires = Carbon::parse($item->ropo_expiry_date);
                    $events->push([
                        'date' => $expires->format('Y-m-d'),
                        'type' => 'alert_ropo',
                        'label' => __('ROPO vence'),
                        'description' => $item->name ?? '',
                        'urgency' => $expires->diffInDays($today, false) >= 0 ? 'danger' : ($expires->diffInDays($today) <= 30 ? 'warning' : 'normal'),
                    ]);
                });
        } catch (\Exception $e) {
        }

        try {
            FieldEquipment::forViticulturist($userId)
                ->whereNotNull('next_inspection_date')
                ->whereBetween('next_inspection_date', [$start, $end])
                ->get()
                ->each(function ($item) use (&$events, $today) {
                    $due = Carbon::parse($item->next_inspection_date);
                    $events->push([
                        'date' => $due->format('Y-m-d'),
                        'type' => 'alert_itb',
                        'label' => __('Inspección ITB'),
                        'description' => $item->name ?? '',
                        'urgency' => $due->diffInDays($today, false) >= 0 ? 'danger' : ($due->diffInDays($today) <= 30 ? 'warning' : 'normal'),
                    ]);
                });
        } catch (\Exception $e) {
        }

        try {
            CommercialAuthorization::forViticulturist($userId)
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [$start, $end])
                ->get()
                ->each(function ($item) use (&$events, $today) {
                    $expires = Carbon::parse($item->expiry_date);
                    $events->push([
                        'date' => $expires->format('Y-m-d'),
                        'type' => 'alert_authorization',
                        'label' => __('Autorización vence'),
                        'description' => $item->name ?? $item->authorization_code ?? '',
                        'urgency' => $expires->diffInDays($today, false) >= 0 ? 'danger' : ($expires->diffInDays($today) <= 30 ? 'warning' : 'normal'),
                    ]);
                });
        } catch (\Exception $e) {
        }

        try {
            ResidueAnalysis::forViticulturist($userId)
                ->whereBetween('analysis_date', [$start, $end])
                ->with('plotPlanting.plot')
                ->get()
                ->each(function ($item) use (&$events) {
                    $events->push([
                        'date' => Carbon::parse($item->analysis_date)->format('Y-m-d'),
                        'type' => 'residue_analysis',
                        'label' => __('Análisis residuos'),
                        'description' => $item->plotPlanting?->plot->name ?? '',
                        'urgency' => 'normal',
                    ]);
                });
        } catch (\Exception $e) {
        }

        try {
            ResidueManagement::forViticulturist($userId)
                ->whereBetween('date', [$start, $end])
                ->get()
                ->each(function ($item) use (&$events) {
                    $events->push([
                        'date' => Carbon::parse($item->date)->format('Y-m-d'),
                        'type' => 'residue_management',
                        'label' => __('Gestión residuos'),
                        'description' => $item->residue_type ?? '',
                        'urgency' => 'normal',
                    ]);
                });
        } catch (\Exception $e) {
        }

        try {
            EnergyUsage::forViticulturist($userId)
                ->whereBetween('date', [$start, $end])
                ->get()
                ->each(function ($item) use (&$events) {
                    $events->push([
                        'date' => Carbon::parse($item->date)->format('Y-m-d'),
                        'type' => 'energy',
                        'label' => __('Consumo energético'),
                        'description' => $item->energy_type ?? '',
                        'urgency' => 'normal',
                    ]);
                });
        } catch (\Exception $e) {
        }

        try {
            PacDeclaration::forViticulturist($userId)
                ->whereNotNull('submitted_at')
                ->whereBetween('submitted_at', [$start, $end])
                ->get()
                ->each(function ($item) use (&$events) {
                    $events->push([
                        'date' => Carbon::parse($item->submitted_at)->format('Y-m-d'),
                        'type' => 'pac_declaration',
                        'label' => __('Declaración PAC'),
                        'description' => $item->campaign_year ?? '',
                        'urgency' => 'normal',
                    ]);
                });
        } catch (\Exception $e) {
        }

        try {
            PacPayment::forViticulturist($userId)
                ->whereNotNull('payment_date')
                ->whereBetween('payment_date', [$start, $end])
                ->get()
                ->each(function ($item) use (&$events) {
                    $events->push([
                        'date' => Carbon::parse($item->payment_date)->format('Y-m-d'),
                        'type' => 'pac_payment',
                        'label' => __('Pago PAC'),
                        'description' => $item->concept ?? '',
                        'urgency' => 'normal',
                    ]);
                });
        } catch (\Exception $e) {
        }

        if ($this->selectedCampaign) {
            try {
                $campaign = Campaign::find($this->selectedCampaign);
                if ($campaign) {
                    foreach ([
                        'mid_validation_date' => __('Validación intermedia'),
                        'final_validation_date' => __('Validación final'),
                    ] as $field => $label) {
                        if ($campaign->$field) {
                            $date = Carbon::parse($campaign->$field);
                            if ($date->between($start, $end)) {
                                $events->push([
                                    'date' => $date->format('Y-m-d'),
                                    'type' => 'campaign_milestone',
                                    'label' => $label,
                                    'description' => $campaign->name,
                                    'urgency' => 'normal',
                                ]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
            }
        }

        return $events->groupBy('date');
    }

    public function getEventsForDate(int $userId, string $date): \Illuminate\Support\Collection
    {
        $day = Carbon::parse($date);

        return $this->getEventsForPeriod($day->copy()->startOfDay(), $day->copy()->endOfDay())
            ->get($date, collect());
    }
}
