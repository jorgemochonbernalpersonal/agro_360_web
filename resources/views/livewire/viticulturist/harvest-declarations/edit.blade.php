<x-agro.form-card
    title="Editar Declaración de Vendimia"
    description="Modifica los datos de la declaración oficial de cosecha"
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
                    <flux:label>Fecha de presentación</flux:label>
                    <flux:input wire:model="submission_date" type="date" />
                    <flux:error name="submission_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>Organismo receptor</flux:label>
                    <flux:input wire:model="authority" type="text" />
                    <flux:error name="authority" />
                </flux:field>

                <flux:field>
                    <flux:label>Nº referencia oficial</flux:label>
                    <flux:input wire:model="reference_number" type="text" placeholder="Número asignado por el organismo" />
                    <flux:error name="reference_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>Estado</flux:label>
                    <flux:select wire:model="status">
                        @foreach($statuses as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>

                @if($status === 'rejected')
                    <flux:field>
                        <flux:label>Motivo de rechazo</flux:label>
                        <flux:textarea wire:model="rejection_reason" rows="2" />
                        <flux:error name="rejection_reason" />
                    </flux:field>
                @endif

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Detalle por Variedad / Parcela">
            <div class="space-y-4">
                @foreach($lines as $i => $line)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 p-4 bg-zinc-50 rounded-xl border border-zinc-200" wire:key="line-{{ $i }}">
                        <flux:field>
                            <flux:label>Variedad</flux:label>
                            <flux:input wire:model.live="lines.{{ $i }}.variety" type="text" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Parcela / Pago</flux:label>
                            <flux:input wire:model.live="lines.{{ $i }}.plot_name" type="text" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Superficie (ha)</flux:label>
                            <flux:input wire:model.live="lines.{{ $i }}.surface_ha" type="number" step="0.0001" min="0" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Producción (kg)</flux:label>
                            <flux:input wire:model.live="lines.{{ $i }}.kg" type="number" step="0.01" min="0" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Destino</flux:label>
                            <flux:input wire:model.live="lines.{{ $i }}.destination" type="text" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Código REGA</flux:label>
                            <flux:input wire:model.live="lines.{{ $i }}.rega_code" type="text" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Comprador</flux:label>
                            <flux:input wire:model.live="lines.{{ $i }}.buyer" type="text" />
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

        <x-agro.form-section title="Totales">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Superficie total (ha)</flux:label>
                    <flux:input wire:model="total_surface_ha" type="number" step="0.0001" readonly />
                </flux:field>
                <flux:field>
                    <flux:label>Producción total (kg)</flux:label>
                    <flux:input wire:model="total_kg" type="number" step="0.01" readonly />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Observaciones">
            <flux:field>
                <flux:label>Notas</flux:label>
                <flux:textarea wire:model="notes" rows="3" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.harvest-declarations.index')"
            submit-label="Guardar Cambios"
        />
    </form>
</x-agro.form-card>
