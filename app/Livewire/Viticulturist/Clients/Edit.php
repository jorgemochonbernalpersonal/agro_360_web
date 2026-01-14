<?php

namespace App\Livewire\Viticulturist\Clients;

use App\Models\Client;
use App\Models\AutonomousCommunity;
use App\Models\Province;
use App\Models\Municipality;
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
    
    // Direcciones
    public $addresses = [];
    public $deletedAddressIds = [];
    
    // Datos geográficos
    public $autonomousCommunities;
    public $provinces = [];
    public $municipalities = [];

    public function updatedHasCae($value)
    {
        if (!$value) {
            $this->cae_number = '';
        }
    }

    public function mount($client)
    {
        // Si es un modelo, usarlo directamente; si es un ID, buscarlo
        if ($client instanceof Client) {
            $this->client = $client;
            $this->client_id = $client->id;
        } else {
            $this->client_id = $client;
        }
        
        // Cargar comunidades autónomas
        $this->autonomousCommunities = AutonomousCommunity::orderBy('name')->get();
        $this->loadClient();
    }

    public function loadClient()
    {
        $user = Auth::user();
        
        // Si ya tenemos el cliente cargado, solo cargar relaciones
        if (!isset($this->client) || $this->client->id != $this->client_id) {
            $this->client = Client::with('addresses')->forUser($user->id)->findOrFail($this->client_id);
        } else {
            // Asegurar que el cliente pertenece al usuario actual
            if ($this->client->user_id !== $user->id) {
                abort(403, 'No tienes permiso para editar este cliente.');
            }
            // Cargar relaciones si no están cargadas
            if (!$this->client->relationLoaded('addresses')) {
                $this->client->load('addresses');
            }
        }

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
        
        // Cargar direcciones existentes
        $this->addresses = $this->client->addresses->map(function ($address) {
            return [
                'id' => $address->id,
                'address' => $address->address ?? '',
                'postal_code' => $address->postal_code ?? '',
                'municipality_id' => $address->municipality_id,
                'province_id' => $address->province_id,
                'autonomous_community_id' => $address->autonomous_community_id,
                'is_default' => $address->is_default,
                'description' => $address->description ?? '',
            ];
        })->toArray();
        
        // Cargar provincias y municipios para direcciones existentes
        foreach ($this->addresses as $index => $address) {
            if ($address['autonomous_community_id']) {
                $this->loadProvinces($index);
            }
            if ($address['province_id']) {
                $this->loadMunicipalities($index);
            }
        }
        
        // Si no tiene direcciones, añadir una vacía
        if (empty($this->addresses)) {
            $this->addresses = [[
                'id' => null,
                'address' => '',
                'postal_code' => '',
                'municipality_id' => null,
                'province_id' => null,
                'autonomous_community_id' => null,
                'is_default' => true,
                'description' => '',
            ]];
        }
    }

    public function addAddress()
    {
        $this->addresses[] = [
            'id' => null,
            'address' => '',
            'postal_code' => '',
            'municipality_id' => null,
            'province_id' => null,
                'autonomous_community_id' => null,
                'is_default' => false,
                'description' => '',
        ];
    }

    public function removeAddress($index)
    {
        if (count($this->addresses) > 1) {
            // Si tiene ID, marcar para eliminar
            if (isset($this->addresses[$index]['id']) && $this->addresses[$index]['id']) {
                $this->deletedAddressIds[] = $this->addresses[$index]['id'];
            }
            
            unset($this->addresses[$index]);
            $this->addresses = array_values($this->addresses);
            
            // Asegurar que al menos una esté marcada como default
            $hasDefault = false;
            foreach ($this->addresses as $address) {
                if ($address['is_default']) {
                    $hasDefault = true;
                    break;
                }
            }
            if (!$hasDefault && count($this->addresses) > 0) {
                $this->addresses[0]['is_default'] = true;
            }
        }
    }

    public function setDefaultAddress($index)
    {
        foreach ($this->addresses as $key => $address) {
            $this->addresses[$key]['is_default'] = ($key === $index);
        }
    }
    
    public function updatedAddresses($value, $key)
    {
        // Si cambia la comunidad autónoma de alguna dirección
        if (str_contains($key, '.autonomous_community_id')) {
            $index = (int) explode('.', $key)[0];
            // Limpiar provincia y municipio primero
            $this->addresses[$index]['province_id'] = null;
            $this->addresses[$index]['municipality_id'] = null;
            $this->provinces[$index] = [];
            $this->municipalities[$index] = [];
            // Luego cargar provincias si hay comunidad autónoma seleccionada
            if ($this->addresses[$index]['autonomous_community_id'] ?? null) {
                $this->loadProvinces($index);
            }
        }
        
        // Si cambia la provincia
        if (str_contains($key, '.province_id')) {
            $index = (int) explode('.', $key)[0];
            // Limpiar municipio primero
            $this->addresses[$index]['municipality_id'] = null;
            $this->municipalities[$index] = [];
            // Luego cargar municipios si hay provincia seleccionada
            if ($this->addresses[$index]['province_id'] ?? null) {
                $this->loadMunicipalities($index);
            }
        }
    }
    
    public function loadProvinces($index)
    {
        $caId = $this->addresses[$index]['autonomous_community_id'] ?? null;
        if ($caId) {
            $this->provinces[$index] = Province::where('autonomous_community_id', $caId)
                ->orderBy('name')
                ->get();
        } else {
            $this->provinces[$index] = [];
        }
    }
    
    public function loadMunicipalities($index)
    {
        $provinceId = $this->addresses[$index]['province_id'] ?? null;
        if ($provinceId) {
            $this->municipalities[$index] = Municipality::where('province_id', $provinceId)
                ->orderBy('name')
                ->get();
        } else {
            $this->municipalities[$index] = [];
        }
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
            'company_document' => 'required_if:client_type,company|nullable|string|max:50',
            'particular_document' => 'nullable|string|max:15',
            'default_discount' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'nullable|in:cash,transfer,check,other',
            'account_number' => 'nullable|string|max:50',
            'has_cae' => 'boolean',
            'cae_number' => 'nullable|string|max:255',
            'active' => 'boolean',
            'notes' => 'nullable|string',
            
            // Validación de direcciones
            'addresses' => 'required|array|min:1',
            'addresses.*.address' => 'required|string|max:255',
            'addresses.*.postal_code' => 'required|string|max:10',
            'addresses.*.municipality_id' => 'required|exists:municipalities,id',
            'addresses.*.province_id' => 'required|exists:provinces,id',
            'addresses.*.autonomous_community_id' => 'required|exists:autonomous_communities,id',
            'addresses.*.is_default' => 'boolean',
            'addresses.*.description' => 'nullable|string|max:500',
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
                
                // Eliminar direcciones marcadas
                if (!empty($this->deletedAddressIds)) {
                    $this->client->addresses()->whereIn('id', $this->deletedAddressIds)->delete();
                }
                
                // Actualizar o crear direcciones
                foreach ($this->addresses as $addressData) {
                    if (!empty($addressData['address'])) {
                        $data = [
                            'address' => $addressData['address'],
                            'postal_code' => $addressData['postal_code'] ?: null,
                            'municipality_id' => $addressData['municipality_id'] ?: null,
                            'province_id' => $addressData['province_id'] ?: null,
                            'autonomous_community_id' => $addressData['autonomous_community_id'] ?: null,
                            'is_default' => $addressData['is_default'] ?? false,
                            'description' => $addressData['description'] ?: null,
                        ];
                        
                        if (isset($addressData['id']) && $addressData['id']) {
                            // Actualizar dirección existente
                            $this->client->addresses()->where('id', $addressData['id'])->update($data);
                        } else {
                            // Crear nueva dirección
                            $this->client->addresses()->create($data);
                        }
                    }
                }
            });

            $this->toastSuccess('Cliente actualizado exitosamente.');
            return redirect()->route('viticulturist.clients.index');
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
