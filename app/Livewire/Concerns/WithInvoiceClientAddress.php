<?php

namespace App\Livewire\Concerns;

use App\Models\Client;

trait WithInvoiceClientAddress
{
    public $availableAddresses = [];

    public function updatedClientId($value): void
    {
        if ($value) {
            $client = Client::with([
                'addresses.municipality',
                'addresses.province',
                'addresses.autonomousCommunity',
            ])->find($value);

            if ($client) {
                $primaryAddress = $client->addresses->first();

                if ($primaryAddress) {
                    $this->client_address_id = $primaryAddress->id;
                } else {
                    $this->client_address_id = '';
                    $this->addError('client_id', __('Este cliente no tiene ninguna dirección configurada. Por favor, añade una dirección al cliente primero.'));
                }

                $this->availableAddresses = $client->addresses;
            } else {
                $this->availableAddresses = collect();
                $this->client_address_id = '';
            }
        } else {
            $this->availableAddresses = collect();
            $this->client_address_id = '';
        }
    }
}
