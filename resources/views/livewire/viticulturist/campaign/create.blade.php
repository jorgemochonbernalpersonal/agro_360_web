<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    @if(session('message'))
        <flux:callout variant="success">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if(session('error'))
        <flux:callout variant="danger">
            {{ session('error') }}
        </flux:callout>
    @endif

    <!-- Header -->
    <x-agro.page-header
        title="Nueva Campaña"
        description="Crea una nueva campaña vitícola"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.campaign.index') }}" variant="outline" icon="arrow-left" data-cy="back-button">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Formulario -->
    <x-agro.card data-cy="campaign-create-form">
        <form wire:submit="save" class="space-y-8" data-cy="campaign-form">
            <!-- Información Básica -->
            <x-agro.form-section title="Información Básica">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <flux:field>
                        <flux:label>Nombre de la Campaña</flux:label>
                        <flux:input
                            wire:model="name"
                            type="text"
                            id="name"
                            data-cy="campaign-name-input"
                            placeholder="Ej: Campaña 2025"
                            required
                        />
                        <flux:error name="name" />
                    </flux:field>

                    <!-- Año -->
                    <flux:field>
                        <flux:label>Año</flux:label>
                        <flux:input
                            wire:model="year"
                            type="number"
                            min="2000"
                            max="{{ now()->year + 5 }}"
                            id="year"
                            data-cy="campaign-year-input"
                            required
                        />
                        <flux:error name="year" />
                    </flux:field>
                </div>

                <!-- Descripción -->
                <div class="mt-6">
                    <flux:field>
                        <flux:label>Descripción</flux:label>
                        <flux:textarea
                            wire:model="description"
                            id="description"
                            data-cy="campaign-description-input"
                            rows="3"
                            placeholder="Descripción de la campaña..."
                        />
                        <flux:error name="description" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <!-- Período -->
            <x-agro.form-section title="Período de la Campaña">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Fecha Inicio -->
                    <flux:field>
                        <flux:label>Fecha de Inicio</flux:label>
                        <flux:input
                            wire:model="start_date"
                            type="date"
                            id="start_date"
                            data-cy="campaign-start-date-input"
                        />
                        <flux:error name="start_date" />
                    </flux:field>

                    <!-- Fecha Fin -->
                    <flux:field>
                        <flux:label>Fecha de Fin</flux:label>
                        <flux:input
                            wire:model="end_date"
                            type="date"
                            id="end_date"
                            data-cy="campaign-end-date-input"
                        />
                        <flux:error name="end_date" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <!-- Opciones -->
            <x-agro.form-section title="Opciones">
                <div class="flex items-center">
                    <flux:checkbox
                        wire:model="active"
                        id="active"
                        data-cy="campaign-active-checkbox"
                        label="Activar esta campaña automáticamente"
                    />
                </div>
                <p class="mt-2 text-xs text-zinc-500">
                    Si se activa, se desactivarán automáticamente las demás campañas.
                </p>
                <flux:error name="active" />
            </x-agro.form-section>

            <!-- Botones -->
            <x-agro.form-actions cancelUrl="{{ route('viticulturist.campaign.index') }}" submitLabel="Crear Campaña" />
        </form>
    </x-agro.card>
</div>
