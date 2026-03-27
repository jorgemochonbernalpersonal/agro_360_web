<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Solicitudes de la DO"
        description="Solicitudes y actuaciones de tu Denominación de Origen."
        icon="document-text"
    />

    @if($pendingCount > 0)
        <flux:callout variant="warning" icon="exclamation-triangle">
            Tienes <strong>{{ $pendingCount }}</strong> {{ $pendingCount === 1 ? 'solicitud pendiente' : 'solicitudes pendientes' }} de respuesta.
        </flux:callout>
    @endif

    {{-- Filtro --}}
    <div class="flex items-center gap-2">
        <flux:select wire:model.live="statusFilter" class="w-44 text-sm">
            <option value="">Todos los estados</option>
            @foreach($statusLabels as $val => $label)
                <option value="{{ $val }}">{{ $label }}</option>
            @endforeach
        </flux:select>
    </div>

    {{-- Tabla --}}
    <x-agro.data-table :headers="['Tipo', 'Título', 'Estado', 'Enviada', 'Acción']">
        @forelse($requests as $req)
            <x-agro.table-row>
                <x-agro.table-cell>
                    <span class="text-xs font-medium text-zinc-600">{{ $typeLabels[$req->type] ?? $req->type }}</span>
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
                        {{ $req->sent_at ? $req->sent_at->format('d/m/Y') : '—' }}
                    </span>
                </x-agro.table-cell>
                <x-agro.table-cell align="right">
                    @if($req->canBeRespondedByWinery())
                        <flux:button wire:click="startResponding({{ $req->id }})" variant="primary" size="sm">
                            Responder
                        </flux:button>
                    @elseif($req->response_notes)
                        <span class="text-xs text-zinc-400 italic">Respondida</span>
                    @else
                        <span class="text-xs text-zinc-300">—</span>
                    @endif
                </x-agro.table-cell>
            </x-agro.table-row>

            {{-- Panel inline de respuesta --}}
            @if($respondingId === $req->id)
                <tr class="bg-blue-50">
                    <td colspan="5" class="px-6 py-4">
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
                <td colspan="5" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-10 h-10 rounded-full bg-zinc-100 flex items-center justify-center">
                            <flux:icon icon="document-text" class="size-5 text-zinc-400" />
                        </div>
                        <p class="text-sm font-medium text-zinc-500">Sin solicitudes de la DO</p>
                        <p class="text-xs text-zinc-400">Cuando tu denominación de origen te envíe actuaciones aparecerán aquí.</p>
                    </div>
                </td>
            </x-agro.table-row>
        @endforelse

        <x-slot name="pagination">{{ $requests->links() }}</x-slot>
    </x-agro.data-table>

</div>
