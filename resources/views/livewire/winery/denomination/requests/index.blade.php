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

    {{-- Tabla ───────────────────────────────────────────────────────────── --}}
    <x-agro.data-table :headers="['Tipo', 'Título', 'Estado', 'Fecha', 'Vence', 'Acción']">
        @forelse($requests as $req)
            <x-agro.table-row>
                <x-agro.table-cell>
                    <div class="flex items-center gap-1.5">
                        @if(in_array($req->type, \App\Models\SupervisorRequest::WINERY_INITIATED))
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-600 border border-blue-200">Tú</span>
                        @else
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-violet-50 text-violet-600 border border-violet-200">DO</span>
                        @endif
                        <span class="text-xs font-medium text-zinc-600">{{ $typeLabels[$req->type] ?? $req->type }}</span>
                    </div>
                </x-agro.table-cell>
                <x-agro.table-cell>
                    <div>
                        <p class="text-sm text-zinc-800">{{ $req->title ?: '—' }}</p>
                        @if($req->notes)
                            <p class="text-xs text-zinc-400 mt-0.5 line-clamp-1">{{ $req->notes }}</p>
                        @endif
                    </div>
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @php $color = $statusColors[$req->status] ?? 'zinc'; @endphp
                    <x-agro.status-badge :status="$req->status" :label="$statusLabels[$req->status] ?? $req->status" :color="$color" />
                </x-agro.table-cell>
                <x-agro.table-cell>
                    <span class="text-xs text-zinc-400">
                        {{ $req->sent_at ? $req->sent_at->format('d/m/Y') : $req->created_at->format('d/m/Y') }}
                    </span>
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @if($req->due_date)
                        @if($req->isOverdue())
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-red-600">
                                <flux:icon icon="exclamation-circle" class="w-3.5 h-3.5" />
                                {{ $req->due_date->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="text-xs text-amber-600">{{ $req->due_date->format('d/m/Y') }}</span>
                        @endif
                    @else
                        <span class="text-xs text-zinc-300">—</span>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell align="right">
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
                </x-agro.table-cell>
            </x-agro.table-row>

            {{-- Panel inline de respuesta --}}
            @if($respondingId === $req->id)
                <tr class="bg-blue-50">
                    <td colspan="6" class="px-6 py-4">
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
                    </td>
                </tr>
            @endif

        @empty
            <x-agro.table-row>
                <td colspan="6" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-10 h-10 rounded-full bg-zinc-100 flex items-center justify-center">
                            <flux:icon icon="document-text" class="size-5 text-zinc-400" />
                        </div>
                        <p class="text-sm font-medium text-zinc-500">Sin solicitudes</p>
                        <p class="text-xs text-zinc-400">Aquí aparecerán las comunicaciones con tu denominación de origen.</p>
                    </div>
                </td>
            </x-agro.table-row>
        @endforelse

        <x-slot name="pagination">{{ $requests->links() }}</x-slot>
    </x-agro.data-table>

</div>
