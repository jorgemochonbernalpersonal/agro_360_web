<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        :title="__('Registrar entrada en bodega')"
        :description="__('La cosecha del cuaderno se convierte en recepción oficial. Solo necesitas elegir el depósito.')"
    >
        <x-slot:actions>
            <flux:button
                :href="route('producer.digital-notebook.harvest.show', $notebookHarvest->id)"
                variant="ghost"
                icon="arrow-left"
                wire:navigate
            >
                {{ __('Volver') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Panel origen: datos del cuaderno (solo lectura) --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <flux:icon name="book-open" class="size-4 text-green-600" />
                <span class="font-medium text-sm">{{ __('Origen: cosecha del cuaderno de campo') }}</span>
            </div>
        </x-slot:header>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-zinc-500">{{ __('Parcela') }}</p>
                <p class="font-medium">{{ $notebookHarvest->plotPlanting?->plot?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-zinc-500">{{ __('Variedad') }}</p>
                <p class="font-medium">{{ $notebookHarvest->plotPlanting?->grapeVariety?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-zinc-500">{{ __('Fecha cosecha') }}</p>
                <p class="font-medium">{{ $notebookHarvest->harvest_start_date?->format('d/m/Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-zinc-500">{{ __('Peso registrado') }}</p>
                <p class="font-medium">{{ number_format($notebookHarvest->total_weight, 0) }} kg</p>
            </div>
        </div>
    </x-agro.card>

    <form wire:submit="save">
        {{-- Sección 1: Depósito (único campo obligatorio nuevo) --}}
        <x-agro.form-section :title="__('Depósito de destino')">
            <div class="space-y-5">
                <flux:field>
                    <flux:label>{{ __('Depósito destino') }} *</flux:label>
                    <flux:select wire:model="container_id">
                        <flux:select.option value="">{{ __('Selecciona un depósito...') }}</flux:select.option>
                        @foreach($availableContainers as $c)
                            @php $available = max(0, $c->capacity - $c->used_capacity); @endphp
                            <flux:select.option value="{{ $c->id }}">
                                {{ $c->name }} ({{ number_format($available, 0) }} kg {{ __('disp.') }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="container_id" />
                </flux:field>
            </div>
        </x-agro.form-section>

        {{-- Sección 2: Datos de recepción (pre-rellenados, ajustables) --}}
        <x-agro.form-section :title="__('Datos de recepción')" :description="__('Pre-rellenados desde el cuaderno. Puedes ajustarlos antes de registrar.')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <flux:field>
                    <flux:label>{{ __('Kg recibidos') }} *</flux:label>
                    <flux:input wire:model="total_weight" type="number" step="0.001" min="0" />
                    <flux:error name="total_weight" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de recepción') }} *</flux:label>
                    <flux:input wire:model="harvest_start_date" type="date" />
                    <flux:error name="harvest_start_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Añada') }}</flux:label>
                    <flux:input wire:model="vintage_year" type="number" min="2000" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Nº Albarán') }} <span class="text-zinc-400 font-normal text-xs">({{ __('opcional') }})</span></flux:label>
                    <flux:input wire:model="harvest_ticket_number" type="text" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Precio/kg') }} <span class="text-zinc-400 font-normal text-xs">({{ __('opcional') }})</span></flux:label>
                    <flux:input wire:model="price_per_kg" type="number" step="0.001" min="0" />
                </flux:field>
            </div>
        </x-agro.form-section>

        {{-- Sección 3: Calidad (colapsable, pre-rellenada) --}}
        @if($baume_degree || $brix_degree || $ph_level || $health_status)
        <x-agro.form-section :title="__('Parámetros de calidad')" :description="__('Importados del cuaderno de campo.')">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-5">
                <flux:field>
                    <flux:label>{{ __('Grado Baumé') }}</flux:label>
                    <flux:input wire:model="baume_degree" type="number" step="0.001" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Grado Brix') }}</flux:label>
                    <flux:input wire:model="brix_degree" type="number" step="0.001" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Acidez total') }}</flux:label>
                    <flux:input wire:model="acidity_level" type="number" step="0.001" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('pH') }}</flux:label>
                    <flux:input wire:model="ph_level" type="number" step="0.001" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Alcohol potencial') }}</flux:label>
                    <flux:input wire:model="potential_alcohol" type="number" step="0.01" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Estado sanitario') }}</flux:label>
                    <flux:select wire:model="health_status">
                        <flux:select.option value="">{{ __('— Sin especificar —') }}</flux:select.option>
                        <flux:select.option value="sano">{{ __('Sano') }}</flux:select.option>
                        <flux:select.option value="daño_leve">{{ __('Daño leve') }}</flux:select.option>
                        <flux:select.option value="daño_moderado">{{ __('Daño moderado') }}</flux:select.option>
                        <flux:select.option value="daño_grave">{{ __('Daño grave') }}</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>
        </x-agro.form-section>
        @endif

        <x-agro.form-actions>
            <x-slot:submit>
                <flux:button type="submit" variant="primary" icon="arrow-right-circle" data-cy="submit-button">
                    {{ __('Registrar en bodega') }}
                </flux:button>
            </x-slot:submit>
            <x-slot:cancel>
                <flux:button
                    :href="route('producer.digital-notebook.harvest.show', $notebookHarvest->id)"
                    variant="ghost"
                    wire:navigate
                >
                    {{ __('Cancelar') }}
                </flux:button>
            </x-slot:cancel>
        </x-agro.form-actions>
    </form>
</div>
