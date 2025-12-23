@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>';
@endphp

<x-form-card
    title="Editar Cuadrilla"
    description="Modifica la información de la cuadrilla"
    :icon="$icon"
    icon-color="from-[var(--color-agro-blue)] to-blue-700"
    :back-url="route('viticulturist.personal.show', $crew)"
>
    <form wire:submit="save" class="space-y-8" data-cy="crew-form">
        <x-form-section title="Información Básica" color="blue">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div>
                        <x-label for="name" required>Nombre de la Cuadrilla</x-label>
                        <x-input 
                            wire:model="name" 
                            type="text" 
                            id="name"
                            data-cy="crew-name-input"
                            :error="$errors->first('name')"
                            required
                        />
                    </div>

                    <!-- Bodega -->
                    @if($wineries->isNotEmpty())
                    <div>
                        <x-label for="winery_id">Bodega <span class="text-gray-500 font-normal">(opcional)</span></x-label>
                        <x-select 
                            wire:model="winery_id" 
                            id="winery_id"
                            data-cy="crew-winery-select"
                            :error="$errors->first('winery_id')"
                        >
                            <option value="">Sin bodega (cuadrilla independiente)</option>
                            @foreach($wineries as $winery)
                                <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    @endif
                </div>

                <!-- Descripción -->
                <div class="mt-6">
                    <x-label for="description">Descripción</x-label>
                    <x-textarea 
                        wire:model="description" 
                        id="description"
                        data-cy="crew-description-input"
                        rows="4"
                        :error="$errors->first('description')"
                    />
                </div>
        </x-form-section>

        <x-form-actions 
            :cancel-url="route('viticulturist.personal.show', $crew)"
            submit-label="Guardar Cambios"
        />
    </form>
</x-form-card>

