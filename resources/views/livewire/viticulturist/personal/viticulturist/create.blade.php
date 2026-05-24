<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    @if(session('message'))
        <flux:callout variant="success">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if(session('error'))
        <flux:callout variant="danger">
            {{ session('error') }}
        </flux:callout>
    @endif

    <!-- Header -->
    <x-agro.page-header
        :title="__('Crear Viticultor')"
        :description="__('Crea un nuevo viticultor para gestionar en tus cuadrillas')"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.personal.index') }}" variant="outline" icon="arrow-left">
                {{ __('Volver') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Formulario -->
    <x-agro.card>
        <form wire:submit="save" class="space-y-8">
            <!-- Informacion del Viticultor -->
            <div class="border-b border-zinc-200 pb-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="p-1.5 rounded-lg bg-agro-50">
                        <flux:icon icon="user" class="size-4 text-agro-600" />
                    </div>
                    <span class="font-semibold text-zinc-900">{{ __('Información del Viticultor') }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <flux:field>
                        <flux:label>{{ __('Nombre Completo') }} *</flux:label>
                        <flux:input
                            wire:model="name"
                            type="text"
                            id="name"
                            :placeholder="__('Ej: Juan Pérez')"
                            required
                        />
                        <flux:error name="name" />
                    </flux:field>

                    <!-- Email -->
                    <flux:field>
                        <flux:label>{{ __('Email') }} *</flux:label>
                        <flux:input
                            wire:model="email"
                            type="email"
                            id="email"
                            placeholder="correo@ejemplo.com"
                            required
                        />
                        <flux:error name="email" />
                    </flux:field>
                </div>

                <!-- Bodega (opcional) -->
                @if($wineries->isNotEmpty())
                <div class="mt-6">
                    <flux:field>
                        <flux:label>{{ __('Bodega') }} <span class="text-zinc-500 font-normal">({{ __('opcional') }})</span></flux:label>
                        <flux:select
                            wire:model="winery_id"
                            id="winery_id"
                        >
                            <option value="">{{ __('Sin bodega') }}</option>
                            @foreach($wineries as $winery)
                                <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="winery_id" />
                    </flux:field>
                </div>
                @endif
            </div>

            <!-- Informacion -->
            <flux:callout variant="info">
                <p class="text-sm font-semibold mb-1">{{ __('Nota importante:') }}</p>
                <p class="text-sm">
                    {{ __('El viticultor que crees se añadirá para gestión interna (cuadrillas, parcelas, etc.), pero no tendrá acceso a la aplicación hasta que se active su cuenta por parte de un administrador o mediante un flujo de registro propio.') }}
                </p>
            </flux:callout>

            <!-- Botones -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-zinc-200">
                <flux:button href="{{ roleRoute('viticulturist.personal.index') }}" variant="outline">{{ __('Cancelar') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Crear Viticultor') }}</flux:button>
            </div>
        </form>
    </x-agro.card>
</div>
