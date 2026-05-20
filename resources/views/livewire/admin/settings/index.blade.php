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
                    placeholder="info@agro365.es"
                    class="max-w-sm"
                />
                @error('support_email')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <x-slot:footer>
            <div class="flex items-center justify-between gap-4">
                @if($lastPlatform)
                    <p class="text-xs text-zinc-400">
                        Último cambio: {{ $lastPlatform->created_at->format('d/m/Y H:i') }}
                        @if($lastPlatform->email) · por {{ $lastPlatform->email }} @endif
                    </p>
                @else
                    <span></span>
                @endif
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
            <div class="flex items-center justify-between gap-4">
                @if($lastBeta)
                    <p class="text-xs text-zinc-400">
                        Último cambio: {{ $lastBeta->created_at->format('d/m/Y H:i') }}
                        @if($lastBeta->email) · por {{ $lastBeta->email }} @endif
                    </p>
                @else
                    <span></span>
                @endif
                <flux:button wire:click="saveBeta" variant="primary" wire:loading.attr="disabled">
                    Guardar fecha beta
                </flux:button>
            </div>
        </x-slot:footer>
    </x-agro.card>

    {{-- Seguridad --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-red-50 rounded-xl flex items-center justify-center">
                    <flux:icon icon="shield-check" class="size-5 text-red-600" />
                </div>
                <div>
                    <h3 class="font-semibold text-zinc-900">Política de contraseñas</h3>
                    <p class="text-xs text-zinc-400">Se aplica al registrarse, al crear usuarios desde admin y al cambiar contraseña</p>
                </div>
            </div>
        </x-slot:header>

        <div class="space-y-5">
            {{-- Longitud mínima --}}
            <div class="space-y-1.5">
                <label class="block text-sm font-medium text-zinc-700">Longitud mínima de contraseña</label>
                <p class="text-xs text-zinc-400">Mínimo 6, máximo 32 caracteres</p>
                <div class="flex items-center gap-3">
                    <flux:input
                        wire:model="password_min_length"
                        type="number"
                        min="6"
                        max="32"
                        class="max-w-[100px]"
                    />
                    <span class="text-sm text-zinc-500">caracteres</span>
                </div>
                @error('password_min_length')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Contraseña fuerte --}}
            <div class="flex items-center justify-between py-3 border-t border-zinc-100">
                <div>
                    <p class="text-sm font-medium text-zinc-900">Requerir contraseña fuerte</p>
                    <p class="text-xs text-zinc-400 mt-0.5">Obliga a incluir mayúscula, número y símbolo</p>
                </div>
                <flux:switch wire:model="require_strong_password" />
            </div>
        </div>

        <x-slot:footer>
            <div class="flex items-center justify-between gap-4">
                @if($lastSecurity)
                    <p class="text-xs text-zinc-400">
                        Último cambio: {{ $lastSecurity->created_at->format('d/m/Y H:i') }}
                        @if($lastSecurity->email) · por {{ $lastSecurity->email }} @endif
                    </p>
                @else
                    <span></span>
                @endif
                <flux:button wire:click="saveSecurity" variant="primary" wire:loading.attr="disabled">
                    Guardar política
                </flux:button>
            </div>
        </x-slot:footer>
    </x-agro.card>

    {{-- Módulos --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-agro-50 rounded-xl flex items-center justify-center">
                    <flux:icon icon="puzzle-piece" class="size-5 text-agro-600" />
                </div>
                <div>
                    <h3 class="font-semibold text-zinc-900">Módulos</h3>
                    <p class="text-xs text-zinc-400">Activa o desactiva funcionalidades para todos los usuarios</p>
                </div>
            </div>
        </x-slot:header>

        <div class="space-y-1">
            <div class="flex items-center justify-between py-3 border-b border-zinc-100">
                <div>
                    <p class="text-sm font-medium text-zinc-900">SILICIE</p>
                    <p class="text-xs text-zinc-400 mt-0.5">Integración con el Registro de Operadores de SILICIE (bodegas)</p>
                </div>
                <flux:switch wire:model="module_silicie" />
            </div>

            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-sm font-medium text-zinc-900">PAC / Cuaderno Digital</p>
                    <p class="text-xs text-zinc-400 mt-0.5">Módulos de Política Agrícola Común: cuaderno de campo, BCAM, IPM (viticultores)</p>
                </div>
                <flux:switch wire:model="module_pac" />
            </div>
        </div>

        <x-slot:footer>
            <div class="flex items-center justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <p class="text-xs text-zinc-400">Los cambios se aplican en el próximo login del usuario</p>
                    @if($lastModules)
                        <p class="text-xs text-zinc-400">
                            Último cambio: {{ $lastModules->created_at->format('d/m/Y H:i') }}
                            @if($lastModules->email) · por {{ $lastModules->email }} @endif
                        </p>
                    @endif
                </div>
                <flux:button wire:click="saveModules" variant="primary" wire:loading.attr="disabled">
                    Guardar módulos
                </flux:button>
            </div>
        </x-slot:footer>
    </x-agro.card>

    {{-- Info --}}
    <div class="flex items-start gap-3 p-4 bg-zinc-50 border border-zinc-200 rounded-xl">
        <flux:icon icon="information-circle" class="size-5 text-zinc-400 shrink-0 mt-0.5" />
        <p class="text-xs text-zinc-500">
            Los cambios se aplican inmediatamente. La configuración se almacena en base de datos
            y se cachea durante 5 minutos. Para aplicar el modo mantenimiento al instante, limpia la caché
            desde consola con <code class="font-mono bg-zinc-100 px-1 rounded">php artisan cache:clear</code>.
        </p>
    </div>

</div>
