<x-agro.form-card
    :title="__('Editar Cuadrilla')"
    :description="__('Modifica la información de la cuadrilla')"
    :back-url="roleRoute('viticulturist.personal.show', $crew)"
>
    <form wire:submit="save" class="space-y-8" data-cy="crew-form">
        <x-agro.form-section :title="__('Información Básica')">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <flux:field>
                        <flux:label>{{ __('Nombre de la Cuadrilla') }} *</flux:label>
                        <flux:input
                            wire:model="name"
                            type="text"
                            id="name"
                            data-cy="crew-name-input"
                            required
                        />
                        <flux:error name="name" />
                    </flux:field>

                    <!-- Bodega -->
                    @if($wineries->isNotEmpty())
                    <flux:field>
                        <flux:label>{{ __('Bodega') }} <span class="text-zinc-500 font-normal">({{ __('opcional') }})</span></flux:label>
                        <flux:select
                            wire:model="winery_id"
                            id="winery_id"
                            data-cy="crew-winery-select"
                        >
                            <option value="">{{ __('Sin bodega (cuadrilla independiente)') }}</option>
                            @foreach($wineries as $winery)
                                <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="winery_id" />
                    </flux:field>
                    @endif
                </div>

                <!-- Descripcion -->
                <div class="mt-6">
                    <flux:field>
                        <flux:label>{{ __('Descripción') }}</flux:label>
                        <flux:textarea
                            wire:model="description"
                            id="description"
                            data-cy="crew-description-input"
                            rows="4"
                        />
                        <flux:error name="description" />
                    </flux:field>
                </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.personal.show', $crew)"
            :submit-label="__('Guardar Cambios')"
        />
    </form>
</x-agro.form-card>
