<?php

namespace App\Livewire\Viticulturist\OfficialReports;

use App\Livewire\Concerns\WithOfficialReportBatch;
use App\Livewire\Concerns\WithOfficialReportSummary;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Models\Campaign;
use App\Models\DigitalSignature;
use App\Models\OfficialReport;
use App\Services\OfficialReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use RuntimeException;

class Create extends Component
{
    use WithOfficialReportBatch, WithOfficialReportSummary,
        WithRoleAwareRedirect, WithToastNotifications, WithViticulturistValidation;

    public string $reportType = 'phytosanitary_treatments';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public $campaignId = null;

    public string $password = '';

    public int $recordCount = 0;

    public int $activitiesCount = 0;

    public bool $showSuccessModal = false;

    public $generatedReport = null;

    public bool $hasDigitalSignature = false;

    public $campaigns = [];

    protected array $messages = [
        'reportType.required' => 'Selecciona el tipo de informe.',
        'startDate.required_if' => 'La fecha de inicio es obligatoria.',
        'endDate.required_if' => 'La fecha de fin es obligatoria.',
        'endDate.after_or_equal' => 'La fecha fin debe ser posterior o igual a la fecha inicio.',
        'campaignId.required_if' => 'Selecciona una campaña.',
        'password.required' => 'La contraseña de firma digital es obligatoria.',
    ];

    protected $listeners = ['signature-updated' => 'refreshSignatureStatus'];

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();

        $this->campaigns = Campaign::where('viticulturist_id', auth()->id())
            ->orderBy('year', 'desc')
            ->get();

        $this->hasDigitalSignature = DigitalSignature::forUser(auth()->id()) !== null;
        $this->updateRecordCount();
    }

    public function refreshSignatureStatus(): void
    {
        $this->hasDigitalSignature = DigitalSignature::forUser(auth()->id()) !== null;
    }

    public function updatedReportType(): void
    {
        $this->updateRecordCount();
    }

    public function updatedStartDate(): void
    {
        $this->updateRecordCount();
    }

    public function updatedEndDate(): void
    {
        $this->updateRecordCount();
    }

    public function updatedCampaignId(): void
    {
        $this->updateRecordCount();
    }

    public function updateRecordCount(): void
    {
        try {
            if ($this->reportType === 'phytosanitary_treatments') {
                if ($this->startDate && $this->endDate) {
                    $this->recordCount = \App\Models\AgriculturalActivity::ofType('phytosanitary')
                        ->forUser(auth()->id())
                        ->whereBetween('activity_date', [$this->startDate, $this->endDate])
                        ->count();
                }
            } elseif ($this->campaignId) {
                $this->activitiesCount = \App\Models\AgriculturalActivity::forUser(auth()->id())
                    ->forCampaign($this->campaignId)
                    ->count();
            }
        } catch (\Exception) {
            $this->recordCount = 0;
            $this->activitiesCount = 0;
        }
    }

    public function setQuickPeriod(string $period): void
    {
        $this->startDate = match ($period) {
            'last_week' => now()->subWeek()->toDateString(),
            'last_month' => now()->startOfMonth()->subMonth()->toDateString(),
            'this_month' => now()->startOfMonth()->toDateString(),
            'last_quarter' => now()->subMonths(3)->toDateString(),
            'this_year' => now()->startOfYear()->toDateString(),
            default => $this->startDate,
        };

        $this->endDate = match ($period) {
            'last_month' => now()->startOfMonth()->subDay()->toDateString(),
            default => now()->toDateString(),
        };

        $this->updateRecordCount();
    }

    public function confirmAndGenerateReport(): void
    {
        if (! $this->checkSignaturePassword()) {
            return;
        }

        $this->showSummaryModal = false;

        try {
            if ($this->reportType === 'phytosanitary_treatments') {
                $periodStart = $this->startDate;
                $periodEnd = $this->endDate;
            } else {
                $campaign = Campaign::find($this->campaignId);
                $periodStart = $campaign->start_date ?? now()->startOfYear();
                $periodEnd = $campaign->end_date ?? now();
            }

            $report = OfficialReport::create([
                'user_id' => auth()->id(),
                'report_type' => $this->reportType,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'report_metadata' => $this->reportType === 'full_digital_notebook'
                    ? ['campaign_id' => $this->campaignId, 'campaign_name' => Campaign::find($this->campaignId)?->name]
                    : [],
                'verification_code' => OfficialReport::generateVerificationCode(),
                'is_valid' => true,
                'processing_status' => 'pending',
                'signature_hash' => 'TEMP_'.uniqid().'_'.time(),
                'signed_at' => now(),
                'signed_ip' => request()->ip(),
            ]);

            \App\Jobs\GenerateOfficialReportJob::dispatch(
                $report->id,
                auth()->id(),
                $this->reportType,
                ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'campaign_id' => $this->campaignId],
                $this->password
            );

            $this->password = '';
            $this->toastSuccess(__('✅ Informe en proceso. Te avisaremos por email cuando esté listo (1-5 min).'));
            $this->viticulturistRoleRedirect('official-reports.index');

        } catch (\Exception $e) {
            Log::error('Error al crear informe para cola', [
                'user_id' => auth()->id(),
                'report_type' => $this->reportType,
                'error' => $e->getMessage(),
            ]);
            $this->showSummaryModal = true;
            $this->addError('generation', $e->getMessage());
            $this->toastError($e instanceof RuntimeException ? $e->getMessage() : __('Error al generar el informe. Inténtalo de nuevo.'));
        }
    }

    public function generateReport(): void
    {
        $rules = ['reportType' => 'required|in:phytosanitary_treatments,full_digital_notebook', 'password' => 'required|string'];

        if ($this->reportType === 'phytosanitary_treatments') {
            $rules['startDate'] = 'required|date';
            $rules['endDate'] = 'required|date|after_or_equal:startDate';
        } else {
            $rules['campaignId'] = $this->campaignOwnershipRule();
        }

        $this->validate($rules, [
            'reportType.required' => __('Selecciona el tipo de informe.'),
            'startDate.required' => __('La fecha de inicio es obligatoria.'),
            'endDate.required' => __('La fecha de fin es obligatoria.'),
            'endDate.after_or_equal' => __('La fecha fin debe ser posterior o igual a la fecha inicio.'),
            'campaignId.required' => __('Selecciona una campaña.'),
            'password.required' => __('La contraseña de firma digital es obligatoria.'),
        ]);

        try {
            $service = app(OfficialReportService::class);

            $this->generatedReport = $this->reportType === 'phytosanitary_treatments'
                ? $service->generatePhytosanitaryReport(auth()->id(), Carbon::parse($this->startDate), Carbon::parse($this->endDate), $this->password)
                : $service->generateFullNotebookReport(auth()->id(), $this->campaignId, $this->password);

            if (! $this->generatedReport->id) {
                throw new \Exception(__('El informe se generó pero no se pudo recuperar correctamente.'));
            }

            try {
                Mail::to(auth()->user()->email)->send(new \App\Mail\OfficialReportGenerated($this->generatedReport));
            } catch (\Exception $emailError) {
                Log::error('Error enviando email de informe generado: '.$emailError->getMessage());
            }

            $this->password = '';
            $this->showSuccessModal = true;
            $this->toastSuccess(__('Informe generado y firmado correctamente.'));

        } catch (\Exception $e) {
            Log::error('Error al generar informe oficial', [
                'user_id' => auth()->id(),
                'report_type' => $this->reportType,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $this->addError('generation', $e->getMessage());
            $this->toastError($e instanceof RuntimeException ? $e->getMessage() : __('Error al generar el informe. Inténtalo de nuevo.'));
            $this->showSummaryModal = true;
        }
    }

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->viticulturistRoleRedirect('official-reports.index');
    }

    public function render()
    {
        return view('livewire.viticulturist.official-reports.create');
    }

    protected function rules(): array
    {
        return [
            'reportType' => 'required|in:phytosanitary_treatments,full_digital_notebook',
            'startDate' => 'required_if:reportType,phytosanitary_treatments|date',
            'endDate' => 'required_if:reportType,phytosanitary_treatments|date|after_or_equal:startDate',
            'campaignId' => ['required_if:reportType,full_digital_notebook', ...$this->campaignOwnershipRule(false)],
            'password' => 'required|string',
        ];
    }

    protected function checkSignaturePassword(): bool
    {
        $this->hasDigitalSignature = DigitalSignature::forUser(auth()->id()) !== null;

        if (! $this->hasDigitalSignature) {
            $this->addError('generation', __('No tienes una contraseña de firma digital configurada. Por favor, créala en Configuración → Firma Digital.'));
            $this->toastError(__('Debes configurar tu contraseña de firma digital primero.'));

            return false;
        }

        $this->validate(
            ['password' => 'required|string'],
            ['password.required' => __('La contraseña de firma digital es obligatoria.')]
        );

        $digitalSignature = DigitalSignature::forUser(auth()->id());
        if (! $digitalSignature) {
            Log::error('No se encontró firma digital para el usuario', ['user_id' => auth()->id()]);
            $this->addError('generation', __('Error al verificar la contraseña. Por favor, intenta de nuevo.'));
            $this->toastError(__('Error al verificar la contraseña.'));

            return false;
        }

        if (! $digitalSignature->verifyPassword($this->password)) {
            $this->addError('password', __('Contraseña de firma digital incorrecta. Recuerda: es la contraseña que configuraste en Configuración → Firma Digital, NO tu contraseña de login.'));
            $this->toastError(__('Contraseña de firma incorrecta. No uses tu contraseña de login, usa la de Configuración → Firma Digital.'));

            return false;
        }

        return true;
    }
}
