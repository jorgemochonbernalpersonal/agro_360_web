<?php

namespace App\Livewire\Viticulturist\Containers;

use App\Models\Container;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    public Container $container;
    
    public function mount($id)
    {
        $this->container = Container::where('user_id', Auth::id())
            ->with(['harvests.plotPlanting.grapeVariety', 'histories'])
            ->findOrFail($id);
    }

    public function getOccupancyColorClass()
    {
        $percentage = $this->container->getOccupancyPercentage();
        
        return match(true) {
            $percentage >= 90 => 'bg-red-500',
            $percentage >= 70 => 'bg-orange-500',
            $percentage >= 50 => 'bg-yellow-500',
            default => 'bg-green-500'
        };
    }

    public function getStatusBadgeClass()
    {
        if ($this->container->archived) {
            return 'bg-gray-500 text-white';
        }
        
        if ($this->container->isFull()) {
            return 'bg-red-500 text-white';
        }
        
        if ($this->container->isEmpty()) {
            return 'bg-blue-500 text-white';
        }
        
        return 'bg-green-500 text-white';
    }

    public function getStatusText()
    {
        if ($this->container->archived) {
            return 'Archivado';
        }
        
        if ($this->container->isFull()) {
            return 'Lleno';
        }
        
        if ($this->container->isEmpty()) {
            return 'Vacío';
        }
        
        return 'Disponible';
    }

    public function getMaintenanceWarning()
    {
        if (!$this->container->next_maintenance_date) {
            return null;
        }
        
        $daysUntil = now()->diffInDays($this->container->next_maintenance_date, false);
        
        if ($daysUntil < 0) {
            return [
                'type' => 'danger',
                'message' => 'Mantenimiento vencido hace ' . abs($daysUntil) . ' días',
            ];
        }
        
        if ($daysUntil <= 7) {
            return [
                'type' => 'warning',
                'message' => 'Mantenimiento en ' . $daysUntil . ' días',
            ];
        }
        
        if ($daysUntil <= 30) {
            return [
                'type' => 'info',
                'message' => 'Mantenimiento en ' . $daysUntil . ' días',
            ];
        }
        
        return null;
    }

    public function render()
    {
        return view('livewire.viticulturist.containers.show', [
            'occupancyColor' => $this->getOccupancyColorClass(),
            'statusBadge' => $this->getStatusBadgeClass(),
            'statusText' => $this->getStatusText(),
            'maintenanceWarning' => $this->getMaintenanceWarning(),
        ])->layout('layouts.app');
    }
}
