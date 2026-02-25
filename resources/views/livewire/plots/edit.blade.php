<div>
<x-agro.form-card title="Editar Parcela" description="Modifica los datos de la parcela" :back-url="route('plots.index')">
    <form wire:submit.prevent="update" class="space-y-8" data-cy="plot-edit-form">
        @error('general')
            <flux:callout variant="danger" icon="x-circle">
                <flux:callout.heading>Error</flux:callout.heading>
                <flux:callout.text>{{ $message }}</flux:callout.text>
            </flux:callout>
        @enderror

        <!-- 1. Informacion Basica -->
        <x-agro.form-section title="Informacion Basica">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label for="name">Nombre de la Parcela *</flux:label>
                    <flux:input wire:model="name" type="text" id="name" data-cy="plot-name" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label for="area">Area (hectareas)</flux:label>
                    <flux:input wire:model="area" type="number" step="0.001" id="area" data-cy="plot-area" />
                    <flux:error name="area" />
                </flux:field>

                <flux:field>
                    <flux:label for="tenure_regime">Régimen de Tenencia *</flux:label>
                    <flux:select wire:model="tenure_regime" id="tenure_regime" required>
                        <option value="propiedad">Propiedad</option>
                        <option value="arrendamiento">Arrendamiento</option>
                        <option value="aparceria">Aparcería</option>
                        <option value="cesion_uso">Cesión de uso</option>
                        <option value="otros">Otros</option>
                    </flux:select>
                    <flux:error name="tenure_regime" />
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label for="description">Descripcion</flux:label>
                    <flux:textarea wire:model="description" id="description" data-cy="plot-description" rows="3" />
                    <flux:error name="description" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <!-- 2. Ubicacion -->
        <x-agro.form-section title="Ubicacion">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label for="autonomous_community_id">Comunidad Autonoma *</flux:label>
                    <flux:select wire:model.live="autonomous_community_id" id="autonomous_community_id" data-cy="plot-autonomous-community-id" required>
                        <option value="">Seleccionar...</option>
                        @foreach ($autonomousCommunities as $community)
                            <option value="{{ $community->id }}">{{ $community->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="autonomous_community_id" />
                </flux:field>

                <flux:field>
                    <flux:label for="province_id">Provincia *</flux:label>
                    <flux:select wire:model.live="province_id" id="province_id" data-cy="plot-province-id" required
                        :disabled="!$autonomous_community_id">
                        <option value="">Seleccionar...</option>
                        @foreach ($provinces as $province)
                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="province_id" />
                </flux:field>

                <div wire:key="municipality-wrapper-{{ $province_id }}">
                    <flux:field>
                        <flux:label for="municipality_id">Municipio *</flux:label>
                        <flux:select wire:model="municipality_id" id="municipality_id" data-cy="plot-municipality-id" required
                            :disabled="!$province_id">
                            <option value="">Seleccionar...</option>
                            @foreach ($municipalities as $municipality)
                                <option value="{{ $municipality->id }}">{{ $municipality->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="municipality_id" />
                    </flux:field>
                </div>
            </div>
        </x-agro.form-section>

        <!-- 3. Identificacion Catastral -->
        <x-agro.form-section title="Identificacion Catastral">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label for="site_name">Paraje</flux:label>
                    <flux:input wire:model="site_name" type="text" id="site_name" placeholder="Ej: El Cerro" />
                    <flux:error name="site_name" />
                </flux:field>
                <flux:field>
                    <flux:label for="valley">Valle / Zona</flux:label>
                    <flux:input wire:model="valley" type="text" id="valley" placeholder="Ej: Valle del Ebro" />
                    <flux:error name="valley" />
                </flux:field>
                <flux:field>
                    <flux:label for="code_parcel">Codigo de Parcela Catastral</flux:label>
                    <flux:input wire:model="code_parcel" type="text" id="code_parcel" placeholder="Ej: 14-023-A-001-0001" />
                    <flux:error name="code_parcel" />
                </flux:field>

                <flux:field>
                    <flux:label for="soil_type">Tipo de Suelo</flux:label>
                    <flux:select wire:model="soil_type" id="soil_type">
                        <option value="">Sin especificar</option>
                        <option value="arenoso">Arenoso</option>
                        <option value="arcilloso">Arcilloso</option>
                        <option value="limoso">Limoso</option>
                        <option value="franco">Franco</option>
                        <option value="franco-arenoso">Franco-arenoso</option>
                        <option value="franco-arcilloso">Franco-arcilloso</option>
                        <option value="franco-limoso">Franco-limoso</option>
                        <option value="pedregoso">Pedregoso</option>
                    </flux:select>
                    <flux:error name="soil_type" />
                </flux:field>

                <flux:field>
                    <flux:label for="orientation">Orientación</flux:label>
                    <flux:select wire:model="orientation" id="orientation">
                        <option value="">Sin especificar</option>
                        <option value="N">Norte (N)</option>
                        <option value="NE">Noreste (NE)</option>
                        <option value="E">Este (E)</option>
                        <option value="SE">Sureste (SE)</option>
                        <option value="S">Sur (S)</option>
                        <option value="SO">Suroeste (SO)</option>
                        <option value="O">Oeste (O)</option>
                        <option value="NO">Noroeste (NO)</option>
                    </flux:select>
                    <flux:error name="orientation" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <!-- 4. Parametros Agronomicos -->
        <x-agro.form-section title="Parametros Agronomicos">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label for="maximum_yield_kg_ha">Rendimiento Maximo Historico (kg/ha)</flux:label>
                    <flux:input wire:model="maximum_yield_kg_ha" type="number" step="0.01" min="0" id="maximum_yield_kg_ha" placeholder="Ej: 8000" />
                    <flux:error name="maximum_yield_kg_ha" />
                </flux:field>
                <flux:field>
                    <flux:label for="degree_day_base">Temperatura Base Grados-Dia (°C)</flux:label>
                    <flux:input wire:model="degree_day_base" type="number" step="0.1" min="0" max="30" id="degree_day_base" placeholder="10.0" />
                    <flux:description>Por defecto 10 °C (estandar viticola)</flux:description>
                    <flux:error name="degree_day_base" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <!-- 5. Asignaciones -->
        @if ($this->canSelectWinery() || $this->canSelectViticulturist() || $this->canSelectSigpac())
            <x-agro.form-section title="Asignaciones">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if (in_array(auth()->user()->role, ['admin', 'supervisor', 'winery', 'viticulturist']))
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
                    @endif

                    @if ($this->canSelectSigpac())
                        <flux:field>
                            <flux:label for="sigpac_use">Usos SIGPAC *</flux:label>
                            <flux:select wire:model="sigpac_use" id="sigpac_use" data-cy="plot-sigpac-use" multiple size="5" required>
                                @forelse ($sigpacUses as $use)
                                    <option value="{{ $use->id }}">
                                        {{ $use->code }} - {{ $use->description }}
                                    </option>
                                @empty
                                    <option value="" disabled>No hay usos SIGPAC disponibles</option>
                                @endforelse
                            </flux:select>
                            <flux:description>
                                Manten pulsado Ctrl (o Cmd en Mac) para seleccionar varios usos.
                            </flux:description>
                            <flux:error name="sigpac_use" />
                        </flux:field>
                    @endif
                </div>
            </x-agro.form-section>
        @endif

        <!-- 6. Configuracion de Alertas -->
        <x-agro.form-section title="Configuracion de Alertas (Teledeteccion)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <flux:field>
                        <flux:label for="ndvi_alert_threshold">Umbral de Alerta NDVI (Vigor)</flux:label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <flux:input wire:model="ndvi_alert_threshold" type="number" step="0.05" min="0" max="1"
                                id="ndvi_alert_threshold" placeholder="0.30" />
                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-zinc-300 bg-zinc-50 text-zinc-500 text-sm">
                                NDVI
                            </span>
                        </div>
                        <flux:error name="ndvi_alert_threshold" />
                    </flux:field>
                    <p class="mt-1 text-xs text-zinc-500">Recibiras una alerta si el vigor baja de este valor (Por defecto: 0.30)</p>
                </div>

                <div class="flex items-center mt-6">
                    <flux:checkbox wire:model="alert_email_enabled" id="alert_email_enabled" label="Recibir alertas por Email" description="Ademas de la notificacion en la web, te enviaremos un correo." />
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :cancel-url="route('plots.index')" submit-label="Actualizar Parcela" />
    </form>
</x-agro.form-card>
</div>
