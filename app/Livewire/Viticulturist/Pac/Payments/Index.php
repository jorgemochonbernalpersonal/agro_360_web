<?php

namespace App\Livewire\Viticulturist\Pac\Payments;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\PacDeclaration;
use App\Models\PacPayment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use WithToastNotifications;

    // Formulario inline
    public bool   $showForm    = false;
    public ?int   $editingId   = null;
    public string $yearFilter  = '';

    public int    $year         = 0;
    public string $payment_type = 'basic_payment';
    public string $amount       = '';
    public string $payment_date = '';
    public string $reference    = '';
    public string $notes        = '';
    public string $declaration_id = '';

    public function mount(): void
    {
        $this->yearFilter = (string) now()->year;
        $this->year       = now()->year;
        $this->payment_date = now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'year'         => 'required|integer|min:2000|max:' . (now()->year + 1),
            'payment_type' => 'required|in:' . implode(',', array_keys(PacPayment::PAYMENT_TYPES)),
            'amount'       => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'reference'    => 'nullable|string|max:100',
            'notes'        => 'nullable|string',
            'declaration_id' => 'nullable|exists:pac_declarations,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'year.required'         => __('El año es obligatorio.'),
            'payment_type.required' => __('El tipo de pago es obligatorio.'),
            'amount.required'       => __('El importe es obligatorio.'),
            'amount.min'            => __('El importe debe ser mayor que 0.'),
            'payment_date.required' => __('La fecha es obligatoria.'),
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->year       = (int) ($this->yearFilter ?: now()->year);
        $this->payment_date = now()->format('Y-m-d');
        $this->showForm   = true;
        $this->editingId  = null;
    }

    public function openEdit(int $id): void
    {
        $payment = PacPayment::forViticulturist(Auth::id())->findOrFail($id);
        $this->editingId      = $id;
        $this->year           = $payment->year;
        $this->payment_type   = $payment->payment_type;
        $this->amount         = $payment->amount;
        $this->payment_date   = $payment->payment_date->format('Y-m-d');
        $this->reference      = $payment->reference ?? '';
        $this->notes          = $payment->notes ?? '';
        $this->declaration_id = $payment->declaration_id ?? '';
        $this->showForm       = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'viticulturist_id' => Auth::id(),
            'year'             => $this->year,
            'payment_type'     => $this->payment_type,
            'amount'           => $this->amount,
            'payment_date'     => $this->payment_date,
            'reference'        => $this->reference ?: null,
            'notes'            => $this->notes ?: null,
            'declaration_id'   => $this->declaration_id ?: null,
        ];

        if ($this->editingId) {
            PacPayment::forViticulturist(Auth::id())->findOrFail($this->editingId)->update($data);
            $this->toastSuccess(__('Pago actualizado.'));
        } else {
            PacPayment::create($data);
            $this->toastSuccess(__('Pago registrado.'));
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $id): void
    {
        PacPayment::forViticulturist(Auth::id())->findOrFail($id)->delete();
        $this->toastSuccess(__('Pago eliminado.'));
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->editingId      = null;
        $this->year           = (int) ($this->yearFilter ?: now()->year);
        $this->payment_type   = 'basic_payment';
        $this->amount         = '';
        $this->payment_date   = now()->format('Y-m-d');
        $this->reference      = '';
        $this->notes          = '';
        $this->declaration_id = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        $viticulturistId = Auth::id();
        $filterYear      = (int) ($this->yearFilter ?: now()->year);

        $payments = PacPayment::forViticulturist($viticulturistId)
            ->where('year', $filterYear)
            ->with('declaration')
            ->orderBy('payment_date', 'desc')
            ->get();

        $allPayments = PacPayment::forViticulturist($viticulturistId)->get();

        $stats = [
            'total_year'    => $payments->sum('amount'),
            'by_type'       => $payments->groupBy('payment_type')
                ->map(fn($g) => $g->sum('amount')),
            'total_historic'=> $allPayments->sum('amount'),
            'count_year'    => $payments->count(),
        ];

        $declarations = PacDeclaration::forViticulturist($viticulturistId)
            ->orderByDesc('year')
            ->get();

        $availableYears = $allPayments->pluck('year')
            ->merge($declarations->pluck('year'))
            ->push(now()->year)
            ->unique()
            ->sortDesc()
            ->values();

        return view('livewire.viticulturist.pac.payments.index', [
            'payments'       => $payments,
            'stats'          => $stats,
            'paymentTypes'   => PacPayment::PAYMENT_TYPES,
            'declarations'   => $declarations,
            'availableYears' => $availableYears,
            'filterYear'     => $filterYear,
        ])->layout('layouts.app');
    }
}
