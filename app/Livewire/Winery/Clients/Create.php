<?php

namespace App\Livewire\Winery\Clients;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AutonomousCommunity;
use App\Models\Client;
use App\Models\Municipality;
use App\Models\Province;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $client_type = 'individual';

    public string $first_name = '';

    public string $last_name = '';

    public string $company_name = '';

    public string $company_document = '';

    public string $particular_document = '';

    public string $email = '';

    public string $phone = '';

    public float $default_discount = 0;

    public string $payment_method = '';

    public string $account_number = '';

    public bool $has_cae = false;

    public string $cae_number = '';

    public string $notes = '';

    public array $addresses = [];

    public $autonomousCommunities;

    public array $provinces = [];

    public array $municipalities = [];

    public function mount(): void
    {
        $this->autonomousCommunities = AutonomousCommunity::orderBy('name')->get();

        $this->addresses = [[
            'address' => '',
            'postal_code' => '',
            'municipality_id' => null,
            'province_id' => null,
            'autonomous_community_id' => null,
            'is_default' => true,
            'description' => '',
        ]];
    }

    public function addAddress(): void
    {
        $this->addresses[] = [
            'address' => '',
            'postal_code' => '',
            'municipality_id' => null,
            'province_id' => null,
            'autonomous_community_id' => null,
            'is_default' => false,
            'description' => '',
        ];
    }

    public function removeAddress(int $index): void
    {
        if (count($this->addresses) > 1) {
            unset($this->addresses[$index]);
            $this->addresses = array_values($this->addresses);

            $hasDefault = collect($this->addresses)->contains('is_default', true);
            if (! $hasDefault) {
                $this->addresses[0]['is_default'] = true;
            }
        }
    }

    public function setDefaultAddress(int $index): void
    {
        foreach ($this->addresses as $key => $_) {
            $this->addresses[$key]['is_default'] = ($key === $index);
        }
    }

    public function updatedAddresses($value, string $key): void
    {
        if (str_contains($key, '.autonomous_community_id')) {
            $index = (int) explode('.', $key)[0];
            $this->addresses[$index]['province_id'] = null;
            $this->addresses[$index]['municipality_id'] = null;
            $this->provinces[$index] = [];
            $this->municipalities[$index] = [];
            if ($this->addresses[$index]['autonomous_community_id'] ?? null) {
                $this->loadProvinces($index);
            }
        }

        if (str_contains($key, '.province_id')) {
            $index = (int) explode('.', $key)[0];
            $this->addresses[$index]['municipality_id'] = null;
            $this->municipalities[$index] = [];
            if ($this->addresses[$index]['province_id'] ?? null) {
                $this->loadMunicipalities($index);
            }
        }
    }

    public function loadProvinces(int $index): void
    {
        $caId = $this->addresses[$index]['autonomous_community_id'] ?? null;
        $this->provinces[$index] = $caId
            ? Province::where('autonomous_community_id', $caId)->orderBy('name')->get()
            : [];
    }

    public function loadMunicipalities(int $index): void
    {
        $provinceId = $this->addresses[$index]['province_id'] ?? null;
        $this->municipalities[$index] = $provinceId
            ? Municipality::where('province_id', $provinceId)->orderBy('name')->get()
            : [];
    }

    public function save()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $client = Client::create([
                    'user_id' => Auth::id(),
                    'client_type' => $this->client_type,
                    'first_name' => $this->client_type === 'individual' ? ($this->first_name ?: null) : null,
                    'last_name' => $this->client_type === 'individual' ? ($this->last_name ?: null) : null,
                    'company_name' => $this->client_type === 'company' ? ($this->company_name ?: null) : null,
                    'company_document' => $this->client_type === 'company' ? ($this->company_document ?: null) : null,
                    'particular_document' => $this->client_type === 'individual' ? ($this->particular_document ?: null) : null,
                    'email' => $this->email ?: null,
                    'phone' => $this->phone ?: null,
                    'default_discount' => $this->default_discount,
                    'payment_method' => $this->payment_method ?: null,
                    'account_number' => $this->account_number ?: null,
                    'has_cae' => $this->has_cae,
                    'cae_number' => $this->has_cae ? ($this->cae_number ?: null) : null,
                    'notes' => $this->notes ?: null,
                    'active' => true,
                ]);

                foreach ($this->addresses as $addressData) {
                    if (! empty($addressData['address'])) {
                        $client->addresses()->create([
                            'address' => $addressData['address'],
                            'postal_code' => $addressData['postal_code'] ?: null,
                            'municipality_id' => $addressData['municipality_id'] ?: null,
                            'province_id' => $addressData['province_id'] ?: null,
                            'autonomous_community_id' => $addressData['autonomous_community_id'] ?: null,
                            'is_default' => $addressData['is_default'] ?? false,
                            'description' => $addressData['description'] ?: null,
                        ]);
                    }
                }
            });

            $this->toastSuccess(__('Cliente creado correctamente.'));

            return $this->redirect(roleRoute('clients.index'), navigate: true);

        } catch (\Exception $e) {
            $this->toastError(__('Error al crear el cliente: :error', ['error' => $e->getMessage()]));
        }
    }

    public function render()
    {
        return view('livewire.winery.clients.create', [
            'autonomousCommunities' => $this->autonomousCommunities,
            'provinces' => $this->provinces,
            'municipalities' => $this->municipalities,
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        $rules = [
            'client_type' => 'required|in:individual,company',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'default_discount' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'nullable|in:cash,transfer,check,other',
            'account_number' => 'nullable|string|max:50',
            'has_cae' => 'boolean',
            'cae_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',

            'addresses' => 'required|array|min:1',
            'addresses.*.address' => 'required|string|max:255',
            'addresses.*.postal_code' => 'required|string|max:10',
            'addresses.*.municipality_id' => 'required|exists:municipalities,id',
            'addresses.*.province_id' => 'required|exists:provinces,id',
            'addresses.*.autonomous_community_id' => 'required|exists:autonomous_communities,id',
            'addresses.*.is_default' => 'boolean',
            'addresses.*.description' => 'nullable|string|max:500',
        ];

        if ($this->client_type === 'individual') {
            $rules['first_name'] = 'required|string|max:255';
            $rules['last_name'] = 'nullable|string|max:255';
            $rules['particular_document'] = 'nullable|string|max:20';
        } else {
            $rules['company_name'] = 'required|string|max:255';
            $rules['company_document'] = 'required|string|max:50';
        }

        return $rules;
    }
}
