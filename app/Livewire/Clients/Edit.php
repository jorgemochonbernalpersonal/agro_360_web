<?php

namespace App\Livewire\Clients;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AutonomousCommunity;
use App\Models\Client;
use App\Models\Municipality;
use App\Models\Province;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public Client $client;

    public string $client_type          = 'individual';
    public string $first_name           = '';
    public string $last_name            = '';
    public string $company_name         = '';
    public string $email                = '';
    public string $phone                = '';
    public string $company_document     = '';
    public string $particular_document  = '';
    public float  $default_discount     = 0;
    public string $payment_method       = '';
    public string $account_number       = '';
    public bool   $has_cae              = false;
    public string $cae_number           = '';
    public string $notes                = '';

    public array $addresses      = [];
    public array $provinces      = [];
    public array $municipalities = [];

    public $autonomousCommunities;

    public function mount(Client $client): void
    {
        abort_if($client->user_id !== Auth::id(), 403);

        $this->client             = $client;
        $this->client_type         = $client->client_type;
        $this->first_name          = $client->first_name ?? '';
        $this->last_name           = $client->last_name ?? '';
        $this->company_name        = $client->company_name ?? '';
        $this->email               = $client->email ?? '';
        $this->phone               = $client->phone ?? '';
        $this->company_document    = $client->company_document ?? '';
        $this->particular_document = $client->particular_document ?? '';
        $this->default_discount    = (float) ($client->default_discount ?? 0);
        $this->payment_method      = $client->payment_method ?? '';
        $this->account_number      = $client->account_number ?? '';
        $this->has_cae             = (bool) ($client->has_cae ?? false);
        $this->cae_number          = $client->cae_number ?? '';
        $this->notes               = $client->notes ?? '';

        $this->autonomousCommunities = AutonomousCommunity::orderBy('name')->get();

        $existing = $client->addresses()->get();

        if ($existing->isEmpty()) {
            $this->addresses = [[
                'id'                       => null,
                'address'                  => '',
                'postal_code'              => '',
                'municipality_id'          => null,
                'province_id'              => null,
                'autonomous_community_id'  => null,
                'is_default'               => true,
                'description'              => '',
            ]];
        } else {
            foreach ($existing as $i => $addr) {
                $this->addresses[$i] = [
                    'id'                      => $addr->id,
                    'address'                 => $addr->address ?? '',
                    'postal_code'             => $addr->postal_code ?? '',
                    'municipality_id'         => $addr->municipality_id,
                    'province_id'             => $addr->province_id,
                    'autonomous_community_id' => $addr->autonomous_community_id,
                    'is_default'              => (bool) $addr->is_default,
                    'description'             => $addr->description ?? '',
                ];

                if ($addr->autonomous_community_id) {
                    $this->provinces[$i] = Province::where('autonomous_community_id', $addr->autonomous_community_id)
                        ->orderBy('name')->get();
                }

                if ($addr->province_id) {
                    $this->municipalities[$i] = Municipality::where('province_id', $addr->province_id)
                        ->orderBy('name')->get();
                }
            }
        }
    }

    // ── Direcciones ────────────────────────────────────────────────────────────

    public function addAddress(): void
    {
        $this->addresses[] = [
            'id'                      => null,
            'address'                 => '',
            'postal_code'             => '',
            'municipality_id'         => null,
            'province_id'             => null,
            'autonomous_community_id' => null,
            'is_default'              => false,
            'description'             => '',
        ];
    }

    public function removeAddress(int $index): void
    {
        if (count($this->addresses) > 1) {
            unset($this->addresses[$index]);
            unset($this->provinces[$index]);
            unset($this->municipalities[$index]);
            $this->addresses      = array_values($this->addresses);
            $this->provinces      = array_values($this->provinces);
            $this->municipalities = array_values($this->municipalities);

            $hasDefault = collect($this->addresses)->contains('is_default', true);
            if (!$hasDefault) {
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
            $this->addresses[$index]['province_id']     = null;
            $this->addresses[$index]['municipality_id'] = null;
            $this->provinces[$index]                    = [];
            $this->municipalities[$index]               = [];
            if ($this->addresses[$index]['autonomous_community_id'] ?? null) {
                $this->loadProvinces($index);
            }
        }

        if (str_contains($key, '.province_id')) {
            $index = (int) explode('.', $key)[0];
            $this->addresses[$index]['municipality_id'] = null;
            $this->municipalities[$index]               = [];
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

    // ── Validación ────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        $rules = [
            'client_type'      => 'required|in:individual,company',
            'email'            => 'nullable|email|max:255',
            'phone'            => 'nullable|string|max:30',
            'default_discount' => 'nullable|numeric|min:0|max:100',
            'payment_method'   => 'nullable|in:cash,transfer,check,other',
            'account_number'   => 'nullable|string|max:50',
            'has_cae'          => 'boolean',
            'cae_number'       => 'nullable|string|max:255',
            'notes'            => 'nullable|string',

            'addresses'                           => 'required|array|min:1',
            'addresses.*.address'                 => 'required|string|max:255',
            'addresses.*.postal_code'             => 'required|string|max:10',
            'addresses.*.municipality_id'         => 'required|exists:municipalities,id',
            'addresses.*.province_id'             => 'required|exists:provinces,id',
            'addresses.*.autonomous_community_id' => 'required|exists:autonomous_communities,id',
            'addresses.*.is_default'              => 'boolean',
            'addresses.*.description'             => 'nullable|string|max:500',
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

    // ── Guardar ───────────────────────────────────────────────────────────────

    public function update()
    {
        $data = $this->validate();

        try {
            DB::transaction(function () use ($data) {
                $this->client->update([
                    'client_type'         => $data['client_type'],
                    'first_name'          => $this->client_type === 'individual' ? ($data['first_name'] ?? null) : null,
                    'last_name'           => $this->client_type === 'individual' ? ($data['last_name'] ?? null) : null,
                    'company_name'        => $this->client_type === 'company' ? ($data['company_name'] ?? null) : null,
                    'company_document'    => $this->client_type === 'company' ? ($data['company_document'] ?? null) : null,
                    'particular_document' => $this->client_type === 'individual' ? ($data['particular_document'] ?? null) : null,
                    'email'               => $data['email'] ?: null,
                    'phone'               => $data['phone'] ?: null,
                    'default_discount'    => $data['default_discount'] ?? 0,
                    'payment_method'      => $data['payment_method'] ?: null,
                    'account_number'      => $data['account_number'] ?: null,
                    'has_cae'             => $this->has_cae,
                    'cae_number'          => $this->has_cae ? ($this->cae_number ?: null) : null,
                    'notes'               => $data['notes'] ?: null,
                ]);

                $keptIds = [];

                foreach ($this->addresses as $addressData) {
                    $payload = [
                        'address'                 => $addressData['address'],
                        'postal_code'             => $addressData['postal_code'] ?: null,
                        'municipality_id'         => $addressData['municipality_id'] ?: null,
                        'province_id'             => $addressData['province_id'] ?: null,
                        'autonomous_community_id' => $addressData['autonomous_community_id'] ?: null,
                        'is_default'              => $addressData['is_default'] ?? false,
                        'description'             => $addressData['description'] ?: null,
                    ];

                    if (!empty($addressData['id'])) {
                        $addr = $this->client->addresses()->findOrFail($addressData['id']);
                        $addr->update($payload);
                        $keptIds[] = $addressData['id'];
                    } else {
                        $created   = $this->client->addresses()->create($payload);
                        $keptIds[] = $created->id;
                    }
                }

                // Eliminar direcciones borradas en el formulario
                $this->client->addresses()->whereNotIn('id', $keptIds)->delete();
            });

            $this->toastSuccess(__('Cliente actualizado correctamente.'));
            return $this->redirect(roleRoute('clients.index'), navigate: true);

        } catch (\Exception $e) {
            $this->toastError(__('Error al actualizar el cliente: :error', ['error' => $e->getMessage()]));
        }
    }

    public function render()
    {
        return view('livewire.clients.edit', [
            'autonomousCommunities' => $this->autonomousCommunities,
            'provinces'             => $this->provinces,
            'municipalities'        => $this->municipalities,
        ])->layout('layouts.app');
    }
}
