<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Sistema"
        description="Configuración de perfil de la denominación de origen."
    />

    {{-- Profile --}}
    <x-agro.card>
        <h3 class="text-sm font-semibold text-zinc-700 mb-4 flex items-center gap-2">
            <flux:icon icon="user-circle" class="w-4 h-4 text-zinc-400" />
            Datos de la denominación
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Nombre</label>
                <input
                    type="text"
                    wire:model="name"
                    class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                />
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Email de contacto</label>
                <input
                    type="email"
                    wire:model="email"
                    class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                />
                @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="mt-4">
            <button wire:click="updateProfile" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Guardar cambios
            </button>
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
                <label class="block text-xs font-medium text-zinc-600 mb-1">Contraseña actual</label>
                <input
                    type="password"
                    wire:model="current_password"
                    class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                />
                @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Nueva contraseña</label>
                <input
                    type="password"
                    wire:model="new_password"
                    class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                />
                @error('new_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Confirmar contraseña</label>
                <input
                    type="password"
                    wire:model="confirm_password"
                    class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                />
            </div>
        </div>
        <div class="mt-4">
            <button wire:click="updatePassword" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Cambiar contraseña
            </button>
        </div>
    </x-agro.card>

    {{-- Pending --}}
    <flux:callout variant="info" icon="information-circle">
        <flux:callout.heading>Configuración avanzada en construcción</flux:callout.heading>
        <flux:callout.text>
            Gestión de usuarios adicionales de la DO, documentos internos y comunicaciones masivas estarán disponibles próximamente.
        </flux:callout.text>
    </flux:callout>

</div>
