<div>
    <x-agro.form-card title="Nueva Parcela" description="Crea una nueva parcela agricola" :back-url="route('plots.index')">
        <form wire:submit.prevent="save" class="space-y-8" data-cy="plot-create-form">
            @error('general')
                <flux:callout variant="danger" icon="x-circle">
                    <flux:callout.heading>Error</flux:callout.heading>
                    <flux:callout.text>{{ $message }}</flux:callout.text>
                </flux:callout>
            @enderror

            <!-- Configuracion de Alertas -->
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

            <x-agro.form-section title="Informacion Basica">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <flux:field>
                        <flux:label for="name">Nombre de la Parcela *</flux:label>
                        <flux:input wire:model="name" type="text" id="name" data-cy="plot-name" placeholder="Ej: Parcela Norte" required />
                        <flux:error name="name" />
                    </flux:field>

                    <!-- Area -->
                    <flux:field>
                        <flux:label for="area">Area (hectareas)</flux:label>
                        <flux:input wire:model="area" type="number" step="0.001" id="area" data-cy="plot-area" placeholder="0.000" />
                        <flux:error name="area" />
                    </flux:field>
                </div>

                <!-- Descripcion -->
                <div class="mt-6">
                    <flux:field>
                        <flux:label for="description">Descripcion</flux:label>
                        <flux:textarea wire:model="description" id="description" data-cy="plot-description" rows="3"
                            placeholder="Descripcion de la parcela..." />
                        <flux:error name="description" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <!-- Asignaciones -->
            @if ($this->canSelectWinery() || $this->canSelectViticulturist() || $this->canSelectSigpac())
                <x-agro.form-section title="Asignaciones">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Bodega removed: plots now belong to viticultor, not directly to a winery -->

                        <!-- Viticultor (Solo admin/supervisor/winery) -->
                        @if (in_array(auth()->user()->role, ['admin', 'supervisor', 'winery', 'viticulturist']))
                            <flux:field>
                                <flux:label for="viticulturist_id">Viticultor Asignado *</flux:label>
                                <flux:select wire:model="viticulturist_id" id="viticulturist_id" data-cy="plot-viticulturist-id" required>
                                    <option value="">Seleccionar...</option>
                                    @forelse ($this->viticulturists as $viticulturist)
                                        <option value="{{ $viticulturist->id }}">{{ $viticulturist->name }}@if($viticulturist->id === auth()->id()) (Yo)@endif</option>
                                    @empty
                                        <option value="" disabled>No hay viticultores disponibles</option>
                                    @endforelse
                                </flux:select>
                                <flux:error name="viticulturist_id" />
                            </flux:field>
                        @endif

                        <!-- Usos SIGPAC (select multiple junto al viticultor) -->
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

            <!-- Ubicacion -->
            <x-agro.form-section title="Ubicacion">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Comunidad Autonoma -->
                    <flux:field>
                        <flux:label for="autonomous_community_id">Comunidad Autonoma *</flux:label>
                        <flux:select wire:model.live="autonomous_community_id" id="autonomous_community_id" data-cy="plot-autonomous-community-id" required>
                            <option value="">Seleccionar...</option>
                            @foreach ($autonomousCommunities as $community)
                                <option value="{{ $community->id }}">
                                    {{ $community->code === '15' ? 'Comunidad Foral de Navarra' : $community->name }}
                                </option>
                            @endforeach
                        </flux:select>
                        <flux:error name="autonomous_community_id" />
                    </flux:field>

                    <!-- Provincia -->
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

                    <!-- Municipio -->
                    <flux:field>
                        <flux:label for="municipality_id">Municipio *</flux:label>
                        <flux:select wire:model.live="municipality_id" id="municipality_id" data-cy="plot-municipality-id" required
                            :disabled="!$province_id">
                            <option value="">Seleccionar...</option>
                            @foreach ($municipalities as $municipality)
                                <option value="{{ $municipality->id }}">{{ $municipality->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="municipality_id" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="route('plots.index')" submit-label="Crear Parcela" />
        </form>
    </x-agro.form-card>
</div>
