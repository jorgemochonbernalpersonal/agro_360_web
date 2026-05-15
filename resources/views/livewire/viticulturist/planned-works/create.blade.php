<x-agro.form-card
    title="Nuevo Trabajo Planificado"
    description="Planifica una tarea para tu explotación vinculada al cuaderno de campo"
    :back-url="roleRoute('viticulturist.planned-works.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- Sección 1: Qué hacer --}}
        <x-agro.form-section title="Descripción del trabajo">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field class="md:col-span-2">
                    <flux:label required>Título</flux:label>
                    <flux:input wire:model="title" type="text" placeholder="Ej: Tratamiento preventivo mildiu parcela norte" />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label required>Categoría</flux:label>
                    <flux:select wire:model="category">
                        @foreach ($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category" />
                </flux:field>

                <flux:field>
                    <flux:label required>Prioridad</flux:label>
                    <flux:select wire:model="priority">
                        @foreach ($priorities as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="priority" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Descripción</flux:label>
                    <flux:textarea wire:model="description" rows="3" placeholder="Detalle del trabajo a realizar, productos necesarios, etc." />
                    <flux:error name="description" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Sección 2: Cuándo y dónde --}}
        <x-agro.form-section title="Planificación">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Fecha prevista</flux:label>
                    <flux:input wire:model="planned_date" type="date" />
                    <flux:error name="planned_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha fin (si dura varios días)</flux:label>
                    <flux:input wire:model="planned_end_date" type="date" />
                    <flux:description>Dejar vacío para tareas de un solo día</flux:description>
                    <flux:error name="planned_end_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Parcela</flux:label>
                    <flux:select wire:model="plot_id">
                        <option value="">Todas / Sin especificar</option>
                        @foreach ($plots as $plot)
                            <option value="{{ $plot->id }}">{{ $plot->name }} @if($plot->municipality?->name)· {{ $plot->municipality->name }}@endif</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Campaña</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">Sin campaña</option>
                        @foreach ($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ $campaign->name }} ({{ $campaign->year }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Sección 3: Notas --}}
        <x-agro.form-section title="Notas adicionales">
            <div class="grid grid-cols-1 gap-6">
                <flux:field>
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="3" placeholder="Observaciones, recordatorios, condiciones meteorológicas requeridas..." />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.planned-works.index')"
            submit-label="Crear Trabajo"
        />
    </form>
</x-agro.form-card>
