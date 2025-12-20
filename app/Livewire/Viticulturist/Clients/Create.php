<?php

namespace App\Livewire\Viticulturist\Clients;

use App\Models\Client;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Create extends Component
{
    use WithToastNotifications;

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

    protected function rules(): array
    {
        $rules = [
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

        return $rules;
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user) {
                Client::create([
                    'user_id' => $user->id,
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

            $this->toastSuccess('Cliente creado exitosamente.');
            return redirect()->route('viticulturist.clients.index');
        } catch (\Exception $e) {
            $this->toastError('Error al crear el cliente: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.clients.create')
            ->layout('layouts.app');
    }
}
