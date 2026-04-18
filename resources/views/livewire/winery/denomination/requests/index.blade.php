<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Solicitudes DO"
        description="Gestiona las comunicaciones con tu Denominación de Origen."
        icon="document-text"
    >
        <x-slot:actions>
            <flux:button wire:click="openCreate" icon="plus" variant="primary" size="sm">
                Nueva solicitud
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    @if($pendingCount > 0)
        <flux:callout variant="warning" icon="exclamation-triangle">
            Tienes <strong>{{ $pendingCount }}</strong> {{ $pendingCount === 1 ? 'solicitud pendiente' : 'solicitudes pendientes' }} de respuesta.
        </flux:callout>
    @endif

    {{-- Panel crear solicitud ────────────────────────────────────────────── --}}
    @if($showCreate)
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="paper-airplane" class="size-4 text-blue-500" />
                    <span>Nueva solicitud a la DO</span>
                </div>
            </x-slot:header>
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <flux:label>Tipo de solicitud <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model="createType" class="mt-1">
                            <option value="">Selecciona un tipo…</option>
                            @foreach($wineryInitiatedLabels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                        @error('createType') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <flux:label>Asunto <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="createTitle" placeholder="Título o referencia…" class="mt-1" />
                        @error('createTitle') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <flux:label>Descripción / notas</flux:label>
                    <flux:textarea wire:model="createNotes" rows="3" placeholder="Detalla el motivo o la información que necesitas…" class="mt-1" />
                    @error('createNotes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-2">
                    <flux:button wire:click="saveRequest" variant="primary" size="sm" icon="paper-airplane">
                        Enviar solicitud
                    </flux:button>
                    <flux:button wire:click="closeCreate" variant="ghost" size="sm">
                        Cancelar
                    </flux:button>
                </div>
            </div>
        </x-agro.card>
    @endif

    {{-- Filtro ──────────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2">
        <flux:select wire:model.live="statusFilter" class="w-44 text-sm">
            <option value="">Todos los estados</option>
            @foreach($statusLabels as $val => $label)
                <option value="{{ $val }}">{{ $label }}</option>
            @endforeach
        </flux:select>
    </div>

    {{-- Cards ───────────────────────────────────────────────────────────── --}}
    <x-agro.loading-grid target="statusFilter, nextPage, previousPage" />

    <div wire:loading.remove wire:target="statusFilter, nextPage, previousPage">
        @if($requests->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($requests as $req)
                    @php $delay = min($loop->index * 50, 300); @endphp
                    @php $color = $statusColors[$req->status] ?? 'zinc'; @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="req-{{ $req->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                                    <flux:icon icon="document-text" class="size-5 text-indigo-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $req->title ?: '—' }}</h3>
                                    <p class="text-xs text-zinc-500">
                                        @if(in_array($req->type, \App\Models\SupervisorRequest::WINERY_INITIATED))
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-600 border border-blue-200">Tú</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-violet-50 text-violet-600 border border-violet-200">DO</span>
                                        @endif
                                        {{ $typeLabels[$req->type] ?? $req->type }}
                                    </p>
                                </div>
                                <x-agro.status-badge :status="$req->status" :label="$statusLabels[$req->status] ?? $req->status" :color="$color" class="shrink-0" />
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            @if($req->notes)
                                <p class="text-xs text-zinc-400 line-clamp-2">{{ $req->notes }}</p>
                            @endif

                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Fecha</span>
                                    <span class="text-zinc-700 font-medium">
                                        {{ $req->sent_at ? $req->sent_at->format('d/m/Y') : $req->created_at->format('d/m/Y') }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Vence</span>
                                    <span class="font-medium">
                                        @if($req->due_date)
                                            @if($req->isOverdue())
                                                <span class="inline-flex items-center gap-1 text-red-600">
                                                    <flux:icon icon="exclamation-circle" class="w-3.5 h-3.5" />
                                                    {{ $req->due_date->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <span class="text-amber-600">{{ $req->due_date->format('d/m/Y') }}</span>
                                            @endif
                                        @else
                                            <span class="text-zinc-300">—</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-1">
                                @if($req->canBeRespondedByWinery())
                                    <flux:button wire:click="startResponding({{ $req->id }})" variant="primary" size="sm">
                                        Responder
                                    </flux:button>
                                    @if(in_array($req->type, \App\Models\SupervisorRequest::WINERY_INITIATED))
                                        <flux:button wire:click="retractRequest({{ $req->id }})"
                                            wire:confirm="¿Retirar esta solicitud? Quedará archivada y la DO no podrá actuar sobre ella."
                                            variant="ghost" size="sm" icon="x-mark">
                                        </flux:button>
                                    @endif
                                @elseif($req->response_notes)
                                    <span class="text-xs text-zinc-400 italic">Respondida</span>
                                @else
                                    <span class="text-xs text-zinc-300">—</span>
                                @endif
                            </div>
                        </x-slot:footer>
                    </x-agro.card>

                    {{-- Panel inline de respuesta --}}
                    @if($respondingId === $req->id)
                        <x-agro.card class="md:col-span-2 lg:col-span-3 xl:col-span-4 border-blue-200 bg-blue-50/50">
                            <div class="space-y-3">
                                @if($req->notes)
                                    <p class="text-sm text-zinc-600"><span class="font-medium">Notas de la DO:</span> {{ $req->notes }}</p>
                                @endif
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 mb-1">Tu respuesta</label>
                                    <flux:textarea wire:model="responseNotes" rows="3" placeholder="Escribe tu respuesta o comentarios…" />
                                    @error('responseNotes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="flex gap-2">
                                    <flux:button wire:click="respond" variant="primary" size="sm">Enviar respuesta</flux:button>
                                    <flux:button wire:click="cancelResponding" variant="ghost" size="sm">Cancelar</flux:button>
                                </div>
                            </div>
                        </x-agro.card>
                    @endif
                @endforeach
            </div>

            <div class="mt-6">{{ $requests->links() }}</div>
        @else
            <x-agro.empty-state
                icon="document-text"
                title="Sin solicitudes"
                description="Aquí aparecerán las comunicaciones con tu denominación de origen."
            />
        @endif
    </div>

</div>
