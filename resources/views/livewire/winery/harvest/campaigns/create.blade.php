<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Nueva Campaña de Vendimia"
        description="Define el periodo y nombre de la campaña para agrupar tus recepciones de uva"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.campaigns.index') }}" variant="ghost" icon="arrow-left">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-agro-50">
                    <flux:icon icon="calendar" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Datos de la Campaña</span>
            </div>
        </x-slot:header>

        <form wire:submit="save" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <flux:field>
                    <flux:label>Nombre de la campaña</flux:label>
                    <flux:input wire:model="name" placeholder="Ej: Vendimia 2026" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Año</flux:label>
                    <flux:input wire:model="year" type="number" min="2000" max="2099" />
                    <flux:error name="year" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <flux:field>
                    <flux:label>Fecha de inicio</flux:label>
                    <flux:input wire:model="start_date" type="date" />
                    <flux:error name="start_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha de fin</flux:label>
                    <flux:input wire:model="end_date" type="date" />
                    <flux:error name="end_date" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Descripción (opcional)</flux:label>
                <flux:textarea wire:model="description" rows="3" placeholder="Notas sobre esta campaña..." />
                <flux:error name="description" />
            </flux:field>

            <div class="pt-2 flex justify-end gap-3">
                <flux:button href="{{ route('winery.campaigns.index') }}" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button type="submit" variant="primary" icon="check">
                    Crear Campaña
                </flux:button>
            </div>
        </form>
    </x-agro.card>
</div>
