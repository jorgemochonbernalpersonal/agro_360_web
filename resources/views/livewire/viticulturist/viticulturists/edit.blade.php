<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Editar Viticultor')"
        :description="__('Actualiza los datos del viticultor')"
    >
        <x-slot:actions>
            <a href="{{ roleRoute('viticulturist.personal.index', ['viewMode' => 'personal']) }}">
                <flux:button variant="outline" icon="arrow-left">
                    {{ __('Volver') }}
                </flux:button>
            </a>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.card>
        <form wire:submit="save" class="space-y-8">
            <x-agro.form-section :title="__('Información del Viticultor')">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label for="name" :badge="__('Obligatorio')">{{ __('Nombre Completo') }}</flux:label>
                        <flux:input wire:model="name" type="text" id="name" :placeholder="__('Ej: Juan Pérez')" required />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="email" :badge="__('Obligatorio')">{{ __('Email') }}</flux:label>
                        <flux:input wire:model="email" type="email" id="email" placeholder="correo@ejemplo.com" required />
                        <flux:error name="email" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-actions
                :cancel-url="roleRoute('viticulturist.personal.index', ['viewMode' => 'personal'])"
                :submit-label="__('Guardar cambios')"
            />
        </form>
    </x-agro.card>

</div>
