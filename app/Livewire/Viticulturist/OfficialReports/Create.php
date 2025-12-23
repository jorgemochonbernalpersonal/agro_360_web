<?php

namespace App\Livewire\Viticulturist\OfficialReports;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Campaign;
use App\Models\OfficialReport;
use App\Services\OfficialReportService;
use Carbon\Carbon;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    // Formulario de generación
    public $reportType = 'phytosanitary_treatments';
    public $startDate;
    public $endDate;
    public $campaignId = null;
    public $password = '';
    // Contador de registros en tiempo real
    public $recordCount = 0;
    public $activitiesCount = 0;
    // Modales
    public $showSummaryModal = false;
    public $showSuccessModal = false;
    // Para el informe generado
    public $generatedReport = null;
    // Resumen del informe
    public $reportSummary = [];
    // Verificar si tiene contraseña de firma configurada
    public $hasDigitalSignature = false;
    // Campañas disponibles
    public $campaigns = [];
    // Generación por lotes
    public $showBatchOption = false;
    public $batchPeriods = [];
    public $totalBatches = 0;

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

    // Listeners para sincronización con otros componentes
    protected $listeners = ['signature-updated' => 'refreshSignatureStatus'];

    public function mount()
    {
        // Inicializar fechas (último mes)
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();

        // Cargar campañas del usuario
        $this->campaigns = Campaign::where('viticulturist_id', auth()->id())
            ->orderBy('year', 'desc')
            ->get();

        // Verificar si tiene contraseña de firma configurada
        $this->hasDigitalSignature = \App\Models\DigitalSignature::forUser(auth()->id()) !== null;

        // Calcular contador inicial
        $this->updateRecordCount();
    }

    /**
     * Refrescar estado de firma digital (llamado por evento)
     */
    public function refreshSignatureStatus()
    {
        $this->hasDigitalSignature = \App\Models\DigitalSignature::forUser(auth()->id()) !== null;
    }

    /**
     * Cuando cambia el tipo de informe
     */
    public function updatedReportType()
    {
        $this->updateRecordCount();
    }

    /**
     * Cuando cambia la fecha de inicio
     */
    public function updatedStartDate()
    {
        $this->updateRecordCount();
    }

    /**
     * Cuando cambia la fecha de fin
     */
    public function updatedEndDate()
    {
        $this->updateRecordCount();
    }

    /**
     * Cuando cambia la campaña
     */
    public function updatedCampaignId()
    {
        $this->updateRecordCount();
    }

    /**
     * Actualizar contador de registros
     */
    public function updateRecordCount()
    {
        try {
            if ($this->reportType === 'phytosanitary_treatments') {
                if ($this->startDate && $this->endDate) {
                    $this->recordCount = \App\Models\AgriculturalActivity::ofType('phytosanitary')
                        ->forUser(auth()->id())
                        ->whereBetween('activity_date', [$this->startDate, $this->endDate])
                        ->count();
                }
            } else {
                if ($this->campaignId) {
                    $this->activitiesCount = \App\Models\AgriculturalActivity::forUser(auth()->id())
                        ->forCampaign($this->campaignId)
                        ->count();
                }
            }
        } catch (\Exception $e) {
            // Silenciar errores de conteo
            $this->recordCount = 0;
            $this->activitiesCount = 0;
        }
    }

    /**
     * Establecer periodo rápido
     */
    public function setQuickPeriod($period)
    {
        switch ($period) {
            case 'last_week':
                $this->startDate = now()->subWeek()->toDateString();
                $this->endDate = now()->toDateString();
                break;
            case 'last_month':
                $this->startDate = now()->startOfMonth()->subMonth()->toDateString();
                $this->endDate = now()->startOfMonth()->subDay()->toDateString();
                break;
            case 'this_month':
                $this->startDate = now()->startOfMonth()->toDateString();
                $this->endDate = now()->toDateString();
                break;
            case 'last_quarter':
                $this->startDate = now()->subMonths(3)->toDateString();
                $this->endDate = now()->toDateString();
                break;
            case 'this_year':
                $this->startDate = now()->startOfYear()->toDateString();
                $this->endDate = now()->toDateString();
                break;
        }

        $this->updateRecordCount();
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
                $treatments = \App\Models\PhytosanitaryTreatment::whereHas('activity', function ($q) use ($user) {
                    $q
                        ->where('viticulturist_id', $user->id)
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
                $estimatedSizeKb = 150 + ($totalTreatments * 5);  // Base 150KB + 5KB por tratamiento

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

                if ($totalActivities === 0) {
                    $this->addError('generation', 'No hay actividades registradas en esta campaña.');
                    return;
                }

                // Si hay más de 200 actividades, calcular períodos para generación por lotes
                if ($totalActivities > 200) {
                    $this->batchPeriods = $this->calculatePeriods($campaign, $totalActivities);
                    $this->totalBatches = count($this->batchPeriods);
                    $this->showBatchOption = true;

                    // Calcular resumen para mostrar información
                    $estimatedSizeKb = 200 + ($totalActivities * 4);
                    $this->reportSummary = [
                        'type' => 'full_digital_notebook',
                        'campaign' => $campaign->name . ' (' . $campaign->year . ')',
                        'total_activities' => $totalActivities,
                        'estimated_size' => $estimatedSizeKb > 1024 ? round($estimatedSizeKb / 1024, 1) . ' MB' : $estimatedSizeKb . ' KB',
                        'estimated_time' => '5-10 min por lote',
                        'batch_mode' => true,
                    ];
                } else {
                    $this->showBatchOption = false;
                    $estimatedSizeKb = 200 + ($totalActivities * 4);

                    $this->reportSummary = [
                        'type' => 'full_digital_notebook',
                        'campaign' => $campaign->name . ' (' . $campaign->year . ')',
                        'total_activities' => $totalActivities,
                        'estimated_size' => $estimatedSizeKb > 1024 ? round($estimatedSizeKb / 1024, 1) . ' MB' : $estimatedSizeKb . ' KB',
                        'estimated_time' => $totalActivities < 30 ? '10-15' : ($totalActivities < 80 ? '15-25' : '25-40'),
                    ];
                }
            }

            // Verificar si tiene contraseña configurada antes de mostrar el modal
            $this->hasDigitalSignature = \App\Models\DigitalSignature::forUser(auth()->id()) !== null;

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
        // Verificar que tenga contraseña configurada
        $this->hasDigitalSignature = \App\Models\DigitalSignature::forUser(auth()->id()) !== null;

        if (!$this->hasDigitalSignature) {
            $this->addError('generation', 'No tienes una contraseña de firma digital configurada. Por favor, créala en Configuración → Firma Digital.');
            $this->toastError('Debes configurar tu contraseña de firma digital primero.');
            return;
        }

        // Validar contraseña
        $this->validate([
            'password' => 'required|string',
        ], [
            'password.required' => 'La contraseña de firma digital es obligatoria.',
        ]);

        // Verificar que la contraseña sea correcta ANTES de generar el informe
        $digitalSignature = \App\Models\DigitalSignature::forUser(auth()->id());
        if (!$digitalSignature) {
            \Log::error('No se encontró firma digital para el usuario', ['user_id' => auth()->id()]);
            $this->addError('generation', 'Error al verificar la contraseña. Por favor, intenta de nuevo.');
            $this->toastError('Error al verificar la contraseña.');
            return;
        }

        if (!$digitalSignature->verifyPassword($this->password)) {
            $this->addError('password', 'Contraseña de firma digital incorrecta. Recuerda: es la contraseña que configuraste en Configuración → Firma Digital, NO tu contraseña de login. ¿La olvidaste?');
            $this->toastError('Contraseña de firma incorrecta. No uses tu contraseña de login, usa la de Configuración → Firma Digital.');
            return;
        }

        // Cerrar modal de resumen y generar
        $this->showSummaryModal = false;

        try {
            // Obtener periodo según tipo de informe
            if ($this->reportType === 'phytosanitary_treatments') {
                $periodStart = $this->startDate;
                $periodEnd = $this->endDate;
            } else {
                // Para cuaderno digital completo, usar fechas de la campaña
                $campaign = \App\Models\Campaign::find($this->campaignId);
                $periodStart = $campaign->start_date ?? now()->startOfYear();
                $periodEnd = $campaign->end_date ?? now();
            }

            // Crear registro de informe con estado pending
            $report = \App\Models\OfficialReport::create([
                'user_id' => auth()->id(),
                'report_type' => $this->reportType,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'report_metadata' => $this->reportType === 'full_digital_notebook'
                    ? [
                        'campaign_id' => $this->campaignId,
                        'campaign_name' => \App\Models\Campaign::find($this->campaignId)?->name
                    ]
                    : [],
                'verification_code' => \App\Models\OfficialReport::generateVerificationCode(),
                'is_valid' => true,
                'processing_status' => 'pending',
                'signature_hash' => 'TEMP_' . uniqid() . '_' . time(),  // Hash temporal único
                'signed_at' => now(),
                'signed_ip' => request()->ip(),
            ]);

            // Despachar job a la cola
            \App\Jobs\GenerateOfficialReportJob::dispatch(
                $report->id,
                auth()->id(),
                $this->reportType,
                [
                    'start_date' => $this->startDate,
                    'end_date' => $this->endDate,
                    'campaign_id' => $this->campaignId,
                ],
                $this->password
            );

            // Limpiar y notificar
            $this->password = '';
            $this->toastSuccess('✅ Informe en proceso. Te avisaremos por email cuando esté listo (1-5 min).');

            // Redirigir
            return redirect()->route('viticulturist.official-reports.index');
        } catch (\Exception $e) {
            \Log::error('Error al crear informe para cola', [
                'user_id' => auth()->id(),
                'report_type' => $this->reportType,
                'error' => $e->getMessage(),
            ]);
            // Re-abrir el modal de resumen para mostrar el error
            $this->showSummaryModal = true;
            $this->addError('generation', $e->getMessage());
            $this->toastError($e->getMessage());
        }
    }

    /**
     * Cerrar modal de resumen
     */
    public function closeSummaryModal()
    {
        $this->showSummaryModal = false;
        $this->reportSummary = [];
        $this->password = '';
        $this->showBatchOption = false;
        $this->batchPeriods = [];
        $this->totalBatches = 0;
        $this->resetValidation('password');

        // Verificar si ahora tiene contraseña configurada (por si fue a configurarla)
        $this->hasDigitalSignature = \App\Models\DigitalSignature::forUser(auth()->id()) !== null;
    }

    /**
     * Calcular períodos para generación por lotes
     */
    private function calculatePeriods($campaign, $totalActivities)
    {
        $start = Carbon::parse($campaign->start_date);
        $end = Carbon::parse($campaign->end_date);
        $daysDiff = $start->diffInDays($end);

        // Si la campaña es menor a 6 meses, dividir por meses
        // Si es mayor, dividir por trimestres
        if ($daysDiff < 180) {
            return $this->splitByMonths($start, $end, $campaign->id);
        } else {
            return $this->splitByQuarters($start, $end, $campaign->id);
        }
    }

    /**
     * Dividir campaña por meses
     */
    private function splitByMonths($start, $end, $campaignId)
    {
        $periods = [];
        $current = $start->copy();

        while ($current->lt($end) || $current->isSameDay($end)) {
            $periodStart = $current->copy();
            $periodEnd = $current->copy()->endOfMonth();
            if ($periodEnd->gt($end)) {
                $periodEnd = $end->copy();
            }

            // Contar actividades en este período
            $count = \App\Models\AgriculturalActivity::forUser(auth()->id())
                ->forCampaign($campaignId)
                ->whereBetween('activity_date', [$periodStart->format('Y-m-d'), $periodEnd->format('Y-m-d')])
                ->count();

            if ($count > 0) {
                $periods[] = [
                    'label' => $periodStart->format('M Y'),
                    'start' => $periodStart->format('Y-m-d'),
                    'end' => $periodEnd->format('Y-m-d'),
                    'count' => $count,
                ];
            }

            $current->addMonth()->startOfMonth();
            if ($current->gt($end)) {
                break;
            }
        }

        return $periods;
    }

    /**
     * Dividir campaña por trimestres
     */
    private function splitByQuarters($start, $end, $campaignId)
    {
        $periods = [];
        $current = $start->copy();

        while ($current->lt($end) || $current->isSameDay($end)) {
            $periodStart = $current->copy();
            $periodEnd = $current->copy()->addMonths(3)->subDay();
            if ($periodEnd->gt($end)) {
                $periodEnd = $end->copy();
            }

            $count = \App\Models\AgriculturalActivity::forUser(auth()->id())
                ->forCampaign($campaignId)
                ->whereBetween('activity_date', [$periodStart->format('Y-m-d'), $periodEnd->format('Y-m-d')])
                ->count();

            if ($count > 0) {
                $quarter = ceil($periodStart->month / 3);
                $periods[] = [
                    'label' => "Q{$quarter} {$periodStart->year}",
                    'start' => $periodStart->format('Y-m-d'),
                    'end' => $periodEnd->format('Y-m-d'),
                    'count' => $count,
                ];
            }

            $current->addMonths(3)->startOfMonth();
            if ($current->gt($end)) {
                break;
            }
        }

        return $periods;
    }

    /**
     * Generar informes por lotes
     */
    public function generateBatchReports()
    {
        // Verificar que tenga contraseña configurada
        $this->hasDigitalSignature = \App\Models\DigitalSignature::forUser(auth()->id()) !== null;

        if (!$this->hasDigitalSignature) {
            $this->addError('generation', 'No tienes una contraseña de firma digital configurada. Por favor, créala en Configuración → Firma Digital.');
            $this->toastError('Debes configurar tu contraseña de firma digital primero.');
            return;
        }

        // Validar contraseña
        $this->validate([
            'password' => 'required|string',
        ], [
            'password.required' => 'La contraseña de firma digital es obligatoria.',
        ]);

        // Verificar que la contraseña sea correcta
        $digitalSignature = \App\Models\DigitalSignature::forUser(auth()->id());
        if (!$digitalSignature) {
            \Log::error('No se encontró firma digital para el usuario', ['user_id' => auth()->id()]);
            $this->addError('generation', 'Error al verificar la contraseña. Por favor, intenta de nuevo.');
            $this->toastError('Error al verificar la contraseña.');
            return;
        }

        if (!$digitalSignature->verifyPassword($this->password)) {
            $this->addError('password', 'Contraseña de firma digital incorrecta.');
            $this->toastError('Contraseña de firma incorrecta.');
            return;
        }

        $campaign = \App\Models\Campaign::findOrFail($this->campaignId);
        $generatedCount = 0;
        $errors = [];

        foreach ($this->batchPeriods as $index => $period) {
            try {
                // Crear informe para este período
                $report = \App\Models\OfficialReport::create([
                    'user_id' => auth()->id(),
                    'report_type' => 'full_digital_notebook',
                    'period_start' => $period['start'],
                    'period_end' => $period['end'],
                    'report_metadata' => [
                        'campaign_id' => $this->campaignId,
                        'campaign_name' => $campaign->name,
                        'batch_index' => $index + 1,
                        'total_batches' => count($this->batchPeriods),
                        'period_label' => $period['label'],
                    ],
                    'verification_code' => \App\Models\OfficialReport::generateVerificationCode(),
                    'processing_status' => 'pending',
                    'signature_hash' => 'TEMP_' . uniqid() . '_' . time(),
                    'signed_at' => now(),
                    'signed_ip' => request()->ip(),
                ]);

                // Despachar job con filtro de fechas
                \App\Jobs\GenerateOfficialReportJob::dispatch(
                    $report->id,
                    auth()->id(),
                    'full_digital_notebook',
                    [
                        'campaign_id' => $this->campaignId,
                        'start_date' => $period['start'],  // Filtro adicional
                        'end_date' => $period['end'],  // Filtro adicional
                    ],
                    $this->password
                );

                $generatedCount++;
            } catch (\Exception $e) {
                \Log::error('Error generando informe por lotes', [
                    'period' => $period['label'],
                    'error' => $e->getMessage(),
                ]);
                $errors[] = "Error en {$period['label']}: " . $e->getMessage();
            }
        }

        if ($generatedCount > 0) {
            $this->password = '';
            $this->toastSuccess("✅ Se generarán {$generatedCount} informes en lotes. Te avisaremos por email cuando estén listos (5-10 min por lote).");
            return redirect()->route('viticulturist.official-reports.index');
        } else {
            $this->addError('generation', 'Error al generar informes: ' . implode(', ', $errors));
            $this->toastError('Error al generar informes por lotes.');
        }
    }

    /**
     * Forzar generación de un solo informe (aunque tenga más de 200 actividades)
     */
    public function forceGenerateSingle()
    {
        $this->showBatchOption = false;
        // Continuar con el flujo normal de confirmAndGenerateReport
    }

    /**
     * Generar el informe oficial
     */
    public function generateReport()
    {
        // Construir reglas de validación dinámicamente según el tipo de informe
        $rules = [
            'reportType' => 'required|in:phytosanitary_treatments,full_digital_notebook',
            'password' => 'required|string',
        ];

        if ($this->reportType === 'phytosanitary_treatments') {
            $rules['startDate'] = 'required|date';
            $rules['endDate'] = 'required|date|after_or_equal:startDate';
        } else {
            $rules['campaignId'] = 'required|exists:campaigns,id';
        }

        $messages = [
            'reportType.required' => 'Selecciona el tipo de informe.',
            'startDate.required' => 'La fecha de inicio es obligatoria.',
            'endDate.required' => 'La fecha de fin es obligatoria.',
            'endDate.after_or_equal' => 'La fecha fin debe ser posterior o igual a la fecha inicio.',
            'campaignId.required' => 'Selecciona una campaña.',
            'campaignId.exists' => 'La campaña seleccionada no existe.',
            'password.required' => 'La contraseña de firma digital es obligatoria.',
        ];

        $this->validate($rules, $messages);

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

            // Verificar que el informe se creó correctamente
            if (!$this->generatedReport || !$this->generatedReport->id) {
                throw new \Exception('El informe se generó pero no se pudo recuperar correctamente.');
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
            $this->toastSuccess('Informe generado y firmado correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error al generar informe oficial', [
                'user_id' => auth()->id(),
                'report_type' => $this->reportType,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->addError('generation', $e->getMessage());
            $this->toastError($e->getMessage());
            $this->showSummaryModal = true;
        }
    }

    /**
     * Cerrar modal de éxito
     */
    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;

        // Redirigir a la lista de informes
        return redirect()->route('viticulturist.official-reports.index');
    }

    public function render()
    {
        return view('livewire.viticulturist.official-reports.create');
    }
}
