<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Cliente: {{ $client->full_name }}"
        description="Detalles del cliente"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <x-button href="{{ route('viticulturist.clients.edit', $client->id) }}" variant="primary">
                Editar
            </x-button>
        </x-slot:actionButton>
    </x-page-header>

    {{-- Información del Cliente --}}
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-lg font-bold mb-4">Información del Cliente</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Tipo</p>
                <p class="font-semibold">{{ $client->client_type === 'company' ? 'Empresa' : 'Particular' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Estado</p>
                <x-status-badge :active="$client->active" />
            </div>
            @if($client->email)
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-semibold">{{ $client->email }}</p>
                </div>
            @endif
            @if($client->phone)
                <div>
                    <p class="text-sm text-gray-500">Teléfono</p>
                    <p class="font-semibold">{{ $client->phone }}</p>
                </div>
            @endif
            @if($client->client_type === 'company' && $client->company_document)
                <div>
                    <p class="text-sm text-gray-500">CIF/NIF</p>
                    <p class="font-semibold">{{ $client->company_document }}</p>
                </div>
            @endif
            @if($client->client_type === 'individual' && $client->particular_document)
                <div>
                    <p class="text-sm text-gray-500">DNI/NIE</p>
                    <p class="font-semibold">{{ $client->particular_document }}</p>
                </div>
            @endif
            @if($client->default_discount > 0)
                <div>
                    <p class="text-sm text-gray-500">Descuento por defecto</p>
                    <p class="font-semibold">{{ number_format($client->default_discount, 2) }}%</p>
                </div>
            @endif
            @if($client->payment_method)
                <div>
                    <p class="text-sm text-gray-500">Forma de pago</p>
                    <p class="font-semibold">
                        @if($client->payment_method === 'cash') Efectivo
                        @elseif($client->payment_method === 'transfer') Transferencia
                        @elseif($client->payment_method === 'check') Cheque
                        @else Otro
                        @endif
                    </p>
                </div>
            @endif
            @if($client->account_number)
                <div>
                    <p class="text-sm text-gray-500">Número de cuenta</p>
                    <p class="font-semibold">{{ $client->account_number }}</p>
                </div>
            @endif
            @if($client->has_cae && $client->cae_number)
                <div>
                    <p class="text-sm text-gray-500">CAE</p>
                    <p class="font-semibold">{{ $client->cae_number }}</p>
                </div>
            @endif
        </div>
        @if($client->notes)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-500 mb-2">Notas</p>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $client->notes }}</p>
            </div>
        @endif
    </div>

    {{-- Direcciones --}}
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">Direcciones</h3>
            @if($client->addresses && $client->addresses->count() > 0)
                <span class="text-sm text-gray-500">
                    {{ $client->addresses->count() }} {{ $client->addresses->count() === 1 ? 'dirección' : 'direcciones' }}
                </span>
            @endif
        </div>
        
        @if($client->addresses && $client->addresses->count() > 0)
            <div class="space-y-4">
                @foreach($client->addresses as $address)
                    <div class="border-2 rounded-lg p-4 {{ $address->is_default ? 'border-[var(--color-agro-green)] bg-green-50/50' : 'border-gray-200 bg-white' }} hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h4 class="font-bold text-gray-900">
                                        @if($address->description)
                                            {{ $address->description }}
                                        @else
                                            Dirección #{{ $loop->iteration }}
                                        @endif
                                    </h4>
                                    @if($address->is_default)
                                        <span class="px-2 py-0.5 text-xs font-semibold bg-[var(--color-agro-green)] text-white rounded-full">
                                            Por defecto
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="space-y-1 text-sm text-gray-700">
                                    <p class="font-medium">{{ $address->address }}</p>
                                    <div class="flex flex-wrap items-center gap-2 text-gray-600">
                                        @if($address->municipality)
                                            <span>{{ $address->municipality->name }}</span>
                                        @endif
                                        @if($address->province)
                                            <span>{{ $address->province->name }}</span>
                                        @endif
                                        @if($address->autonomousCommunity)
                                            <span class="text-gray-500">({{ $address->autonomousCommunity->name }})</span>
                                        @endif
                                        @if($address->postal_code)
                                            <span class="font-semibold">{{ $address->postal_code }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="ml-4">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="font-medium">No hay direcciones registradas</p>
                <p class="text-sm mt-1">Edita el cliente para agregar direcciones</p>
            </div>
        @endif
    </div>
</div>
