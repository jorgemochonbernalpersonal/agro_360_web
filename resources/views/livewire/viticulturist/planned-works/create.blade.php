<x-agro.form-card
    :title="__('Nuevo Trabajo Planificado')"
    :description="__('Planifica una tarea para tu explotación vinculada al cuaderno de campo')"
    :back-url="roleRoute('viticulturist.planned-works.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- Sección 1: Qué hacer --}}
        <x-agro.form-section :title="__('Descripción del trabajo')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field class="md:col-span-2">
                    <flux:label required>{{ __('Título') }}</flux:label>
                    <flux:input wire:model="title" type="text" :placeholder="__('Ej: Tratamiento preventivo mildiu parcela norte')" />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Categoría') }}</flux:label>
                    <flux:select wire:model="category">
                        @foreach ($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Prioridad') }}</flux:label>
                    <flux:select wire:model="priority">
                        @foreach ($priorities as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="priority" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>{{ __('Descripción') }}</flux:label>
                    <flux:textarea wire:model="description" rows="3" :placeholder="__('Detalle del trabajo a realizar, productos necesarios, etc.')" />
                    <flux:error name="description" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Sección 2: Cuándo y dónde --}}
        <x-agro.form-section :title="__('Planificación')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Fecha prevista') }}</flux:label>
                    <flux:input wire:model="planned_date" type="date" />
                    <flux:error name="planned_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha fin (si dura varios días)') }}</flux:label>
                    <flux:input wire:model="planned_end_date" type="date" />
                    <flux:description>{{ __('Dejar vacío para tareas de un solo día') }}</flux:description>
                    <flux:error name="planned_end_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Parcela') }}</flux:label>
                    <flux:select wire:model="plot_id">
                        <option value="">{{ __('Todas / Sin especificar') }}</option>
                        @foreach ($plots as $plot)
                            <option value="{{ $plot->id }}">{{ $plot->name }} @if($plot->municipality?->name)· {{ $plot->municipality->name }}@endif</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Campaña') }}</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">{{ __('Sin campaña') }}</option>
                        @foreach ($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ $campaign->name }} ({{ $campaign->year }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Sección 3: Notas --}}
        <x-agro.form-section :title="__('Notas adicionales')">
            <div class="grid grid-cols-1 gap-6">
                <flux:field>
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="3" :placeholder="__('Observaciones, recordatorios, condiciones meteorológicas requeridas...')" />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.planned-works.index')"
            :submit-label="__('Crear Trabajo')"
        />
    </form>
</x-agro.form-card>
