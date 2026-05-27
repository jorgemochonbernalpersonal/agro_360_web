<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        :title="__('Cliente') . ': ' . $client->full_name"
        :description="__('Detalles del cliente')"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.clients.edit', $client->id) }}" variant="primary" icon="pencil-square">
                {{ __('Editar') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Información del Cliente --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-agro-50">
                    <flux:icon icon="user" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">{{ __('Información del Cliente') }}</span>
            </div>
        </x-slot:header>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-zinc-500">{{ __('Tipo') }}</p>
                <p class="font-semibold">{{ $client->client_type === 'company' ? __('Empresa') : __('Particular') }}</p>
            </div>
            <div>
                <p class="text-sm text-zinc-500">{{ __('Estado') }}</p>
                <x-agro.status-badge :active="$client->active" />
            </div>
            @if($client->email)
                <div>
                    <p class="text-sm text-zinc-500">{{ __('Email') }}</p>
                    <p class="font-semibold">{{ $client->email }}</p>
                </div>
            @endif
            @if($client->phone)
                <div>
                    <p class="text-sm text-zinc-500">{{ __('Teléfono') }}</p>
                    <p class="font-semibold">{{ $client->phone }}</p>
                </div>
            @endif
            @if($client->client_type === 'company' && $client->company_document)
                <div>
                    <p class="text-sm text-zinc-500">{{ __('CIF/NIF') }}</p>
                    <p class="font-semibold">{{ mask_nif($client->company_document) }}</p>
                </div>
            @endif
            @if($client->client_type === 'individual' && $client->particular_document)
                <div>
                    <p class="text-sm text-zinc-500">{{ __('DNI/NIE') }}</p>
                    <p class="font-semibold">{{ mask_nif($client->particular_document) }}</p>
                </div>
            @endif
            @if($client->default_discount > 0)
                <div>
                    <p class="text-sm text-zinc-500">{{ __('Descuento por defecto') }}</p>
                    <p class="font-semibold">{{ number_format($client->default_discount, 2) }}%</p>
                </div>
            @endif
            @if($client->payment_method)
                <div>
                    <p class="text-sm text-zinc-500">{{ __('Forma de pago') }}</p>
                    <p class="font-semibold">{{ match($client->payment_method) { 'cash' => 'Efectivo', 'transfer' => 'Transferencia', 'check' => 'Cheque', default => 'Otro' } }}</p>
                </div>
            @endif
            @if($client->account_number)
                <div>
                    <p class="text-sm text-zinc-500">{{ __('Número de cuenta') }}</p>
                    <p class="font-semibold">{{ $client->account_number }}</p>
                </div>
            @endif
            @if($client->has_cae && $client->cae_number)
                <div>
                    <p class="text-sm text-zinc-500">{{ __('CAE') }}</p>
                    <p class="font-semibold">{{ $client->cae_number }}</p>
                </div>
            @endif
        </div>
        @if($client->notes)
            <div class="mt-4 pt-4 border-t border-zinc-200">
                <p class="text-sm text-zinc-500 mb-2">{{ __('Notas') }}</p>
                <p class="text-sm text-zinc-700 whitespace-pre-wrap">{{ $client->notes }}</p>
            </div>
        @endif
    </x-agro.card>

    {{-- Direcciones --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-blue-50">
                        <flux:icon icon="map-pin" class="size-4 text-blue-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">{{ __('Direcciones') }}</span>
                </div>
                @if($client->addresses && $client->addresses->count() > 0)
                    <flux:badge color="blue" size="sm">{{ $client->addresses->count() }} {{ $client->addresses->count() === 1 ? 'dirección' : 'direcciones' }}</flux:badge>
                @endif
            </div>
        </x-slot:header>

        @if($client->addresses && $client->addresses->count() > 0)
            <div class="space-y-4">
                @foreach($client->addresses as $address)
                    <div class="border-2 rounded-lg p-4 {{ $address->is_default ? 'border-agro-500 bg-agro-50/50' : 'border-zinc-200 bg-white' }} hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h4 class="font-bold text-zinc-900">
                                        @if($address->description)
                                            {{ $address->description }}
                                        @else
                                            Dirección #{{ $loop->iteration }}
                                        @endif
                                    </h4>
                                    @if($address->is_default)
                                        <flux:badge color="green" size="sm">{{ __('Por defecto') }}</flux:badge>
                                    @endif
                                </div>

                                <div class="space-y-1 text-sm text-zinc-700">
                                    <p class="font-medium">{{ $address->address }}</p>
                                    <div class="flex flex-wrap items-center gap-2 text-zinc-600">
                                        @if($address->municipality)
                                            <span>{{ $address->municipality->name }}</span>
                                        @endif
                                        @if($address->province)
                                            <span>{{ $address->province->name }}</span>
                                        @endif
                                        @if($address->autonomousCommunity)
                                            <span class="text-zinc-500">({{ $address->autonomousCommunity->name }})</span>
                                        @endif
                                        @if($address->postal_code)
                                            <span class="font-semibold">{{ $address->postal_code }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="ml-4">
                                <flux:icon icon="map-pin" class="size-6 text-zinc-400" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-agro.empty-state
                :message="__('No hay direcciones registradas')"
                :description="__('Edita el cliente para agregar direcciones')"
                icon="map-pin"
            />
        @endif
    </x-agro.card>
</div>
