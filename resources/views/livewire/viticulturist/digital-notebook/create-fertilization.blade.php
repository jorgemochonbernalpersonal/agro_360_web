@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>';
@endphp

<x-form-card
    title="Registrar Fertilización"
    description="Registra una nueva fertilización en el cuaderno digital"
    :icon="$icon"
    icon-color="from-blue-600 to-blue-700"
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

        <x-form-section title="Información del Fertilizante" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="fertilizer_type">Tipo de Fertilizante</x-label>
                        <x-input wire:model="fertilizer_type" type="text" id="fertilizer_type" placeholder="Ej: Orgánico, Mineral, etc." :error="$errors->first('fertilizer_type')" />
                    </div>
                    <div>
                        <x-label for="fertilizer_name">Nombre del Fertilizante</x-label>
                        <x-input wire:model="fertilizer_name" type="text" id="fertilizer_name" placeholder="Nombre comercial" :error="$errors->first('fertilizer_name')" />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <x-label for="quantity">Cantidad (kg)</x-label>
                        <x-input wire:model="quantity" type="number" step="0.001" id="quantity" placeholder="0.000" :error="$errors->first('quantity')" />
                    </div>
                    <div>
                        <x-label for="npk_ratio">Relación NPK</x-label>
                        <x-input wire:model="npk_ratio" type="text" id="npk_ratio" placeholder="Ej: 10-10-10" :error="$errors->first('npk_ratio')" />
                    </div>
                    <div>
                        <x-label for="area_applied">Área Aplicada (ha)</x-label>
                        <x-input wire:model="area_applied" type="number" step="0.001" id="area_applied" placeholder="0.000" :error="$errors->first('area_applied')" />
                    </div>
                </div>
                <div class="mt-6">
                    <x-label for="application_method">Método de Aplicación</x-label>
                    <x-select wire:model="application_method" id="application_method" :error="$errors->first('application_method')">
                        <option value="">Selecciona un método</option>
                        <option value="aplicación al suelo">Aplicación al Suelo</option>
                        <option value="fertirrigación">Fertirrigación</option>
                        <option value="aplicación foliar">Aplicación Foliar</option>
                        <option value="otro">Otro</option>
                    </x-select>
                </div>
        </x-form-section>

        <x-form-section title="Información Adicional" color="green" class="pb-6">
                <!-- ¿Quién realizó el trabajo? -->
                <div class="mb-6">
                    <x-label class="mb-3 block font-semibold text-gray-700">¿Quién realizó el trabajo?</x-label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Opción: Equipo completo -->
                        <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'crew' ? 'border-[var(--color-agro-green)] bg-[var(--color-agro-green-bg)]' : 'border-gray-200 hover:border-gray-300' }}">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input 
                                    type="radio" 
                                    wire:model.live="workType" 
                                    value="crew" 
                                    class="w-5 h-5 text-[var(--color-agro-green)] focus:ring-[var(--color-agro-green)]"
                                />
                                <div class="flex-1">
                                    <span class="font-semibold text-gray-900">Equipo completo</span>
                                    <p class="text-sm text-gray-500 mt-1">Todo el equipo trabajó en esta actividad</p>
                                </div>
                            </label>
                            @if($workType === 'crew')
                                <div class="mt-4">
                                    <x-label for="crew_id" class="text-sm">Selecciona el equipo</x-label>
                                    <x-select 
                                        wire:model="crew_id" 
                                        id="crew_id"
                                        class="mt-1"
                                        :error="$errors->first('crew_id')"
                                    >
                                        <option value="">Selecciona un equipo</option>
                                        @foreach($crews as $crew)
                                            <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                        @endforeach
                                    </x-select>
                                </div>
                            @endif
                        </div>

                        <!-- Opción: Viticultor individual -->
                        <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'individual' ? 'border-[var(--color-agro-green)] bg-[var(--color-agro-green-bg)]' : 'border-gray-200 hover:border-gray-300' }}">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input 
                                    type="radio" 
                                    wire:model.live="workType" 
                                    value="individual" 
                                    class="w-5 h-5 text-[var(--color-agro-green)] focus:ring-[var(--color-agro-green)]"
                                />
                                <div class="flex-1">
                                    <span class="font-semibold text-gray-900">Viticultor individual</span>
                                    <p class="text-sm text-gray-500 mt-1">Un viticultor específico realizó el trabajo</p>
                                </div>
                            </label>
                            @if($workType === 'individual')
                                <div class="mt-4">
                                    <x-label for="crew_member_id" class="text-sm">Selecciona el viticultor</x-label>
                                    <x-select 
                                        wire:model="crew_member_id" 
                                        id="crew_member_id"
                                        class="mt-1"
                                        :error="$errors->first('crew_member_id')"
                                    >
                                        <option value="">Selecciona un viticultor</option>
                                        @if(isset($allViticulturists))
                                            @foreach($allViticulturists as $viticulturist)
                                                <option value="{{ $viticulturist->id }}">
                                                    {{ $viticulturist->name }} ({{ $viticulturist->email }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </x-select>
                                </div>
                            @endif
                        </div>
                    </div>
                    @error('workType')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Maquinaria -->
                <div>
                    <x-label for="machinery_id">Maquinaria</x-label>
                    <x-select wire:model="machinery_id" id="machinery_id" :error="$errors->first('machinery_id')">
                        <option value="">Sin maquinaria asignada</option>
                        @foreach($machinery as $item)
                            <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                        @endforeach
                    </x-select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <x-label for="weather_conditions">Condiciones Meteorológicas</x-label>
                        <x-input wire:model="weather_conditions" type="text" id="weather_conditions" placeholder="Ej: Soleado, nublado" :error="$errors->first('weather_conditions')" />
                    </div>
                    <div>
                        <x-label for="temperature">Temperatura (°C)</x-label>
                        <x-input wire:model="temperature" type="number" step="0.1" id="temperature" placeholder="20.0" :error="$errors->first('temperature')" />
                    </div>
                </div>
                <div class="mt-6">
                    <x-label for="notes">Notas</x-label>
                    <x-textarea wire:model="notes" id="notes" rows="4" placeholder="Observaciones, comentarios, etc." :error="$errors->first('notes')" />
                </div>
        </x-form-section>

        <x-form-actions 
            :cancel-url="route('viticulturist.digital-notebook')"
            submit-label="Registrar Fertilización"
        />
    </form>
</x-form-card>

