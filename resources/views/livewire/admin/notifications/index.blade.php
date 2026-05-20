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

                    {{-- Preview de destinatarios --}}
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

                    <div class="flex flex-col gap-2">
                        <flux:button
                            wire:click="openPreview"
                            variant="ghost"
                            icon="eye"
                            class="w-full"
                            :disabled="$recipientCount === 0"
                        >
                            Previsualizar email
                        </flux:button>
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
                </div>
            </x-agro.card>
        </div>
    </div>

    {{-- Historial de envíos --}}
    @if($history->count() > 0)
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-zinc-100">
                    <flux:icon icon="clock" class="size-4 text-zinc-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Historial de envíos</span>
                <flux:badge color="zinc" size="sm">Últimos 10</flux:badge>
            </div>
        </x-slot:header>

        <div class="overflow-x-auto -mx-5 sm:-mx-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 bg-zinc-50">
                        <th class="text-left text-xs font-medium text-zinc-500 px-5 sm:px-6 py-2.5">Asunto</th>
                        <th class="text-left text-xs font-medium text-zinc-500 px-4 py-2.5">Audiencia</th>
                        <th class="text-right text-xs font-medium text-zinc-500 px-4 py-2.5">Destinatarios</th>
                        <th class="text-left text-xs font-medium text-zinc-500 px-4 py-2.5">Admin</th>
                        <th class="text-left text-xs font-medium text-zinc-500 px-5 sm:px-6 py-2.5">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach($history as $log)
                    <tr class="hover:bg-zinc-50 transition-colors">
                        <td class="px-5 sm:px-6 py-2.5">
                            <p class="font-medium text-zinc-800 truncate max-w-[200px]">{{ $log->subject }}</p>
                        </td>
                        <td class="px-4 py-2.5">
                            <flux:badge color="blue" size="sm">{{ $log->audienceLabel() }}</flux:badge>
                        </td>
                        <td class="px-4 py-2.5 text-right font-semibold text-zinc-900">
                            {{ $log->recipient_count }}
                        </td>
                        <td class="px-4 py-2.5 text-zinc-500 text-xs">
                            {{ $log->admin?->name ?? 'Sistema' }}
                        </td>
                        <td class="px-5 sm:px-6 py-2.5">
                            <p class="text-zinc-700">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                            <p class="text-zinc-400 text-xs">{{ $log->created_at->diffForHumans() }}</p>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-agro.card>
    @endif

    {{-- Modal: Preview email --}}
    <flux:modal wire:model="showPreviewModal" class="w-full max-w-2xl">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="p-2 rounded-lg bg-blue-50">
                    <flux:icon icon="envelope-open" class="size-5 text-blue-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">Vista previa del email</h3>
                    <p class="text-xs text-zinc-500">Así verán el mensaje los {{ $recipientCount }} destinatario(s)</p>
                </div>
            </div>

            {{-- Email frame --}}
            <div class="border border-zinc-200 rounded-xl overflow-hidden">
                {{-- Header del email --}}
                <div class="bg-agro-600 px-6 py-4">
                    <p class="text-white font-semibold text-sm">Agro365</p>
                </div>
                {{-- Body --}}
                <div class="bg-white px-6 py-5 space-y-4">
                    <div class="border-b border-zinc-100 pb-3">
                        <p class="text-xs text-zinc-400 uppercase tracking-wide font-medium">Asunto</p>
                        <p class="text-base font-semibold text-zinc-900 mt-1">{{ $subject ?: '(sin asunto)' }}</p>
                    </div>
                    <div class="text-sm text-zinc-700 leading-relaxed whitespace-pre-wrap">{{ $message ?: '(sin contenido)' }}</div>
                </div>
                {{-- Footer del email --}}
                <div class="bg-zinc-50 px-6 py-3 border-t border-zinc-100">
                    <p class="text-xs text-zinc-400">Este mensaje fue enviado desde Agro365. Si no esperabas este email, puedes ignorarlo.</p>
                </div>
            </div>

            <div class="flex justify-between gap-3 mt-6 pt-4 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="closePreview">Cerrar</flux:button>
                <flux:button
                    variant="primary"
                    icon="paper-airplane"
                    wire:click="send"
                    wire:confirm="¿Enviar este email a {{ $recipientCount }} usuario(s)? Los emails se pondrán en cola."
                >
                    Confirmar envío ({{ $recipientCount }})
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
