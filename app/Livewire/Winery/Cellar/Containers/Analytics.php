<?php

namespace App\Livewire\Winery\Cellar\Containers;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\ContainerType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Analytics extends Component
{
    use WithToastNotifications;

    public string $roomFilter = '';

    public string $typeFilter = '';

    public function exportCsv(): StreamedResponse
    {
        $wineryId = Auth::id();
        $containers = Container::where('user_id', $wineryId)
            ->where('archived', false)
            ->with(['containerType', 'containerRoom'])
            ->orderBy('name')
            ->get();

        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csv .= "Nombre,Tipo,Sala,Capacidad (kg),Uva/cosecha (kg),Vino (L),Ocupación %,Estado\n";

        foreach ($containers as $c) {
            $used = $c->used_capacity + $c->wine_volume_liters;
            $pct = $c->capacity > 0 ? round($used / $c->capacity * 100, 1) : 0;
            $state = $c->wine_volume_liters > 0 ? 'Con vino' : ($c->used_capacity > 0 ? 'Con uva' : 'Vacío');

            $csv .= implode(',', [
                '"'.$c->name.'"',
                '"'.($c->containerType?->name ?? '').'"',
                '"'.($c->containerRoom?->name ?? 'Sin sala').'"',
                $c->capacity,
                $c->used_capacity,
                $c->wine_volume_liters,
                $pct,
                $state,
            ])."\n";
        }

        return response()->streamDownload(
            fn () => print ($csv),
            'contenedores_'.now()->format('Y-m-d').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function render()
    {
        $wineryId = Auth::id();

        $base = Container::where('user_id', $wineryId)->where('archived', false);
        if ($this->roomFilter) {
            $base->where('container_room_id', $this->roomFilter);
        }
        if ($this->typeFilter) {
            $base->where('type_id', $this->typeFilter);
        }

        $all = (clone $base)->get(['id', 'capacity', 'used_capacity', 'wine_volume_liters', 'type_id', 'container_room_id']);

        // ── KPIs ─────────────────────────────────────────────────────────
        $total = $all->count();
        $withHarvest = $all->where('used_capacity', '>', 0)->count();
        $withWine = $all->where('wine_volume_liters', '>', 0)->count();
        $bothContent = $all->filter(fn ($c) => $c->used_capacity > 0 && $c->wine_volume_liters > 0)->count();
        $empty = $all->filter(fn ($c) => $c->used_capacity <= 0 && $c->wine_volume_liters <= 0)->count();

        $totalCapacity = $all->sum('capacity');
        $usedHarvest = $all->sum('used_capacity');
        $usedWine = $all->sum('wine_volume_liters');
        $globalPct = $totalCapacity > 0
            ? min(round(($usedHarvest + $usedWine) / $totalCapacity * 100, 1), 100)
            : 0;

        // ── Buckets de utilización ────────────────────────────────────────
        $buckets = ['0-20' => 0, '20-40' => 0, '40-60' => 0, '60-80' => 0, '80-100' => 0, '>100' => 0];
        foreach ($all as $c) {
            if ($c->capacity <= 0) {
                $buckets['0-20']++;

                continue;
            }
            $pct = ($c->used_capacity + $c->wine_volume_liters) / $c->capacity * 100;
            if ($pct > 100) {
                $buckets['>100']++;
            } elseif ($pct > 80) {
                $buckets['80-100']++;
            } elseif ($pct > 60) {
                $buckets['60-80']++;
            } elseif ($pct > 40) {
                $buckets['40-60']++;
            } elseif ($pct > 20) {
                $buckets['20-40']++;
            } else {
                $buckets['0-20']++;
            }
        }
        $bucketsMax = max(1, max($buckets));

        // ── Por tipo ─────────────────────────────────────────────────────
        $byType = DB::table('containers as c')
            ->leftJoin('container_types as ct', 'ct.id', '=', 'c.type_id')
            ->where('c.user_id', $wineryId)
            ->where('c.archived', false)
            ->when($this->typeFilter, fn ($q) => $q->where('c.type_id', $this->typeFilter))
            ->when($this->roomFilter, fn ($q) => $q->where('c.container_room_id', $this->roomFilter))
            ->groupBy('ct.id', 'ct.name')
            ->selectRaw('COALESCE(ct.name, "Sin tipo") as name, COUNT(*) as count,
                         SUM(c.capacity) as total_cap,
                         SUM(c.used_capacity) as harvest_used,
                         SUM(c.wine_volume_liters) as wine_used')
            ->orderByDesc('count')
            ->get();
        $typeMax = max(1, $byType->max('count'));

        // ── Por sala ─────────────────────────────────────────────────────
        $byRoom = DB::table('containers as c')
            ->leftJoin('container_rooms as cr', 'cr.id', '=', 'c.container_room_id')
            ->where('c.user_id', $wineryId)
            ->where('c.archived', false)
            ->when($this->typeFilter, fn ($q) => $q->where('c.type_id', $this->typeFilter))
            ->when($this->roomFilter, fn ($q) => $q->where('c.container_room_id', $this->roomFilter))
            ->groupBy('cr.id', 'cr.name')
            ->selectRaw('COALESCE(cr.name, "Sin sala") as room, COUNT(*) as count,
                         SUM(c.capacity) as total_cap,
                         SUM(c.used_capacity + c.wine_volume_liters) as used')
            ->orderByDesc('count')
            ->get();
        $roomMax = max(1, $byRoom->max('count'));

        // ── Top 5 más llenos ─────────────────────────────────────────────
        $topFilled = Container::where('user_id', $wineryId)
            ->where('archived', false)
            ->where('capacity', '>', 0)
            ->whereRaw('(used_capacity + wine_volume_liters) > 0')
            ->when($this->typeFilter, fn ($q) => $q->where('type_id', $this->typeFilter))
            ->when($this->roomFilter, fn ($q) => $q->where('container_room_id', $this->roomFilter))
            ->with(['containerType', 'containerRoom'])
            ->orderByRaw('(used_capacity + wine_volume_liters) / capacity DESC')
            ->take(5)
            ->get();

        // ── Top 5 más vacíos (con capacidad) ─────────────────────────────
        $topEmpty = Container::where('user_id', $wineryId)
            ->where('archived', false)
            ->where('capacity', '>', 0)
            ->when($this->typeFilter, fn ($q) => $q->where('type_id', $this->typeFilter))
            ->when($this->roomFilter, fn ($q) => $q->where('container_room_id', $this->roomFilter))
            ->with(['containerType', 'containerRoom'])
            ->orderByRaw('(used_capacity + wine_volume_liters) / capacity ASC')
            ->take(5)
            ->get();

        // Filtros disponibles
        $rooms = DB::table('container_rooms')->where('user_id', $wineryId)->orderBy('name')->get(['id', 'name']);
        $types = ContainerType::orderBy('name')->get(['id', 'name']);

        return view('livewire.winery.cellar.containers.analytics', compact(
            'total', 'withHarvest', 'withWine', 'bothContent', 'empty',
            'totalCapacity', 'usedHarvest', 'usedWine', 'globalPct',
            'buckets', 'bucketsMax',
            'byType', 'typeMax',
            'byRoom', 'roomMax',
            'topFilled', 'topEmpty',
            'rooms', 'types'
        ))->layout('layouts.app');
    }
}
