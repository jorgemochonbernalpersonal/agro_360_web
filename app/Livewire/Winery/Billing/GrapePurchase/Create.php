<?php

namespace App\Livewire\Winery\Billing\GrapePurchase;

use App\Livewire\Concerns\WithGrapePurchaseFormRules;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Harvest;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\GrapePurchaseInvoiceIssuedNotification;
use App\Services\GrapePurchaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Create extends Component
{
    use WithGrapePurchaseFormRules, WithRoleAwareRedirect, WithToastNotifications;

    public string $viticulturist_id = '';

    public string $invoice_date = '';

    public string $observations = '';

    public string $payment_type = '';

    // Selected harvest rows
    public array $lines = [];

    public function mount(): void
    {
        $this->invoice_date = now()->toDateString();
    }

    public function updatedViticulturistId(): void
    {
        $this->lines = [];
    }

    public function toggleHarvest(int $harvestId): void
    {
        $existing = array_search($harvestId, array_column($this->lines, 'harvest_id'));

        if ($existing !== false) {
            array_splice($this->lines, $existing, 1);
            $this->lines = array_values($this->lines);

            return;
        }

        $harvest = Harvest::where('winery_id', Auth::id())->find($harvestId);
        if (! $harvest) {
            return;
        }

        $this->lines[] = [
            'harvest_id' => $harvestId,
            'quantity' => (string) ($harvest->total_weight ?? 0),
            'unit_price' => '',
            'tax_rate' => '0',
            'description' => '',
        ];
    }

    public function removeLine(int $index): void
    {
        array_splice($this->lines, $index, 1);
        $this->lines = array_values($this->lines);
    }

    public function save()
    {
        $this->validate();

        // Guard: viticulturist must belong to this winery (bypass for producer using own plots)
        $isSelfPurchase = Auth::user()->isProducer() && (int) $this->viticulturist_id === Auth::id();
        $belongs = $isSelfPurchase || WineryViticulturist::where('winery_id', Auth::id())
            ->where('viticulturist_id', $this->viticulturist_id)
            ->where('source', 'own')
            ->exists();

        if (! $belongs) {
            $this->addError('viticulturist_id', __('El viticultor no pertenece a tu bodega.'));

            return;
        }

        $viticulturist = User::findOrFail($this->viticulturist_id);

        try {
            $invoice = app(GrapePurchaseService::class)->createInvoice(
                Auth::id(),
                $viticulturist,
                [
                    'invoice_date' => $this->invoice_date,
                    'payment_type' => $this->payment_type ?: null,
                    'observations' => $this->observations ?: null,
                ],
                $this->lines,
            );

            $invoice->load('user');
            $viticulturist->notify(new GrapePurchaseInvoiceIssuedNotification($invoice));

            $this->toastSuccess("Liquidación {$invoice->invoice_number} creada — Ref.: {$invoice->delivery_note_code}");

            return $this->roleRedirect('invoices.grape-purchase.index');

        } catch (\Exception $e) {
            Log::error('Error al crear liquidación de vendimia: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al crear la liquidación. Inténtalo de nuevo.'));
        }
    }

    public function render()
    {
        $wineryId = Auth::id();

        $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)
            ->where('source', 'own')
            ->pluck('viticulturist_id');

        if (Auth::user()->isProducer()) {
            $viticulturistIds = $viticulturistIds->push($wineryId)->unique();
        }

        $viticulturists = User::whereIn('id', $viticulturistIds)->orderBy('name')->get(['id', 'name']);

        $availableHarvests = collect();
        $selectedHarvestIds = array_column($this->lines, 'harvest_id');

        if ($this->viticulturist_id) {
            $availableHarvests = Harvest::where('winery_id', $wineryId)
                ->whereHas('batch', fn ($q) => $q->where('viticulturist_id', $this->viticulturist_id))
                // Exclude harvests already included in an active (non-cancelled) invoice
                ->whereDoesntHave('invoiceItems', function ($q) {
                    $q->where('concept_type', 'harvest')
                        ->whereHas('invoice', fn ($q2) => $q2->where('status', '!=', 'cancelled'));
                })
                ->with(['plotPlanting.grapeVariety'])
                ->orderByDesc('harvest_start_date')
                ->get();
        }

        return view('livewire.winery.billing.grape-purchase.create', [
            'viticulturists' => $viticulturists,
            'availableHarvests' => $availableHarvests,
            'selectedHarvestIds' => $selectedHarvestIds,
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        return array_merge($this->grapePurchaseBaseRules(), [
            'viticulturist_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $wineryId = \Illuminate\Support\Facades\Auth::id();
                    $isSelf = \Illuminate\Support\Facades\Auth::user()->isProducer() && (int) $value === $wineryId;
                    if (! $isSelf && $value && ! \App\Models\WineryViticulturist::where('winery_id', $wineryId)
                        ->where('viticulturist_id', $value)
                        ->where('source', 'own')
                        ->exists()) {
                        $fail(__('El viticultor seleccionado no pertenece a tu bodega.'));
                    }
                },
            ],
            'lines.*.harvest_id' => 'required|exists:harvests,id',
        ]);
    }
}
