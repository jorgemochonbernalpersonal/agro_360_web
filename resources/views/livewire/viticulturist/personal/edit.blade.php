<x-agro.form-card
    title="Editar Cuadrilla"
    description="Modifica la informacion de la cuadrilla"
    :back-url="route('viticulturist.personal.show', $crew)"
>
    <form wire:submit="save" class="space-y-8" data-cy="crew-form">
        <x-agro.form-section title="Informacion Basica">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <flux:field>
                        <flux:label>Nombre de la Cuadrilla *</flux:label>
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
                        <flux:label>Bodega <span class="text-zinc-500 font-normal">(opcional)</span></flux:label>
                        <flux:select
                            wire:model="winery_id"
                            id="winery_id"
                            data-cy="crew-winery-select"
                        >
                            <option value="">Sin bodega (cuadrilla independiente)</option>
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
                        <flux:label>Descripcion</flux:label>
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
            :cancel-url="route('viticulturist.personal.show', $crew)"
            submit-label="Guardar Cambios"
        />
    </form>
</x-agro.form-card>
