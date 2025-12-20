<?php

namespace App\Livewire\Viticulturist\Clients;

use App\Models\Client;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Edit extends Component
{
    use WithToastNotifications;

    public Client $client;
    public $client_id;

    public $client_type = 'individual';
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $phone = '';
    public $company_name = '';
    public $company_document = '';
    public $particular_document = '';
    public $default_discount = 0;
    public $payment_method = '';
    public $account_number = '';
    public $has_cae = false;
    public $cae_number = '';
    public $active = true;
    public $notes = '';

    public function updatedHasCae($value)
    {
        if (!$value) {
            $this->cae_number = '';
        }
    }

    public function mount($client)
    {
        $this->client_id = $client;
        $this->loadClient();
    }

    public function loadClient()
    {
        $user = Auth::user();
        $this->client = Client::forUser($user->id)->findOrFail($this->client_id);

        $this->client_type = $this->client->client_type;
        $this->first_name = $this->client->first_name ?? '';
        $this->last_name = $this->client->last_name ?? '';
        $this->email = $this->client->email ?? '';
        $this->phone = $this->client->phone ?? '';
        $this->company_name = $this->client->company_name ?? '';
        $this->company_document = $this->client->company_document ?? '';
        $this->particular_document = $this->client->particular_document ?? '';
        $this->default_discount = $this->client->default_discount;
        $this->payment_method = $this->client->payment_method ?? '';
        $this->account_number = $this->client->account_number ?? '';
        $this->has_cae = $this->client->has_cae;
        $this->cae_number = $this->client->cae_number ?? '';
        $this->active = $this->client->active;
        $this->notes = $this->client->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'client_type' => 'required|in:individual,company',
            'first_name' => 'required_if:client_type,individual|nullable|string|max:100',
            'last_name' => 'required_if:client_type,individual|nullable|string|max:100',
            'email' => 'nullable|email|max:50',
            'phone' => 'nullable|string|max:50',
            'company_name' => 'required_if:client_type,company|nullable|string|max:100',
            'company_document' => 'nullable|string|max:50',
            'particular_document' => 'nullable|string|max:15',
            'default_discount' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'nullable|in:cash,transfer,check,other',
            'account_number' => 'nullable|string|max:50',
            'has_cae' => 'boolean',
            'cae_number' => 'nullable|string|max:255',
            'active' => 'boolean',
            'notes' => 'nullable|string',
        ];
    }

    public function update()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $this->client->update([
                    'client_type' => $this->client_type,
                    'first_name' => $this->first_name ?: null,
                    'last_name' => $this->last_name ?: null,
                    'email' => $this->email ?: null,
                    'phone' => $this->phone ?: null,
                    'company_name' => $this->company_name ?: null,
                    'company_document' => $this->company_document ?: null,
                    'particular_document' => $this->particular_document ?: null,
                    'default_discount' => $this->default_discount,
                    'payment_method' => $this->payment_method ?: null,
                    'account_number' => $this->account_number ?: null,
                    'has_cae' => $this->has_cae,
                    'cae_number' => $this->cae_number ?: null,
                    'active' => $this->active,
                    'notes' => $this->notes ?: null,
                ]);
            });

            $this->toastSuccess('Cliente actualizado exitosamente.');
            return redirect()->route('viticulturist.clients.show', $this->client->id);
        } catch (\Exception $e) {
            $this->toastError('Error al actualizar el cliente: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.clients.edit')
            ->layout('layouts.app');
    }
}
