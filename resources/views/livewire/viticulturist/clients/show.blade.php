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

    <div class="glass-card rounded-xl p-6">
        <h3 class="text-lg font-bold mb-4">Información del Cliente</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Tipo</p>
                <p class="font-semibold">{{ $client->client_type === 'company' ? 'Empresa' : 'Particular' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Estado</p>
                <p class="font-semibold">{{ $client->active ? 'Activo' : 'Inactivo' }}</p>
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
        </div>
    </div>
</div>
