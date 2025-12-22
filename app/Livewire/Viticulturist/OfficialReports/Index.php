<?php

namespace App\Livewire\Viticulturist\OfficialReports;

use Livewire\Component;
use App\Services\OfficialReportService;
use App\Models\OfficialReport;
use App\Models\Campaign;
use Carbon\Carbon;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Formulario de generación
    public $reportType = 'phytosanitary_treatments';
    public $startDate;
    public $endDate;
    public $campaignId = null;
    public $password = '';
    
    // Modales
    public $showSuccessModal = false;
    public $showSummaryModal = false;
    public $showInvalidateModal = false;
    public $showShareModal = false;
    public $showPreviewModal = false;
    
    // Para el informe generado
    public $generatedReport = null;
    
    // Resumen del informe
    public $reportSummary = [];
    
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
    
    // Campañas disponibles
    public $campaigns = [];

    protected $rules = [
        'reportType' => 'required|in:phytosanitary_treatments,full_digital_notebook',
        'startDate' => 'required_if:reportType,phytosanitary_treatments|date',
        'endDate' => 'required_if:reportType,phytosanitary_treatments|date|after_or_equal:startDate',
        'campaignId' => 'required_if:reportType,full_digital_notebook|exists:campaigns,id',
        'password' => 'required|string',
    ];

    protected $messages = [
        'reportType.required' => 'Selecciona el tipo de informe.',
        'startDate.required_if' => 'La fecha de inicio es obligatoria.',
        'endDate.required_if' => 'La fecha de fin es obligatoria.',
        'endDate.after_or_equal' => 'La fecha fin debe ser posterior o igual a la fecha inicio.',
        'campaignId.required_if' => 'Selecciona una campaña.',
        'password.required' => 'La contraseña de firma digital es obligatoria.',
    ];

    public function mount()
    {
        // Inicializar fechas (último mes)
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
        
        // Cargar campañas del usuario
        $this->campaigns = Campaign::where('viticulturist_id', auth()->id())
            ->orderBy('year', 'desc')
            ->get();
    }

    /**
     * Calcular resumen del informe antes de generar
     */
    public function calculateSummary()
    {
        // Validar campos antes de calcular
        if ($this->reportType === 'phytosanitary_treatments') {
            $this->validate([
                'startDate' => 'required|date',
                'endDate' => 'required|date|after_or_equal:startDate',
            ]);
        } else {
            $this->validate([
                'campaignId' => 'required|exists:campaigns,id',
            ]);
        }

        try {
            $user = auth()->user();
            
            if ($this->reportType === 'phytosanitary_treatments') {
                // Contar tratamientos en el periodo
                $treatments = \App\Models\PhytosanitaryTreatment::whereHas('activity', function($q) use ($user) {
                    $q->where('viticulturist_id', $user->id)
                      ->whereBetween('activity_date', [$this->startDate, $this->endDate]);
                })->with(['activity.plot', 'product'])->get();
                
                $totalTreatments = $treatments->count();
                
                // LÍMITE: Máximo 150 tratamientos
                if ($totalTreatments > 150) {
                    $this->addError('generation', "Demasiados tratamientos ($totalTreatments). El límite es 150. Reduce el periodo o contacta con soporte.");
                    return;
                }
                
                if ($totalTreatments === 0) {
                    $this->addError('generation', 'No hay tratamientos fitosanitarios en este periodo.');
                    return;
                }
                
                $plots = $treatments->pluck('activity.plot')->unique('id');
                $products = $treatments->pluck('product')->unique('id');
                $totalArea = $treatments->sum('area_treated');
                
                // Estimar tamaño (aproximado)
                $estimatedSizeKb = 150 + ($totalTreatments * 5); // Base 150KB + 5KB por tratamiento
                
                $this->reportSummary = [
                    'type' => 'phytosanitary_treatments',
                    'period' => \Carbon\Carbon::parse($this->startDate)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($this->endDate)->format('d/m/Y'),
                    'total_treatments' => $totalTreatments,
                    'plots_count' => $plots->count(),
                    'products_count' => $products->count(),
                    'total_area' => round($totalArea, 2),
                    'estimated_size' => $estimatedSizeKb > 1024 ? round($estimatedSizeKb / 1024, 1) . ' MB' : $estimatedSizeKb . ' KB',
                    'estimated_time' => $totalTreatments < 20 ? '5-10' : ($totalTreatments < 50 ? '10-15' : '15-30'),
                ];
                
            } else {
                // Cuaderno completo
                $campaign = \App\Models\Campaign::findOrFail($this->campaignId);
                $activities = \App\Models\AgriculturalActivity::where('viticulturist_id', $user->id)
                    ->where('campaign_id', $campaign->id)
                    ->get();
                
                $totalActivities = $activities->count();
                
                if ($totalActivities > 200) {
                    $this->addError('generation', "Demasiadas actividades ($totalActivities). El límite es 200. Contacta con soporte.");
                    return;
                }
                
                if ($totalActivities === 0) {
                    $this->addError('generation', 'No hay actividades registradas en esta campaña.');
                    return;
                }
                
                $estimatedSizeKb = 200 + ($totalActivities * 4);
                
                $this->reportSummary = [
                    'type' => 'full_digital_notebook',
                    'campaign' => $campaign->name . ' (' . $campaign->year . ')',
                    'total_activities' => $totalActivities,
                    'estimated_size' => $estimatedSizeKb > 1024 ? round($estimatedSizeKb / 1024, 1) . ' MB' : $estimatedSizeKb . ' KB',
                    'estimated_time' => $totalActivities < 30 ? '10-15' : ($totalActivities < 80 ? '15-25' : '25-40'),
                ];
            }
            
            // Mostrar modal de resumen
            $this->showSummaryModal = true;
            
        } catch (\Exception $e) {
            $this->addError('generation', 'Error al calcular resumen: ' . $e->getMessage());
        }
    }

    /**
     * Confirmar y generar informe (con validación de contraseña)
     */
    public function confirmAndGenerateReport()
    {
        // Validar contraseña
        $this->validate([
            'password' => 'required|string',
        ], [
            'password.required' => 'La contraseña de firma digital es obligatoria.',
        ]);

        // Cerrar modal de resumen y generar
        $this->showSummaryModal = false;
        $this->generateReport();
    }

    /**
     * Cerrar modal de resumen
     */
    public function closeSummaryModal()
    {
        $this->showSummaryModal = false;
        $this->reportSummary = [];
        $this->password = '';
        $this->resetValidation('password');
    }


    /**
     * Generar el informe oficial
     */
    public function generateReport()
    {
        // Validar solo campos del formulario (la contraseña ya se validó en confirmAndGenerateReport)
        $this->validate([
            'reportType' => 'required|in:phytosanitary_treatments,full_digital_notebook',
            'startDate' => 'required_if:reportType,phytosanitary_treatments|date',
            'endDate' => 'required_if:reportType,phytosanitary_treatments|date|after_or_equal:startDate',
            'campaignId' => 'required_if:reportType,full_digital_notebook|exists:campaigns,id',
            'password' => 'required|string',
        ], [
            'reportType.required' => 'Selecciona el tipo de informe.',
            'startDate.required_if' => 'La fecha de inicio es obligatoria.',
            'endDate.required_if' => 'La fecha de fin es obligatoria.',
            'endDate.after_or_equal' => 'La fecha fin debe ser posterior o igual a la fecha inicio.',
            'campaignId.required_if' => 'Selecciona una campaña.',
            'password.required' => 'La contraseña de firma digital es obligatoria.',
        ]);

        try {
            $service = new OfficialReportService();
            
            if ($this->reportType === 'phytosanitary_treatments') {
                // Informe de tratamientos fitosanitarios
                $this->generatedReport = $service->generatePhytosanitaryReport(
                    auth()->id(),
                    Carbon::parse($this->startDate),
                    Carbon::parse($this->endDate),
                    $this->password
                );
            } else {
                // Cuaderno digital completo
                $this->generatedReport = $service->generateFullNotebookReport(
                    auth()->id(),
                    $this->campaignId,
                    $this->password
                );
            }

            // Enviar email automático de notificación
            try {
                \Mail::to(auth()->user()->email)->send(new \App\Mail\OfficialReportGenerated($this->generatedReport));
            } catch (\Exception $emailError) {
                // Log error pero no fallar operación
                \Log::error('Error enviando email de informe generado: ' . $emailError->getMessage());
            }

            // Limpiar contraseña y abrir modal de éxito
            $this->password = '';
            $this->showSuccessModal = true;
            
            // Resetear paginación para mostrar el nuevo informe
            $this->resetPage();

        } catch (\Exception $e) {
            $this->addError('generation', $e->getMessage());
            // Mantener el modal de resumen abierto si hay error
            $this->showSummaryModal = true;
        }
    }

    /**
     * Descargar informe
     */
    public function downloadReport($reportId)
    {
        try {
            $report = OfficialReport::findOrFail($reportId);
            
            // Verificar permisos
            if ($report->user_id !== auth()->id()) {
                $this->addError('download', 'No tienes permiso para descargar este informe.');
                return;
            }

            $service = new OfficialReportService();
            return $service->downloadReport($report);
            
        } catch (\Exception $e) {
            $this->addError('download', 'Error al descargar el informe: ' . $e->getMessage());
        }
    }

    /**
     * Cerrar modal de éxito
     */
    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        $this->generatedReport = null;
    }

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
        
        $reports = $query->recent()->paginate(10);

        // Calcular estadísticas
        $baseQuery = OfficialReport::forUser(auth()->id());
        $totalCount = $baseQuery->count();
        $validCount = (clone $baseQuery)->where('is_valid', true)->count();
        $invalidCount = (clone $baseQuery)->where('is_valid', false)->count();
        $lastReport = $baseQuery->recent()->first();
        
        // Estadísticas de firmas
        $allReports = $baseQuery->get();
        $signatureStats = [
            'total_signed' => $allReports->count(),
            'total_valid' => $allReports->where('is_valid', true)->count(),
            'total_verifications' => $allReports->sum('verification_count'),
            'last_signed' => $allReports->sortByDesc('signed_at')->first(),
        ];
        
        // Actividad reciente (últimos 10)
        $recentSignatures = OfficialReport::forUser(auth()->id())
            ->whereNotNull('signed_at')
            ->orderBy('signed_at', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.viticulturist.official-reports.index', [
            'reports' => $reports,
            'totalCount' => $totalCount,
            'validCount' => $validCount,
            'invalidCount' => $invalidCount,
            'lastReportDate' => $lastReport ? $lastReport->created_at->format('d/m/Y') : null,
            'signatureStats' => $signatureStats,
            'recentSignatures' => $recentSignatures,
        ]);
    }
}
