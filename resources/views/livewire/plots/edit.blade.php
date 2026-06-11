<div>
<x-agro.form-card :title="__('Editar Parcela')" :description="__('Modifica los datos de la parcela')" :back-url="roleRoute('plots.index')">
    <form wire:submit.prevent="update" class="space-y-8" data-cy="plot-edit-form">
        @error('general')
            <flux:callout variant="danger" icon="x-circle">
                <flux:callout.heading>{{ __('Error') }}</flux:callout.heading>
                <flux:callout.text>{{ $message }}</flux:callout.text>
            </flux:callout>
        @enderror

        <!-- 1. Datos Principales -->
        <x-agro.form-section :title="__('Datos Principales')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if (in_array(auth()->user()->role, ['admin', 'supervisor', 'winery', 'viticulturist', 'producer']))
                    <div class="md:col-span-2">
                        <flux:field>
                            <flux:label for="viticulturist_id">{{ __('Viticultor Asignado') }} *</flux:label>
                            <flux:select wire:model="viticulturist_id" id="viticulturist_id" data-cy="plot-viticulturist-id" required>
                                <option value="">{{ __('Seleccionar...') }}</option>
                                @forelse ($this->viticulturists as $viticulturist)
                                    <option value="{{ $viticulturist->id }}">{{ $viticulturist->name }}</option>
                                @empty
                                    <option value="" disabled>{{ __('No hay viticultores disponibles') }}</option>
                                @endforelse
                            </flux:select>
                            <flux:error name="viticulturist_id" />
                        </flux:field>
                    </div>
                @endif

                <flux:field>
                    <flux:label for="name">{{ __('Nombre de la Parcela') }} *</flux:label>
                    <flux:input wire:model="name" type="text" id="name" data-cy="plot-name" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label for="area">{{ __('Área (hectáreas)') }} *</flux:label>
                    <flux:input wire:model="area" type="number" step="0.001" min="0.001" id="area" data-cy="plot-area" />
                    <flux:error name="area" />
                </flux:field>

                <flux:field>
                    <flux:label for="property_type_id">{{ __('Régimen de Tenencia') }}</flux:label>
                    <flux:select wire:model="property_type_id" id="property_type_id">
                        <option value="">{{ __('Sin especificar') }}</option>
                        @foreach ($propertyTypes as $pt)
                            <option value="{{ $pt->id }}">{{ __($pt->name) }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="property_type_id" />
                </flux:field>

                <div class="md:col-span-2">
                    <flux:field>
                        <flux:label for="description">{{ __('Descripción') }}</flux:label>
                        <flux:textarea wire:model="description" id="description" data-cy="plot-description" rows="3" />
                        <flux:error name="description" />
                    </flux:field>
                </div>
            </div>
        </x-agro.form-section>

        <!-- 2. Ubicacion -->
        <x-agro.form-section :title="__('Ubicación')">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6"
                x-data="{
                    communityId: '{{ $autonomous_community_id }}',
                    provinceId: '{{ $province_id }}',
                    municipalityId: '{{ $municipality_id }}',
                    municipalities: @js($initMunicipalities),
                    allProvinces: @js($allProvinces),
                    get filteredProvinces() {
                        if (!this.communityId) return [];
                        return this.allProvinces.filter(p => String(p.autonomous_community_id) === String(this.communityId));
                    },
                    onCommunityChange(value) {
                        this.communityId = value;
                        this.provinceId = '';
                        this.municipalityId = '';
                        this.municipalities = [];
                        this.$nextTick(() => {
                            const p = document.getElementById('province_id');
                            if (p) { p.value = ''; p.dispatchEvent(new Event('change', { bubbles: true })); }
                        });
                    },
                    async onProvinceChange(value) {
                        this.provinceId = value;
                        this.municipalityId = '';
                        this.municipalities = [];
                        this.$nextTick(() => {
                            const m = document.getElementById('municipality_id');
                            if (m) { m.value = ''; m.dispatchEvent(new Event('change', { bubbles: true })); }
                        });
                        if (value) {
                            this.municipalities = await $wire.getMunicipalities(value);
                        }
                    }
                }">
                <flux:field>
                    <flux:label for="autonomous_community_id">{{ __('Comunidad Autónoma') }} *</flux:label>
                    <flux:select wire:model="autonomous_community_id" id="autonomous_community_id"
                        data-cy="plot-autonomous-community-id" required
                        x-on:change="onCommunityChange($event.target.value)">
                        <option value="">{{ __('Seleccionar...') }}</option>
                        @foreach ($autonomousCommunities as $community)
                            <option value="{{ $community->id }}">{{ $community->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="autonomous_community_id" />
                </flux:field>

                <flux:field>
                    <flux:label for="province_id">{{ __('Provincia') }} *</flux:label>
                    <flux:select wire:model="province_id" id="province_id" data-cy="plot-province-id" required
                        x-bind:disabled="!communityId"
                        x-on:change="onProvinceChange($event.target.value)"
                        x-init="$nextTick(() => { if (provinceId) $el.value = provinceId })">
                        <option value="">{{ __('Seleccionar...') }}</option>
                        <template x-for="province in filteredProvinces" :key="province.id">
                            <option :value="province.id" x-text="province.name" :selected="String(province.id) === String(provinceId)"></option>
                        </template>
                    </flux:select>
                    <flux:error name="province_id" />
                </flux:field>

                <flux:field>
                    <flux:label for="municipality_id">{{ __('Municipio') }} *</flux:label>
                    <flux:select wire:model="municipality_id" id="municipality_id" data-cy="plot-municipality-id" required
                        x-bind:disabled="!provinceId"
                        x-init="$nextTick(() => { if (municipalityId) $el.value = municipalityId })">
                        <option value="">{{ __('Seleccionar...') }}</option>
                        <template x-for="mun in municipalities" :key="mun.id">
                            <option :value="mun.id" x-text="mun.name" :selected="String(mun.id) === String(municipalityId)"></option>
                        </template>
                    </flux:select>
                    <flux:error name="municipality_id" />
                </flux:field>

                <flux:field>
                    <flux:label for="site_id">{{ __('Paraje') }}</flux:label>
                    <flux:select wire:model="site_id" id="site_id">
                        <option value="">{{ __('Sin especificar') }}</option>
                        @foreach ($sites as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="site_id" />
                </flux:field>

                <flux:field>
                    <flux:label for="valley_id">{{ __('Valle / Zona') }}</flux:label>
                    <flux:select wire:model="valley_id" id="valley_id">
                        <option value="">{{ __('Sin especificar') }}</option>
                        @foreach ($valleys as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="valley_id" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <!-- 3. Identificacion Catastral -->
        <x-agro.form-section :title="__('Identificación Catastral')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label for="code_parcel">{{ __('Código de Parcela Catastral') }}</flux:label>
                    <flux:input wire:model="code_parcel" type="text" id="code_parcel" :placeholder="__('Ej: 14-023-A-001-0001')" />
                    <flux:error name="code_parcel" />
                </flux:field>
                <flux:field>
                    <flux:label for="enclosure">{{ __('Recinto / Enclave') }}</flux:label>
                    <flux:input wire:model="enclosure" type="text" id="enclosure" :placeholder="__('Ref. recinto')" />
                    <flux:error name="enclosure" />
                </flux:field>
                <flux:field>
                    <flux:label for="cadastral_area">{{ __('Superficie Catastral (ha)') }}</flux:label>
                    <flux:input wire:model="cadastral_area" type="number" step="0.001" min="0" id="cadastral_area" placeholder="0.000" />
                    <flux:description>{{ __('Superficie según el catastro (puede diferir del área agrícola)') }}</flux:description>
                    <flux:error name="cadastral_area" />
                </flux:field>
                <flux:field>
                    <flux:label for="pac_eligible_area">{{ __('Superficie Admisible PAC (ha)') }}</flux:label>
                    <flux:input wire:model="pac_eligible_area" type="number" step="0.001" min="0" id="pac_eligible_area" placeholder="0.000" />
                    <flux:description>{{ __('Superficie reconocida como elegible para ayudas PAC (FEGA)') }}</flux:description>
                    <flux:error name="pac_eligible_area" />
                </flux:field>
                <flux:field>
                    <flux:label for="non_eligible_area">{{ __('Superficie No Admisible (ha)') }}</flux:label>
                    <flux:input wire:model="non_eligible_area" type="number" step="0.001" min="0" id="non_eligible_area" placeholder="0.000" />
                    <flux:description>{{ __('Caminos, construcciones, linderos excluidos de la PAC') }}</flux:description>
                    <flux:error name="non_eligible_area" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <!-- 4. Caracteristicas de la Parcela -->
        <x-agro.form-section :title="__('Características de la Parcela')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label for="soil_type_id">{{ __('Tipo de Suelo') }}</flux:label>
                    <flux:select wire:model="soil_type_id" id="soil_type_id">
                        <option value="">{{ __('Sin especificar') }}</option>
                        @foreach ($soilTypes as $st)
                            <option value="{{ $st->id }}">{{ __($st->name) }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="soil_type_id" />
                </flux:field>
                <flux:field>
                    <flux:label for="topography_id">{{ __('Topografía') }}</flux:label>
                    <flux:select wire:model="topography_id" id="topography_id">
                        <option value="">{{ __('Sin especificar') }}</option>
                        @foreach ($topographies as $t)
                            <option value="{{ $t->id }}">{{ __($t->name) }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="topography_id" />
                </flux:field>
                <flux:field>
                    <flux:label for="orientation_id">{{ __('Orientación') }}</flux:label>
                    <flux:select wire:model="orientation_id" id="orientation_id">
                        <option value="">{{ __('Sin especificar') }}</option>
                        @foreach ($orientations as $o)
                            <option value="{{ $o->id }}">{{ __($o->name) }} ({{ $o->abbreviation }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="orientation_id" />
                </flux:field>
                <flux:field>
                    <flux:label for="slope">{{ __('Pendiente (%)') }}</flux:label>
                    <flux:input wire:model="slope" type="number" step="0.01" min="0" max="100" id="slope" :placeholder="__('Ej: 12.5')" />
                    <flux:error name="slope" />
                </flux:field>
                <flux:field>
                    <flux:label for="irrigation_type_id">{{ __('Tipo de Riego') }}</flux:label>
                    <flux:select wire:model="irrigation_type_id" id="irrigation_type_id">
                        <option value="">{{ __('Sin especificar') }}</option>
                        @foreach ($irrigationTypes as $it)
                            <option value="{{ $it->id }}">{{ __($it->name) }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="irrigation_type_id" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <!-- 5. Cultivo -->
        <x-agro.form-section :title="__('Cultivo')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label for="planting_pattern">{{ __('Marco de Plantación') }}</flux:label>
                    <flux:input wire:model="planting_pattern" type="text" id="planting_pattern" :placeholder="__('Ej: 2.5x1.2 tresbolillo')" />
                    <flux:error name="planting_pattern" />
                </flux:field>
                <div class="md:col-span-2 mt-1">
                    <flux:checkbox wire:model="is_organic" id="is_organic" :label="__('Producción Ecológica')" :description="__('La parcela está bajo un programa de agricultura ecológica certificada.')" />
                </div>
            </div>
            <p class="text-xs text-zinc-500 mt-3">{{ __('El año de plantación, sistema de conducción y número de cepas se gestionan en cada plantación.') }}</p>
        </x-agro.form-section>

        <!-- 6. Avanzado -->
        <x-agro.form-section :title="__('Avanzado')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label for="degree_day_base">{{ __('Temperatura Base Grados-Día (°C)') }}</flux:label>
                    <flux:input wire:model="degree_day_base" type="number" step="0.1" min="0" max="30" id="degree_day_base" placeholder="10.0" />
                    <flux:description>{{ __('Por defecto 10 °C (estándar vitícola)') }}</flux:description>
                    <flux:error name="degree_day_base" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :cancel-url="roleRoute('plots.index')" :submit-label="__('Actualizar Parcela')" />
    </form>
</x-agro.form-card>

{{-- Modal de confirmación: cambio de municipio con SIGPAC vinculado --}}
@if($showSigpacWarning)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                <flux:icon icon="exclamation-triangle" class="w-5 h-5 text-amber-600" />
            </div>
            <div class="flex-1">
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ __('Cambio de municipio con SIGPAC vinculado') }}
                </h3>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Esta parcela tiene geometrías SIGPAC asociadas al municipio actual.') }}
                    {{ __('Al cambiar de municipio,') }} <strong>{{ __('todos los vínculos SIGPAC se eliminarán') }}</strong>
                    {{ __('y deberás asignar nuevos códigos para el nuevo municipio.') }}
                </p>
                <p class="mt-2 text-sm font-medium text-amber-700 dark:text-amber-400">
                    {{ __('¿Deseas continuar?') }}
                </p>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <flux:button variant="ghost" wire:click="$set('showSigpacWarning', false)">
                {{ __('Cancelar') }}
            </flux:button>
            <flux:button variant="danger" wire:click="confirmUpdate" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="confirmUpdate">{{ __('Sí, cambiar municipio') }}</span>
                <span wire:loading wire:target="confirmUpdate">{{ __('Guardando...') }}</span>
            </flux:button>
        </div>
    </div>
</div>
@endif
</div>
