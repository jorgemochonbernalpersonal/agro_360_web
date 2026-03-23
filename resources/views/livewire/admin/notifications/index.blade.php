<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Notificaciones"
        description="Envía comunicaciones a los usuarios del sistema"
    />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Formulario --}}
        <div class="lg:col-span-2 space-y-4">
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-blue-50">
                            <flux:icon icon="envelope" class="size-4 text-blue-600" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">Componer Mensaje</span>
                    </div>
                </x-slot:header>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">Asunto</label>
                        <flux:input wire:model.live="subject" placeholder="Asunto del email..." />
                        @error('subject') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">Mensaje</label>
                        <flux:textarea
                            wire:model.live="message"
                            rows="8"
                            placeholder="Escribe el contenido del mensaje aquí..."
                        />
                        @error('message') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </x-agro.card>
        </div>

        {{-- Panel audiencia + envío --}}
        <div class="space-y-4">
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-purple-50">
                            <flux:icon icon="users" class="size-4 text-purple-600" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">Audiencia</span>
                    </div>
                </x-slot:header>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">Rol</label>
                        <flux:select wire:model.live="audienceRole">
                            <option value="all">Todos los roles</option>
                            <option value="viticulturist">Viticultores</option>
                            <option value="winery">Bodegas</option>
                            <option value="supervisor">Supervisores</option>
                            <option value="producer">Productores</option>
                            <option value="admin">Admins</option>
                        </flux:select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">Email verificado</label>
                        <flux:select wire:model.live="audienceVerified">
                            <option value="">Todos</option>
                            <option value="1">Solo verificados</option>
                            <option value="0">Solo no verificados</option>
                        </flux:select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">Estado</label>
                        <flux:select wire:model.live="audienceActive">
                            <option value="1">Solo activos</option>
                            <option value="">Todos</option>
                            <option value="0">Solo inactivos</option>
                        </flux:select>
                    </div>

                    {{-- Contador de destinatarios --}}
                    <div class="bg-zinc-50 rounded-lg p-4 text-center">
                        <p class="text-3xl font-bold text-zinc-900">{{ $recipientCount }}</p>
                        <p class="text-xs text-zinc-500 mt-1">destinatario(s)</p>
                    </div>

                    {{-- Preview --}}
                    @if($previewUsers->count() > 0)
                    <div>
                        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide mb-2">Vista previa</p>
                        <div class="space-y-1">
                            @foreach($previewUsers as $u)
                            <div class="flex items-center gap-2 text-xs">
                                <flux:icon icon="user" class="size-3 text-zinc-400 flex-shrink-0" />
                                <span class="text-zinc-700 truncate">{{ $u->name }}</span>
                                <span class="text-zinc-400 truncate">{{ $u->email }}</span>
                            </div>
                            @endforeach
                            @if($recipientCount > 5)
                            <p class="text-xs text-zinc-400 mt-1">... y {{ $recipientCount - 5 }} más</p>
                            @endif
                        </div>
                    </div>
                    @endif

                    <flux:button
                        wire:click="send"
                        wire:confirm="¿Enviar este email a {{ $recipientCount }} usuario(s)? Los emails se pondrán en cola."
                        variant="primary"
                        icon="paper-airplane"
                        class="w-full"
                        :disabled="$recipientCount === 0"
                    >
                        Enviar ({{ $recipientCount }})
                    </flux:button>
                </div>
            </x-agro.card>
        </div>
    </div>
</div>
