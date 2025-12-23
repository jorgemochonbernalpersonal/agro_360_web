<?php

namespace App\Livewire\Viticulturist\OfficialReports;

use Livewire\Component;
use App\Models\OfficialReport;
use Livewire\WithPagination;
use App\Livewire\Concerns\WithToastNotifications;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    // Modales
    public $showInvalidateModal = false;
    public $showShareModal = false;
    public $showPreviewModal = false;
    
    // Para invalidar
    public $reportToInvalidate = null;
    public $invalidatePassword = '';
    public $invalidateReason = '';
    
    // Para compartir
    public $reportToShare = null;
    public $shareEmail = '';
    public $shareMessage = '';
    
    // Para vista previa
    public $reportToPreview = null;
    
    // Filtros y búsqueda
    public $search = '';
    public $statusFilter = 'all'; // all, valid, invalid

    /**
     * Abrir modal para invalidar informe
     */
    public function openInvalidateModal($reportId)
    {
        $this->reportToInvalidate = OfficialReport::findOrFail($reportId);
        
        // Verificar permisos
        if ($this->reportToInvalidate->user_id !== auth()->id()) {
            $this->addError('invalidate', 'No tienes permiso para invalidar este informe.');
            return;
        }
        
        // Verificar si ya está invalidado
        if (!$this->reportToInvalidate->isValid()) {
            $this->addError('invalidate', 'Este informe ya está invalidado.');
            return;
        }
        
        // Verificar si se puede invalidar (límite de tiempo)
        if (!$this->reportToInvalidate->canBeInvalidated()) {
            $maxDays = config('reports.max_days_to_invalidate', 30);
            $daysSinceSigned = $this->reportToInvalidate->signed_at->diffInDays(now());
            $this->addError('invalidate', "Este informe no puede ser invalidado. Han pasado {$daysSinceSigned} días desde su firma. Solo se pueden invalidar informes con menos de {$maxDays} días.");
            return;
        }
        
        $this->showInvalidateModal = true;
    }

    /**
     * Invalidar informe
     */
    public function invalidateReport()
    {
        $this->validate([
            'invalidatePassword' => 'required|string',
            'invalidateReason' => 'required|string|min:10',
        ], [
            'invalidatePassword.required' => 'La contraseña es obligatoria.',
            'invalidateReason.required' => 'Debes especificar un motivo.',
            'invalidateReason.min' => 'El motivo debe tener al menos 10 caracteres.',
        ]);

        try {
            // Verificar contraseña
            if (!\Hash::check($this->invalidatePassword, auth()->user()->password)) {
                $this->addError('invalidatePassword', 'Contraseña incorrecta.');
                return;
            }

            // Invalidar informe
            $this->reportToInvalidate->invalidate($this->invalidateReason);

            $this->closeInvalidateModal();
            $this->toastSuccess('Informe invalidado correctamente.');
            
        } catch (\Exception $e) {
            $this->addError('invalidate', 'Error al invalidar: ' . $e->getMessage());
        }
    }

    /**
     * Cerrar modal de invalidar
     */
    public function closeInvalidateModal()
    {
        $this->showInvalidateModal = false;
        $this->reportToInvalidate = null;
        $this->invalidatePassword = '';
        $this->invalidateReason = '';
        $this->resetValidation(['invalidatePassword', 'invalidateReason']);
    }

    /**
     * Abrir modal para compartir informe
     */
    public function openShareModal($reportId)
    {
        $this->reportToShare = OfficialReport::findOrFail($reportId);
        
        // Verificar permisos
        if ($this->reportToShare->user_id !== auth()->id()) {
            $this->addError('share', 'No tienes permiso para compartir este informe.');
            return;
        }
        
        $this->showShareModal = true;
    }

    /**
     * Compartir informe por email
     */
    public function shareReport()
    {
        $this->validate([
            'shareEmail' => 'required|email',
            'shareMessage' => 'nullable|string|max:500',
        ], [
            'shareEmail.required' => 'El email es obligatorio.',
            'shareEmail.email' => 'Introduce un email válido.',
            'shareMessage.max' => 'El mensaje no puede superar 500 caracteres.',
        ]);

        try {
            // Enviar email
            \Mail::to($this->shareEmail)->send(
                new \App\Mail\OfficialReportShared(
                    $this->reportToShare,
                    $this->shareMessage ?? 'Te comparto este informe oficial.',
                    auth()->user()->name
                )
            );

            $this->closeShareModal();
            $this->toastSuccess('Informe compartido exitosamente a ' . $this->shareEmail);
            
        } catch (\Exception $e) {
            $this->addError('share', 'Error al enviar email: ' . $e->getMessage());
        }
    }

    /**
     * Cerrar modal de compartir
     */
    public function closeShareModal()
    {
        $this->showShareModal = false;
        $this->reportToShare = null;
        $this->shareEmail = '';
        $this->shareMessage = '';
        $this->resetValidation(['shareEmail', 'shareMessage']);
    }

    /**
     * Abrir modal de vista previa
     */
    public function openPreviewModal($reportId)
    {
        $this->reportToPreview = OfficialReport::findOrFail($reportId);
        
        // Verificar permisos
        if ($this->reportToPreview->user_id !== auth()->id()) {
            $this->addError('preview', 'No tienes permiso para ver este informe.');
            return;
        }
        
        $this->showPreviewModal = true;
    }

    /**
     * Cerrar modal de vista previa
     */
    public function closePreviewModal()
    {
        $this->showPreviewModal = false;
        $this->reportToPreview = null;
    }

    /**
     * Resetear filtros
     */
    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->resetPage();
    }

    public function render()
    {
        $query = OfficialReport::forUser(auth()->id())
            ->with('user');
        
        // Aplicar búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('verification_code', 'like', '%' . $this->search . '%')
                  ->orWhere('report_type', 'like', '%' . $this->search . '%');
            });
        }
        
        // Aplicar filtro de estado
        if ($this->statusFilter === 'valid') {
            $query->where('is_valid', true);
        } elseif ($this->statusFilter === 'invalid') {
            $query->where('is_valid', false);
        }
        
        $reports = $query->recent()->paginate(15);

        // Calcular estadísticas
        $baseQuery = OfficialReport::forUser(auth()->id());
        $totalCount = $baseQuery->count();
        $validCount = (clone $baseQuery)->where('is_valid', true)->count();
        $invalidCount = (clone $baseQuery)->where('is_valid', false)->count();
        $lastReport = $baseQuery->recent()->first();

        return view('livewire.viticulturist.official-reports.index', [
            'reports' => $reports,
            'totalCount' => $totalCount,
            'validCount' => $validCount,
            'invalidCount' => $invalidCount,
            'lastReportDate' => $lastReport ? $lastReport->created_at->format('d/m/Y') : null,
        ]);
    }
}
