<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Sistema') }}"
        :description="__('Configuración de perfil de la denominación de origen.')"
    />

    {{-- Profile --}}
    <x-agro.card>
        <h3 class="text-sm font-semibold text-zinc-700 mb-4 flex items-center gap-2">
            <flux:icon icon="user-circle" class="w-4 h-4 text-zinc-400" />
            Datos de la denominación
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <flux:label>{{ __('Nombre') }}</flux:label>
                <flux:input type="text" wire:model="name"
                    />
                <flux:error name="name" />
            </div>
            <div>
                <flux:label>{{ __('Email de contacto') }}</flux:label>
                <flux:input type="email" wire:model="email"
                    />
                <flux:error name="email" />
            </div>
        </div>
        <div class="mt-4">
            <flux:button wire:click="updateProfile" variant="primary">{{ __('Guardar cambios') }}</flux:button>
        </div>
    </x-agro.card>

    {{-- Password --}}
    <x-agro.card>
        <h3 class="text-sm font-semibold text-zinc-700 mb-4 flex items-center gap-2">
            <flux:icon icon="lock-closed" class="w-4 h-4 text-zinc-400" />
            Cambiar contraseña
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <flux:label>{{ __('Contraseña actual') }}</flux:label>
                <flux:input type="password" wire:model="current_password"
                    />
                <flux:error name="current_password" />
            </div>
            <div>
                <flux:label>{{ __('Nueva contraseña') }}</flux:label>
                <flux:input type="password" wire:model="new_password"
                    />
                <flux:error name="new_password" />
            </div>
            <div>
                <flux:label>{{ __('Confirmar contraseña') }}</flux:label>
                <flux:input type="password" wire:model="confirm_password"
                    />
            </div>
        </div>
        <div class="mt-4">
            <flux:button wire:click="updatePassword" variant="primary">{{ __('Cambiar contraseña') }}</flux:button>
        </div>
    </x-agro.card>

    {{-- Pending --}}
    <flux:callout variant="info" icon="information-circle">
        <flux:callout.heading>{{ __('Configuración avanzada en construcción') }}</flux:callout.heading>
        <flux:callout.text>
            Gestión de usuarios adicionales de la DO, documentos internos y comunicaciones masivas estarán disponibles próximamente.
        </flux:callout.text>
    </flux:callout>

</div>
