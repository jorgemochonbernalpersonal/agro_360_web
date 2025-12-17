@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
@endphp

<x-form-card
    title="Registrar Labor Cultural"
    description="Registra una nueva labor cultural en el cuaderno digital"
    :icon="$icon"
    icon-color="from-purple-600 to-purple-700"
    :back-url="route('viticulturist.digital-notebook')"
>
    <form wire:submit="save" class="space-y-8">
        <x-form-section title="Información Básica" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="plot_id" required>Parcela</x-label>
                        <x-select wire:model="plot_id" id="plot_id" :error="$errors->first('plot_id')" required>
                            <option value="">Selecciona una parcela</option>
                            @foreach($plots as $plot)
                                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <div>
                        <x-label for="activity_date" required>Fecha</x-label>
                        <x-input wire:model="activity_date" type="date" id="activity_date" :error="$errors->first('activity_date')" required />
                    </div>
                </div>
        </x-form-section>

        <x-form-section title="Información de la Labor" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="work_type">Tipo de Labor</x-label>
                        <x-select wire:model="work_type" id="work_type" :error="$errors->first('work_type')">
                            <option value="">Selecciona un tipo</option>
                            <option value="poda">Poda</option>
                            <option value="deshojado">Deshojado</option>
                            <option value="despuntado">Despuntado</option>
                            <option value="vendimia">Vendimia</option>
                            <option value="laboreo">Laboreo</option>
                            <option value="desbroce">Desbroce</option>
                            <option value="otro">Otro</option>
                        </x-select>
                    </div>
                    <div>
                        <x-label for="workers_count">Número de Trabajadores</x-label>
                        <x-input wire:model="workers_count" type="number" min="1" id="workers_count" placeholder="0" :error="$errors->first('workers_count')" />
                    </div>
                </div>
                <div class="mt-6">
                    <x-label for="hours_worked">Horas Trabajadas</x-label>
                    <x-input wire:model="hours_worked" type="number" step="0.5" min="0" id="hours_worked" placeholder="0.0" :error="$errors->first('hours_worked')" />
                </div>
                <div class="mt-6">
                    <x-label for="description">Descripción</x-label>
                    <x-textarea wire:model="description" id="description" rows="4" placeholder="Descripción detallada de la labor realizada..." :error="$errors->first('description')" />
                </div>
        </x-form-section>

        <x-form-section title="Información Adicional" color="green" class="pb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <x-label for="crew_id">Cuadrilla</x-label>
                        <x-select wire:model="crew_id" id="crew_id" :error="$errors->first('crew_id')">
                            <option value="">Sin cuadrilla asignada</option>
                            @foreach($crews as $crew)
                                <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <div>
                        <x-label for="machinery_id">Maquinaria</x-label>
                        <x-select wire:model="machinery_id" id="machinery_id" :error="$errors->first('machinery_id')">
                            <option value="">Sin maquinaria asignada</option>
                            @foreach($machinery as $item)
                                <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                            @endforeach
                        </x-select>
                    </div>
                    <div>
                        <x-label for="weather_conditions">Condiciones Meteorológicas</x-label>
                        <x-input wire:model="weather_conditions" type="text" id="weather_conditions" placeholder="Ej: Soleado, nublado" :error="$errors->first('weather_conditions')" />
                    </div>
                </div>
                <div class="mt-6">
                    <x-label for="temperature">Temperatura (°C)</x-label>
                    <x-input wire:model="temperature" type="number" step="0.1" id="temperature" placeholder="20.0" :error="$errors->first('temperature')" />
                </div>
                <div class="mt-6">
                    <x-label for="notes">Notas</x-label>
                    <x-textarea wire:model="notes" id="notes" rows="4" placeholder="Observaciones, comentarios, etc." :error="$errors->first('notes')" />
                </div>
        </x-form-section>

        <x-form-actions 
            :cancel-url="route('viticulturist.digital-notebook')"
            submit-label="Registrar Labor"
        />
    </form>
</x-form-card>

