<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Configuración del sistema"
        description="Parámetros globales de la plataforma Agro365"
    />

    {{-- Plataforma --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center">
                    <flux:icon icon="server" class="size-5 text-blue-600" />
                </div>
                <div>
                    <h3 class="font-semibold text-zinc-900">Plataforma</h3>
                    <p class="text-xs text-zinc-400">Controla el acceso y estado general del sistema</p>
                </div>
            </div>
        </x-slot:header>

        <div class="space-y-5">
            {{-- Registro --}}
            <div class="flex items-center justify-between py-3 border-b border-zinc-100">
                <div>
                    <p class="text-sm font-medium text-zinc-900">Registro de nuevos usuarios</p>
                    <p class="text-xs text-zinc-400 mt-0.5">Permite que nuevos usuarios se registren en la plataforma</p>
                </div>
                <flux:switch wire:model="registration_open" />
            </div>

            {{-- Mantenimiento --}}
            <div class="flex items-center justify-between py-3 border-b border-zinc-100">
                <div>
                    <p class="text-sm font-medium text-zinc-900">Modo mantenimiento</p>
                    <p class="text-xs text-zinc-400 mt-0.5">Muestra una página de mantenimiento a todos los usuarios no admin</p>
                </div>
                <flux:switch wire:model="maintenance_mode" />
            </div>

            {{-- Email soporte --}}
            <div class="space-y-1.5">
                <label class="block text-sm font-medium text-zinc-700">Email de soporte</label>
                <p class="text-xs text-zinc-400">Dirección de contacto que se muestra a los usuarios</p>
                <flux:input
                    wire:model="support_email"
                    type="email"
                    placeholder="soporte@agro365.es"
                    class="max-w-sm"
                />
                @error('support_email')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <x-slot:footer>
            <div class="flex justify-end">
                <flux:button wire:click="savePlatform" variant="primary" wire:loading.attr="disabled">
                    Guardar configuración
                </flux:button>
            </div>
        </x-slot:footer>
    </x-agro.card>

    {{-- Beta --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-violet-50 rounded-xl flex items-center justify-center">
                    <flux:icon icon="beaker" class="size-5 text-violet-600" />
                </div>
                <div>
                    <h3 class="font-semibold text-zinc-900">Acceso Beta</h3>
                    <p class="text-xs text-zinc-400">Fecha límite por defecto al activar el acceso beta a un usuario</p>
                </div>
            </div>
        </x-slot:header>

        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-zinc-700">Fecha límite beta por defecto</label>
            <p class="text-xs text-zinc-400">Se aplica al activar beta desde el panel de usuarios si no se especifica otra fecha</p>
            <flux:input
                wire:model="beta_end_date"
                type="date"
                class="max-w-xs"
            />
            @error('beta_end_date')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <x-slot:footer>
            <div class="flex justify-end">
                <flux:button wire:click="saveBeta" variant="primary" wire:loading.attr="disabled">
                    Guardar fecha beta
                </flux:button>
            </div>
        </x-slot:footer>
    </x-agro.card>

    {{-- Info --}}
    <div class="flex items-start gap-3 p-4 bg-zinc-50 border border-zinc-200 rounded-xl">
        <flux:icon icon="information-circle" class="size-5 text-zinc-400 shrink-0 mt-0.5" />
        <p class="text-xs text-zinc-500">
            Los cambios de plataforma se aplican inmediatamente. La configuración se almacena en base de datos
            y se cachea durante 5 minutos. Para aplicar el modo mantenimiento al instante, limpia la caché
            desde consola con <code class="font-mono bg-zinc-100 px-1 rounded">php artisan cache:clear</code>.
        </p>
    </div>

</div>
