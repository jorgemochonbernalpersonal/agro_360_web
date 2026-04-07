<x-agro.form-card
    title="Nueva Declaración de Vendimia"
    description="Declaración oficial de cosecha ante el organismo competente (CCAA / Denominación de Origen)"
    :back-url="roleRoute('viticulturist.harvest-declarations.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section title="Datos de la Declaración">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Campaña</flux:label>
                    <flux:select wire:model="campaign_id">
                        @foreach($campaigns as $c)
                            <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Año de vendimia</flux:label>
                    <flux:input wire:model="declaration_year" type="number" min="2000" max="2100" />
                    <flux:error name="declaration_year" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de declaración</flux:label>
                    <flux:input wire:model="declaration_date" type="date" />
                    <flux:error name="declaration_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>Organismo receptor</flux:label>
                    <flux:input wire:model="authority" type="text" placeholder="Ej: Junta de Castilla y León — ATRIA, D.O. Ribera del Duero" />
                    <flux:description>CCAA, Denominación de Origen o MAPA según corresponda</flux:description>
                    <flux:error name="authority" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Detalle por Variedad / Parcela">
            <div class="space-y-4">
                @foreach($lines as $i => $line)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 p-4 bg-zinc-50 rounded-xl border border-zinc-200" wire:key="line-{{ $i }}">
                        <flux:field>
                            <flux:label>Variedad</flux:label>
                            <flux:input wire:model.live="lines.{{ $i }}.variety" type="text" placeholder="Tempranillo" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Parcela / Pago</flux:label>
                            <flux:input wire:model.live="lines.{{ $i }}.plot_name" type="text" placeholder="Parcela Norte" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Superficie (ha)</flux:label>
                            <flux:input wire:model.live="lines.{{ $i }}.surface_ha" type="number" step="0.0001" min="0" placeholder="0.0000" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Producción (kg)</flux:label>
                            <flux:input wire:model.live="lines.{{ $i }}.kg" type="number" step="0.01" min="0" placeholder="0.00" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Destino</flux:label>
                            <flux:input wire:model.live="lines.{{ $i }}.destination" type="text" placeholder="Bodega García S.L." />
                        </flux:field>
                        <flux:field>
                            <flux:label>Código REGA destino</flux:label>
                            <flux:input wire:model.live="lines.{{ $i }}.rega_code" type="text" placeholder="ES-VA-XXXXXX" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Comprador</flux:label>
                            <flux:input wire:model.live="lines.{{ $i }}.buyer" type="text" placeholder="Nombre o NIF" />
                        </flux:field>
                        <div class="flex items-end pb-1">
                            @if(count($lines) > 1)
                                <button type="button" wire:click="removeLine({{ $i }})"
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach

                <flux:button type="button" wire:click="addLine" variant="outline" icon="plus" size="sm">
                    Añadir línea
                </flux:button>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Totales (calculados automáticamente)">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Superficie total (ha)</flux:label>
                    <flux:input wire:model="total_surface_ha" type="number" step="0.0001" readonly />
                    <flux:description>Suma de todas las líneas</flux:description>
                </flux:field>
                <flux:field>
                    <flux:label>Producción total (kg)</flux:label>
                    <flux:input wire:model="total_kg" type="number" step="0.01" readonly />
                    <flux:description>Suma de todas las líneas</flux:description>
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Observaciones">
            <flux:field>
                <flux:label>Notas</flux:label>
                <flux:textarea wire:model="notes" rows="3" placeholder="Información adicional relevante para la declaración..." />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.harvest-declarations.index')"
            submit-label="Guardar como Borrador"
        />
    </form>
</x-agro.form-card>
