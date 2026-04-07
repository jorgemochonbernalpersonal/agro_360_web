<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    @if (session('message'))
        <flux:callout variant="success" icon="check-circle" dismissible>
            {{ session('message') }}
        </flux:callout>
    @endif

    @if (session('error'))
        <flux:callout variant="danger" icon="exclamation-circle" dismissible>
            {{ session('error') }}
        </flux:callout>
    @endif

    <!-- Header -->
    <x-agro.page-header
        title="Crear Viticultor"
        description="Crea un nuevo viticultor para gestionar en tus cuadrillas y parcelas"
    >
        <x-slot:actions>
            <a href="{{ roleRoute('viticulturist.personal.index', ['viewMode' => 'personal']) }}">
                <flux:button variant="outline" icon="arrow-left">
                    Volver
                </flux:button>
            </a>
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Formulario -->
    <x-agro.card>
        <form wire:submit="save" class="space-y-8">
            <!-- Información del Viticultor -->
            <x-agro.form-section title="Información del Viticultor">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <flux:field>
                        <flux:label for="name" badge="Obligatorio">Nombre Completo</flux:label>
                        <flux:input wire:model="name" type="text" id="name" placeholder="Ej: Juan Pérez"
                            required />
                        <flux:error name="name" />
                    </flux:field>

                    <!-- Email -->
                    <flux:field>
                        <flux:label for="email" badge="Obligatorio">Email</flux:label>
                        <flux:input wire:model="email" type="email" id="email" placeholder="correo@ejemplo.com"
                            required />
                        <flux:error name="email" />
                    </flux:field>
                </div>

                <!-- Bodega (opcional) -->
                @if ($wineries->isNotEmpty())
                    <div class="mt-6">
                        <flux:field>
                            <flux:label for="winery_id">Bodega <span
                                    class="text-zinc-500 font-normal">(opcional)</span></flux:label>
                            <flux:select wire:model="winery_id" id="winery_id">
                                <option value="">Sin bodega</option>
                                @foreach ($wineries as $winery)
                                    <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                                @endforeach
                            </flux:select>
                            <flux:error name="winery_id" />
                        </flux:field>
                    </div>
                @endif
            </x-agro.form-section>

            <!-- Información -->
            <flux:callout variant="info">
                <flux:callout.heading>Nota importante:</flux:callout.heading>
                <flux:callout.text>
                    El viticultor que crees se añadirá para gestión interna (cuadrillas, parcelas, etc.),
                    pero no tendrá acceso a la aplicación hasta que se active su cuenta por parte de un
                    administrador o mediante un flujo de registro propio.
                </flux:callout.text>
            </flux:callout>

            <x-agro.form-actions
                :cancel-url="roleRoute('viticulturist.personal.index', ['viewMode' => 'personal'])"
                submit-label="Crear Viticultor"
            />
        </form>
    </x-agro.card>
</div>
