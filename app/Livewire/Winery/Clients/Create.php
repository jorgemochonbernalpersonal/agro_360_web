<?php

namespace App\Livewire\Winery\Clients;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $client_type          = 'individual';
    public string $first_name           = '';
    public string $last_name            = '';
    public string $company_name         = '';
    public string $email                = '';
    public string $phone                = '';
    public string $company_document     = '';
    public string $particular_document  = '';
    public string $payment_method       = '';
    public string $account_number       = '';
    public string $notes                = '';

    protected function rules(): array
    {
        $rules = [
            'client_type'    => 'required|in:individual,company',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:30',
            'payment_method' => 'nullable|in:cash,transfer,check,other',
            'account_number' => 'nullable|string|max:50',
            'notes'          => 'nullable|string',
        ];

        if ($this->client_type === 'individual') {
            $rules['first_name']          = 'required|string|max:255';
            $rules['last_name']           = 'nullable|string|max:255';
            $rules['particular_document'] = 'nullable|string|max:20';
        } else {
            $rules['company_name']     = 'required|string|max:255';
            $rules['company_document'] = 'nullable|string|max:20';
        }

        return $rules;
    }

    public function save()
    {
        $data = $this->validate();

        Client::create([
            'user_id'             => Auth::id(),
            'client_type'         => $data['client_type'],
            'first_name'          => $this->client_type === 'individual' ? ($data['first_name'] ?? null) : null,
            'last_name'           => $this->client_type === 'individual' ? ($data['last_name'] ?? null) : null,
            'company_name'        => $this->client_type === 'company' ? ($data['company_name'] ?? null) : null,
            'company_document'    => $this->client_type === 'company' ? ($data['company_document'] ?? null) : null,
            'particular_document' => $this->client_type === 'individual' ? ($data['particular_document'] ?? null) : null,
            'email'               => $data['email'] ?: null,
            'phone'               => $data['phone'] ?: null,
            'payment_method'      => $data['payment_method'] ?: null,
            'account_number'      => $data['account_number'] ?: null,
            'notes'               => $data['notes'] ?: null,
            'active'              => true,
        ]);

        $this->toastSuccess('Cliente creado correctamente.');
        return $this->redirect(route('winery.clients.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.clients.create')->layout('layouts.app');
    }
}
