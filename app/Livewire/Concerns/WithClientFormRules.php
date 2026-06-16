<?php

namespace App\Livewire\Concerns;

trait WithClientFormRules
{
    protected function clientFormRules(bool $companyDocumentRequired = false): array
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

            'addresses'                                    => 'required|array|min:1',
            'addresses.*.address'                          => 'required|string|max:255',
            'addresses.*.postal_code'                      => 'required|string|max:10',
            'addresses.*.municipality_id'                  => 'required|exists:municipalities,id',
            'addresses.*.province_id'                      => 'required|exists:provinces,id',
            'addresses.*.autonomous_community_id'          => 'required|exists:autonomous_communities,id',
            'addresses.*.is_default'                       => 'boolean',
            'addresses.*.description'                      => 'nullable|string|max:500',
        ];

        if ($this->client_type === 'individual') {
            $rules['first_name']           = 'required|string|max:255';
            $rules['last_name']            = 'nullable|string|max:255';
            $rules['particular_document']  = 'nullable|string|max:20';
        } else {
            $rules['company_name']     = 'required|string|max:255';
            $rules['company_document'] = ($companyDocumentRequired ? 'required' : 'nullable').'|string|max:50';
        }

        return $rules;
    }
}
