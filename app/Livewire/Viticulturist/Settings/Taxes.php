<?php

namespace App\Livewire\Viticulturist\Settings;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Tax;
use App\Models\UserTax;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Taxes extends Component
{
    use WithToastNotifications;

    /** IDs de impuestos habilitados por el usuario */
    public array $enabledTaxIds = [];

    /** ID del impuesto marcado como defecto */
    public ?int $defaultTaxId = null;

    public function mount(): void
    {
        $this->loadUserTaxes();
    }

    /**
     * Activar o desactivar un impuesto para el usuario.
     * Si se desactiva el que era defecto, se asigna el primero habilitado restante.
     */
    public function toggleTax(int $taxId): void
    {
        $userId = Auth::id();

        if (in_array($taxId, $this->enabledTaxIds, true)) {
            // Deshabilitar
            UserTax::where('user_id', $userId)->where('tax_id', $taxId)->delete();
            $this->enabledTaxIds = array_values(array_filter($this->enabledTaxIds, fn ($id) => $id !== $taxId));

            // Si era el defecto, reasignar al primero restante
            if ($this->defaultTaxId === $taxId) {
                $this->defaultTaxId = null;
                if (! empty($this->enabledTaxIds)) {
                    $this->setDefault($this->enabledTaxIds[0]);

                    return;
                }
            }

            $this->toastInfo(__('Impuesto desactivado.'));
        } else {
            // Habilitar
            UserTax::firstOrCreate(
                ['user_id' => $userId, 'tax_id' => $taxId],
                ['is_default' => false, 'order' => count($this->enabledTaxIds)]
            );
            $this->enabledTaxIds[] = $taxId;

            // Si es el primero, marcarlo como defecto automáticamente
            if ($this->defaultTaxId === null) {
                $this->setDefault($taxId);

                return;
            }

            $this->toastSuccess(__('Impuesto activado.'));
        }
    }

    /**
     * Marcar un impuesto como el defecto del usuario.
     * Lo habilita si no lo estaba ya.
     */
    public function setDefault(int $taxId): void
    {
        $userId = Auth::id();

        // Asegurarse de que esté habilitado
        if (! in_array($taxId, $this->enabledTaxIds, true)) {
            UserTax::firstOrCreate(
                ['user_id' => $userId, 'tax_id' => $taxId],
                ['is_default' => false, 'order' => count($this->enabledTaxIds)]
            );
            $this->enabledTaxIds[] = $taxId;
        }

        // Quitar el defecto anterior
        UserTax::where('user_id', $userId)->update(['is_default' => false]);

        // Establecer el nuevo defecto
        UserTax::where('user_id', $userId)
            ->where('tax_id', $taxId)
            ->update(['is_default' => true]);

        $this->defaultTaxId = $taxId;

        $tax = Tax::find($taxId);
        $this->toastSuccess("Impuesto por defecto: {$tax->name} ({$tax->formatted_rate})");
    }

    public function render()
    {
        $taxes = Tax::active()->orderBy('rate')->get();

        return view('livewire.viticulturist.settings.taxes', [
            'taxes' => $taxes,
        ])->layout('layouts.app');
    }

    protected function loadUserTaxes(): void
    {
        $userTaxes = UserTax::where('user_id', Auth::id())->get();
        $this->enabledTaxIds = $userTaxes->pluck('tax_id')->map(fn ($id) => (int) $id)->toArray();
        $this->defaultTaxId = (int) ($userTaxes->firstWhere('is_default', true)->tax_id ?? 0) ?: null;
    }
}
