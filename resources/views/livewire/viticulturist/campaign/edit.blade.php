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
        :title="__('Editar Campaña')"
        :description="__('Modifica los datos de la campaña') . ' ' . $campaign->name"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.campaign.index') }}" variant="outline" icon="arrow-left">
                {{ __('Volver') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Formulario -->
    <x-agro.card data-cy="campaign-edit-form">
        <form wire:submit="save" class="space-y-8" data-cy="campaign-form">
            <!-- Información Básica -->
            <x-agro.form-section :title="__('Información Básica')">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <flux:field>
                        <flux:label>{{ __('Nombre de la Campaña') }}</flux:label>
                        <flux:input
                            wire:model="name"
                            value="{{ $name }}"
                            type="text"
                            id="name"
                            data-cy="campaign-name-input"
                            :placeholder="__('Ej: Campaña 2025')"
                            required
                        />
                        <flux:error name="name" />
                    </flux:field>

                    <!-- Año -->
                    <flux:field>
                        <flux:label>{{ __('Año') }}</flux:label>
                        <flux:input
                            wire:model="year"
                            value="{{ $year }}"
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
                        <flux:label>{{ __('Descripción') }}</flux:label>
                        <flux:textarea
                            wire:model="description"
                            id="description"
                            data-cy="campaign-description-input"
                            rows="3"
                            :placeholder="__('Descripción de la campaña...')"
                        >{{ $description }}</flux:textarea>
                        <flux:error name="description" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <!-- Período -->
            <x-agro.form-section :title="__('Período de la Campaña')">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Fecha Inicio -->
                    <flux:field>
                        <flux:label>{{ __('Fecha de Inicio') }}</flux:label>
                        <flux:input
                            wire:model="start_date"
                            value="{{ $start_date }}"
                            type="date"
                            id="start_date"
                            data-cy="campaign-start-date-input"
                        />
                        <flux:error name="start_date" />
                    </flux:field>

                    <!-- Fecha Fin -->
                    <flux:field>
                        <flux:label>{{ __('Fecha de Fin') }}</flux:label>
                        <flux:input
                            wire:model="end_date"
                            value="{{ $end_date }}"
                            type="date"
                            id="end_date"
                            data-cy="campaign-end-date-input"
                        />
                        <flux:error name="end_date" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <!-- Opciones -->
            <x-agro.form-section :title="__('Opciones')">
                <div class="flex items-center">
                    <flux:checkbox
                        wire:model="active"
                        :checked="$active"
                        id="active"
                        data-cy="campaign-active-checkbox"
                        :label="__('Activar esta campaña')"
                    />
                </div>
                <p class="mt-2 text-xs text-zinc-500">
                    {{ __('Si se activa, se desactivarán automáticamente las demás campañas.') }}
                </p>
                <flux:error name="active" />
            </x-agro.form-section>

            <!-- Botones -->
            <x-agro.form-actions :cancelUrl="roleRoute('viticulturist.campaign.index')" :submitLabel="__('Guardar Cambios')" />
        </form>
    </x-agro.card>
</div>
