<div>
<x-agro.form-card title="Editar Parcela" description="Modifica los datos de la parcela" :back-url="auth()->user()->isWinery() ? route('winery.plots.index') : route('plots.index')">
    <form wire:submit.prevent="update" class="space-y-8" data-cy="plot-edit-form">
        @error('general')
            <flux:callout variant="danger" icon="x-circle">
                <flux:callout.heading>Error</flux:callout.heading>
                <flux:callout.text>{{ $message }}</flux:callout.text>
            </flux:callout>
        @enderror

        <!-- 1. Datos Principales -->
        <x-agro.form-section title="Datos Principales">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if (in_array(auth()->user()->role, ['admin', 'supervisor', 'winery', 'viticulturist']))
                    <div class="md:col-span-2">
                        <flux:field>
                            <flux:label for="viticulturist_id">Viticultor Asignado *</flux:label>
                            <flux:select wire:model="viticulturist_id" id="viticulturist_id" data-cy="plot-viticulturist-id" required>
                                <option value="">Seleccionar...</option>
                                @forelse ($this->viticulturists as $viticulturist)
                                    <option value="{{ $viticulturist->id }}">{{ $viticulturist->name }}</option>
                                @empty
                                    <option value="" disabled>No hay viticultores disponibles</option>
                                @endforelse
                            </flux:select>
                            <flux:error name="viticulturist_id" />
                        </flux:field>
                    </div>
                @endif

                <flux:field>
                    <flux:label for="name">Nombre de la Parcela *</flux:label>
                    <flux:input wire:model="name" type="text" id="name" data-cy="plot-name" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label for="area">Área (hectáreas) *</flux:label>
                    <flux:input wire:model="area" type="number" step="0.001" min="0.001" id="area" data-cy="plot-area" />
                    <flux:error name="area" />
                </flux:field>

                <flux:field>
                    <flux:label for="property_type_id">Régimen de Tenencia</flux:label>
                    <flux:select wire:model="property_type_id" id="property_type_id">
                        <option value="">Sin especificar</option>
                        @foreach ($propertyTypes as $pt)
                            <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="property_type_id" />
                </flux:field>

                <div class="md:col-span-2">
                    <flux:field>
                        <flux:label for="description">Descripción</flux:label>
                        <flux:textarea wire:model="description" id="description" data-cy="plot-description" rows="3" />
                        <flux:error name="description" />
                    </flux:field>
                </div>
            </div>
        </x-agro.form-section>

        <!-- 2. Ubicacion -->
        <x-agro.form-section title="Ubicación">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6"
                x-data="{
                    provinces: @js($provinces->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values()),
                    municipalities: @js($municipalities->map(fn($m) => ['id' => $m->id, 'name' => $m->name])->values()),
                    async communityChanged(id) {
                        this.provinces = [];
                        this.municipalities = [];
                        if (!id) return;
                        this.provinces = await $wire.fetchProvinces(Number(id));
                    },
                    async provinceChanged(id) {
                        this.municipalities = [];
                        if (!id) return;
                        this.municipalities = await $wire.fetchMunicipalities(Number(id));
                    }
                }">
                <flux:field>
                    <flux:label for="autonomous_community_id">Comunidad Autónoma *</flux:label>
                    <flux:select wire:model="autonomous_community_id" id="autonomous_community_id"
                        data-cy="plot-autonomous-community-id" required
                        x-on:change="communityChanged($event.target.value)">
                        <option value="">Seleccionar...</option>
                        @foreach ($autonomousCommunities as $community)
                            <option value="{{ $community->id }}">{{ $community->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="autonomous_community_id" />
                </flux:field>

                <flux:field>
                    <flux:label for="province_id">Provincia *</flux:label>
                    <flux:select wire:model="province_id" wire:ignore id="province_id" data-cy="plot-province-id" required
                        x-bind:disabled="provinces.length === 0"
                        x-on:change="provinceChanged($event.target.value)"
                        x-init="$nextTick(() => { $el.value = '{{ $province_id }}'; }); $watch('provinces', () => $nextTick(() => { $el.value = ''; }));">
                        <option value="">Seleccionar...</option>
                        <template x-for="province in provinces" :key="province.id">
                            <option :value="province.id" x-text="province.name"></option>
                        </template>
                    </flux:select>
                    <flux:error name="province_id" />
                </flux:field>

                <flux:field>
                    <flux:label for="municipality_id">Municipio *</flux:label>
                    <flux:select wire:model="municipality_id" wire:ignore id="municipality_id" data-cy="plot-municipality-id" required
                        x-bind:disabled="municipalities.length === 0"
                        x-on:change="$wire.selectMunicipality(Number($event.target.value))"
                        x-init="$nextTick(() => { $el.value = '{{ $municipality_id }}'; }); $watch('municipalities', () => $nextTick(() => { $el.value = ''; }));">
                        <option value="">Seleccionar...</option>
                        <template x-for="municipality in municipalities" :key="municipality.id">
                            <option :value="municipality.id" x-text="municipality.name"></option>
                        </template>
                    </flux:select>
                    <flux:error name="municipality_id" />
                </flux:field>

                <flux:field>
                    <flux:label for="site_id">Paraje</flux:label>
                    <flux:select wire:model="site_id" id="site_id">
                        <option value="">Sin especificar</option>
                        @foreach ($sites as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="site_id" />
                </flux:field>

                <flux:field>
                    <flux:label for="valley_id">Valle / Zona</flux:label>
                    <flux:select wire:model="valley_id" id="valley_id">
                        <option value="">Sin especificar</option>
                        @foreach ($valleys as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="valley_id" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <!-- 3. Identificacion Catastral -->
        <x-agro.form-section title="Identificación Catastral">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label for="code_parcel">Código de Parcela Catastral</flux:label>
                    <flux:input wire:model="code_parcel" type="text" id="code_parcel" placeholder="Ej: 14-023-A-001-0001" />
                    <flux:error name="code_parcel" />
                </flux:field>
                <flux:field>
                    <flux:label for="enclosure">Recinto / Enclave</flux:label>
                    <flux:input wire:model="enclosure" type="text" id="enclosure" placeholder="Ref. recinto" />
                    <flux:error name="enclosure" />
                </flux:field>
                <flux:field>
                    <flux:label for="cadastral_area">Superficie Catastral (ha)</flux:label>
                    <flux:input wire:model="cadastral_area" type="number" step="0.001" min="0" id="cadastral_area" placeholder="0.000" />
                    <flux:description>Superficie según el catastro (puede diferir del área agrícola)</flux:description>
                    <flux:error name="cadastral_area" />
                </flux:field>
                <flux:field>
                    <flux:label for="pac_eligible_area">Superficie Admisible PAC (ha)</flux:label>
                    <flux:input wire:model="pac_eligible_area" type="number" step="0.001" min="0" id="pac_eligible_area" placeholder="0.000" />
                    <flux:description>Superficie reconocida como elegible para ayudas PAC (FEGA)</flux:description>
                    <flux:error name="pac_eligible_area" />
                </flux:field>
                <flux:field>
                    <flux:label for="non_eligible_area">Superficie No Admisible (ha)</flux:label>
                    <flux:input wire:model="non_eligible_area" type="number" step="0.001" min="0" id="non_eligible_area" placeholder="0.000" />
                    <flux:description>Caminos, construcciones, linderos excluidos de la PAC</flux:description>
                    <flux:error name="non_eligible_area" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <!-- 4. Caracteristicas de la Parcela -->
        <x-agro.form-section title="Características de la Parcela">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label for="soil_type_id">Tipo de Suelo</flux:label>
                    <flux:select wire:model="soil_type_id" id="soil_type_id">
                        <option value="">Sin especificar</option>
                        @foreach ($soilTypes as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="soil_type_id" />
                </flux:field>
                <flux:field>
                    <flux:label for="topography_id">Topografía</flux:label>
                    <flux:select wire:model="topography_id" id="topography_id">
                        <option value="">Sin especificar</option>
                        @foreach ($topographies as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="topography_id" />
                </flux:field>
                <flux:field>
                    <flux:label for="orientation_id">Orientación</flux:label>
                    <flux:select wire:model="orientation_id" id="orientation_id">
                        <option value="">Sin especificar</option>
                        @foreach ($orientations as $o)
                            <option value="{{ $o->id }}">{{ $o->name }} ({{ $o->abbreviation }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="orientation_id" />
                </flux:field>
                <flux:field>
                    <flux:label for="slope">Pendiente (%)</flux:label>
                    <flux:input wire:model="slope" type="number" step="0.01" min="0" max="100" id="slope" placeholder="Ej: 12.5" />
                    <flux:error name="slope" />
                </flux:field>
                <flux:field>
                    <flux:label for="irrigation_type_id">Tipo de Riego</flux:label>
                    <flux:select wire:model="irrigation_type_id" id="irrigation_type_id">
                        <option value="">Sin especificar</option>
                        @foreach ($irrigationTypes as $it)
                            <option value="{{ $it->id }}">{{ $it->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="irrigation_type_id" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <!-- 5. Plantacion y Cultivo -->
        <x-agro.form-section title="Plantación y Cultivo">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label for="plantation_year">Año de Plantación</flux:label>
                    <flux:input wire:model="plantation_year" type="number" min="1800" max="{{ date('Y') }}" id="plantation_year" placeholder="Ej: 1985" />
                    <flux:error name="plantation_year" />
                </flux:field>
                <flux:field>
                    <flux:label for="number_of_vines">Número de Cepas</flux:label>
                    <flux:input wire:model="number_of_vines" type="number" min="0" id="number_of_vines" placeholder="Ej: 2500" />
                    <flux:error name="number_of_vines" />
                </flux:field>
                <flux:field>
                    <flux:label for="training_system_id">Sistema de Conducción</flux:label>
                    <flux:select wire:model="training_system_id" id="training_system_id">
                        <option value="">Sin especificar</option>
                        @foreach ($trainingSystems as $ts)
                            <option value="{{ $ts->id }}">{{ $ts->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="training_system_id" />
                </flux:field>
                <flux:field>
                    <flux:label for="planting_pattern">Marco de Plantación</flux:label>
                    <flux:input wire:model="planting_pattern" type="text" id="planting_pattern" placeholder="Ej: 2.5x1.2 tresbolillo" />
                    <flux:error name="planting_pattern" />
                </flux:field>
                <div class="md:col-span-2 mt-1">
                    <flux:checkbox wire:model="is_organic" id="is_organic" label="Producción Ecológica" description="La parcela está bajo un programa de agricultura ecológica certificada." />
                </div>
            </div>
        </x-agro.form-section>

        <!-- 6. Avanzado -->
        <x-agro.form-section title="Avanzado">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label for="degree_day_base">Temperatura Base Grados-Día (°C)</flux:label>
                    <flux:input wire:model="degree_day_base" type="number" step="0.1" min="0" max="30" id="degree_day_base" placeholder="10.0" />
                    <flux:description>Por defecto 10 °C (estándar vitícola)</flux:description>
                    <flux:error name="degree_day_base" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :cancel-url="auth()->user()->isWinery() ? route('winery.plots.index') : route('plots.index')" submit-label="Actualizar Parcela" />
    </form>
</x-agro.form-card>
</div>
