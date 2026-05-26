<div class="space-y-6 animate-fade-in">

    {{-- Breadcrumb + Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-zinc-400 mb-1">
                <a href="{{ route('supervisor.oversight.growers.index') }}" wire:navigate
                   class="hover:text-zinc-600 transition">Supervisión — Viticultores</a>
                <flux:icon icon="chevron-right" class="size-3" />
                <span class="text-zinc-600">{{ $viticulturist->name }}</span>
            </div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ $viticulturist->name }}</h1>
            <p class="text-sm text-zinc-400 mt-0.5">{{ $viticulturist->email }}</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <flux:button
                wire:click="openAssignWineryModal"
                variant="ghost" size="sm" icon="building-office-2"
            >{{ __('Asignar bodega') }}</flux:button>
            <a href="{{ route('supervisor.inspection.index') }}" wire:navigate>
                <flux:button variant="ghost" size="sm" icon="clipboard-document-list">{{ __('Nueva inspección') }}</flux:button>
            </a>
            <flux:button
                wire:click="removeFromPool"
                wire:confirm="{{ __('¿Eliminar a :name del pool de la DO? Se retirarán sus asignaciones a bodegas y solicitudes de cuaderno.', ['name' => $viticulturist->name]) }}"
                variant="ghost" size="sm" icon="trash"
                class="text-red-400 hover:text-red-600 hover:bg-red-50"
            >
                Retirar del pool
            </flux:button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <x-agro.stat-card :label="__('Parcelas activas')" :value="$totalPlots" icon="map" color="agro" />
        <x-agro.stat-card :label="__('Área total (ha)')" :value="number_format($totalArea, 2)" icon="globe-europe-africa" color="blue" />
        <x-agro.stat-card :label="__('Certificaciones')" :value="$certifications->count()" icon="check-badge" color="emerald" />
        <x-agro.stat-card :label="__('Inspecciones')" :value="$inspections->count()" icon="shield-check" color="violet" />
    </div>

    {{-- Alertas PAC --}}
    @if($plotsWithoutPac > 0 || $lockedPlots > 0)
        <div class="flex flex-wrap gap-3">
            @if($plotsWithoutPac > 0)
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-700">
                    <flux:icon icon="exclamation-triangle" class="size-4" />
                    {{ $plotsWithoutPac }} parcela{{ $plotsWithoutPac !== 1 ? 's' : '' }} sin datos PAC
                </div>
            @endif
            @if($lockedPlots > 0)
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-50 border border-blue-200 text-sm text-blue-700">
                    <flux:icon icon="lock-closed" class="size-4" />
                    {{ $lockedPlots }} parcela{{ $lockedPlots !== 1 ? 's' : '' }} bloqueada{{ $lockedPlots !== 1 ? 's' : '' }} (PAC)
                </div>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Parcelas --}}
        <div class="lg:col-span-2 space-y-4">
            <x-agro.card>
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <flux:icon icon="map" class="size-4 text-zinc-400" />
                        <span class="font-medium text-zinc-700">{{ __('Parcelas') }}</span>
                    </div>
                </x-slot>

                @forelse($plots as $plot)
                    <div class="px-4 py-3 border-b border-zinc-100 last:border-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-zinc-800">{{ $plot->name }}</span>
                                    @if($plot->is_locked)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs bg-blue-50 text-blue-600 border border-blue-200">
                                            <flux:icon icon="lock-closed" class="size-3" />PAC
                                        </span>
                                    @endif
                                    @if($plot->is_organic)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs bg-green-50 text-green-600 border border-green-200">{{ __('ECO') }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-zinc-400 mt-0.5">
                                    {{ $plot->municipality?->name ?? '—' }}
                                    @if($plot->code_parcel)· {{ $plot->code_parcel }}@endif
                                </div>
                                @if($plot->plantings->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($plot->plantings as $planting)
                                            <span class="px-1.5 py-0.5 rounded text-xs bg-zinc-100 text-zinc-600">
                                                {{ $planting->grapeVariety?->name ?? '—' }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-sm font-medium text-zinc-700">{{ number_format($plot->area, 2) }} ha</div>
                                @if($plot->pac_eligible_area)
                                    <div class="text-xs text-zinc-400">Elegible: {{ number_format($plot->pac_eligible_area, 2) }} ha</div>
                                @else
                                    <div class="text-xs text-amber-500">Sin PAC</div>
                                @endif
                            </div>
                        </div>
                        @if($plot->lastAgriculturalActivity)
                            <div class="text-xs text-zinc-400 mt-1.5">
                                Última actividad: {{ $plot->lastAgriculturalActivity->activity_date->format('d/m/Y') }}
                                · {{ \App\Models\AgriculturalActivity::ACTIVITY_TYPES[$plot->lastAgriculturalActivity->activity_type] ?? $plot->lastAgriculturalActivity->activity_type }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-sm text-zinc-400">Sin parcelas activas</div>
                @endforelse
            </x-agro.card>

            {{-- Cuaderno (si hay acceso) --}}
            @if($hasNotebookAccess)
                <x-agro.card>
                    <x-slot name="header">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <flux:icon icon="book-open" class="size-4 text-zinc-400" />
                                <span class="font-medium text-zinc-700">{{ __('Cuaderno de campo') }}</span>
                            </div>
                            <flux:button
                                wire:click="revokeNotebookAccess"
                                wire:confirm="{{ __('¿Revocar el acceso al cuaderno de :name?', ['name' => $viticulturist->name]) }}"
                                variant="ghost" size="sm" icon="x-mark"
                            >
                                Revocar acceso
                            </flux:button>
                        </div>
                    </x-slot>

                    @forelse($recentActivities as $activity)
                        <div class="px-4 py-2.5 border-b border-zinc-100 last:border-0 flex items-center justify-between gap-4">
                            <div>
                                <span class="text-sm text-zinc-700">
                                    {{ \App\Models\AgriculturalActivity::ACTIVITY_TYPES[$activity->activity_type] ?? $activity->activity_type }}
                                </span>
                                @if($activity->plot_id)
                                    <span class="text-xs text-zinc-400"> · Parcela #{{ $activity->plot_id }}</span>
                                @endif
                            </div>
                            <span class="text-xs text-zinc-400 shrink-0">{{ $activity->activity_date->format('d/m/Y') }}</span>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center text-sm text-zinc-400">Sin actividades registradas aún.</div>
                    @endforelse
                </x-agro.card>
            @elseif($pendingNotebookRequest)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-center space-y-2">
                    <flux:icon icon="clock" class="size-8 mx-auto text-amber-400" />
                    <p class="text-sm font-medium text-amber-800">{{ __('Solicitud de acceso pendiente') }}</p>
                    <p class="text-xs text-amber-600">{{ __('El viticultor debe aprobar el acceso desde su panel.') }}</p>
                </div>
            @elseif(!$viticulturist->can_login)
                <div class="rounded-xl border border-dashed border-zinc-200 p-6 text-center text-sm text-zinc-400">
                    <flux:icon icon="book-open" class="size-8 mx-auto mb-2 text-zinc-300" />
                    <p>{{ __('El viticultor aún no ha activado su cuenta. El acceso al cuaderno estará disponible cuando lo haga.') }}</p>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-zinc-200 p-6 text-center space-y-3">
                    <flux:icon icon="book-open" class="size-8 mx-auto text-zinc-300" />
                    <p class="text-sm text-zinc-400">{{ __('Sin acceso al cuaderno de campo.') }}</p>
                    <flux:button wire:click="requestNotebookAccess" variant="ghost" size="sm" icon="envelope">{{ __('Solicitar acceso al cuaderno') }}</flux:button>
                </div>
            @endif
        </div>

        {{-- Panel lateral --}}
        <div class="space-y-4">

            {{-- Actividad por tipo (año actual) --}}
            <x-agro.card>
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <flux:icon icon="chart-bar" class="size-4 text-zinc-400" />
                        <span class="font-medium text-zinc-700">Actividad {{ now()->year }}</span>
                    </div>
                </x-slot>
                @foreach(\App\Models\AgriculturalActivity::ACTIVITY_TYPES as $type => $label)
                    @php $count = $activityCounts[$type] ?? 0; @endphp
                    @if($count > 0)
                        <div class="px-4 py-2 flex items-center justify-between border-b border-zinc-50 last:border-0">
                            <span class="text-sm text-zinc-600">{{ $label }}</span>
                            <span class="text-sm font-medium text-zinc-800">{{ $count }}</span>
                        </div>
                    @endif
                @endforeach
                @if($activityCounts->isEmpty())
                    <div class="px-4 py-4 text-center text-sm text-zinc-400">Sin actividad este año</div>
                @endif
            </x-agro.card>

            {{-- Bodegas del DO --}}
            <x-agro.card>
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <flux:icon icon="building-office-2" class="size-4 text-zinc-400" />
                            <span class="font-medium text-zinc-700">{{ __('Bodegas DO') }}</span>
                        </div>
                        <button wire:click="openAssignWineryModal"
                                class="text-xs text-indigo-600 hover:underline">{{ __('+ Asignar') }}</button>
                    </div>
                </x-slot>
                @forelse($wineryRelations as $rel)
                    <div class="px-4 py-2.5 border-b border-zinc-100 last:border-0 flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-zinc-700 truncate">{{ $rel->winery?->name ?? '—' }}</div>
                            <div class="text-xs mt-0.5">
                                @if($rel->notebook_access)
                                    <span class="text-emerald-600">{{ __('Cuaderno: concedido') }}</span>
                                @else
                                    <span class="text-zinc-400">{{ __('Cuaderno: sin acceso') }}</span>
                                @endif
                            </div>
                        </div>
                        <button
                            wire:click="unassignWinery({{ $rel->winery_id }})"
                            wire:confirm="{{ __('¿Retirar la asignación de :name a este viticultor?', ['name' => $rel->winery?->name]) }}"
                            class="text-zinc-300 hover:text-red-500 transition"
                            title="{{ __('Desasignar bodega') }}"
                        >
                            <flux:icon icon="x-mark" class="size-4" />
                        </button>
                    </div>
                @empty
                    <div class="px-4 py-4 text-center text-sm text-zinc-400">No asignado a bodegas</div>
                @endforelse
            </x-agro.card>

            {{-- Certificaciones --}}
            <x-agro.card>
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <flux:icon icon="check-badge" class="size-4 text-zinc-400" />
                        <span class="font-medium text-zinc-700">{{ __('Certificaciones') }}</span>
                    </div>
                </x-slot>
                @forelse($certifications as $cert)
                    <div class="px-4 py-2.5 border-b border-zinc-100 last:border-0">
                        <div class="text-sm font-medium text-zinc-700">{{ $cert->certification_type_label }}</div>
                        @if($cert->expiry_date)
                            <div class="text-xs mt-0.5
                                {{ $cert->is_expired ? 'text-red-500' : ($cert->is_expiring_soon ? 'text-amber-500' : 'text-zinc-400') }}">
                                Vence: {{ $cert->expiry_date->format('d/m/Y') }}
                                @if($cert->is_expired) · Caducada
                                @elseif($cert->is_expiring_soon) · Pronto vence
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="px-4 py-4 text-center text-sm text-zinc-400">Sin certificaciones</div>
                @endforelse
            </x-agro.card>

            {{-- Inspecciones recientes --}}
            @if($inspections->isNotEmpty())
                <x-agro.card>
                    <x-slot name="header">
                        <div class="flex items-center gap-2">
                            <flux:icon icon="shield-check" class="size-4 text-zinc-400" />
                            <span class="font-medium text-zinc-700">{{ __('Inspecciones recientes') }}</span>
                        </div>
                    </x-slot>
                    @foreach($inspections as $insp)
                        <div class="px-4 py-2.5 border-b border-zinc-100 last:border-0 flex items-center justify-between gap-2">
                            <div>
                                <div class="text-sm text-zinc-700">{{ $insp->inspection_date?->format('d/m/Y') ?? '—' }}</div>
                                @if($insp->result)
                                    <div class="text-xs {{ $insp->result === 'compliant' ? 'text-emerald-600' : ($insp->result === 'non_compliant' ? 'text-red-500' : 'text-zinc-400') }}">
                                        {{ $insp->result === 'compliant' ? 'Conforme' : ($insp->result === 'non_compliant' ? 'No conforme' : 'Pendiente') }}
                                    </div>
                                @endif
                            </div>
                            <x-agro.status-badge :status="$insp->status" />
                        </div>
                    @endforeach
                </x-agro.card>
            @endif

        </div>
    </div>

    {{-- Modal: asignar bodega --}}
    <flux:modal wire:model="showAssignWineryModal" class="w-full max-w-md">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-blue-50">
                    <flux:icon icon="building-office-2" class="size-5 text-blue-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">{{ __('Asignar a bodega') }}</h3>
                    <p class="text-xs text-zinc-500">Selecciona una bodega de tu DO para asignarle a {{ $viticulturist->name }}</p>
                </div>
            </div>

            @if($availableWineries->count() > 0)
                <flux:field>
                    <flux:label>{{ __('Bodega') }}</flux:label>
                    <flux:select wire:model="assignWineryId">
                        <flux:select.option value="">{{ __('— Selecciona una bodega —') }}</flux:select.option>
                        @foreach($availableWineries as $winery)
                            <flux:select.option value="{{ $winery->id }}">{{ $winery->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="assignWineryId" />
                </flux:field>
            @else
                <p class="text-sm text-zinc-400 text-center py-4">{{ __('Todas las bodegas de tu DO ya están asignadas a este viticultor.') }}</p>
            @endif

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="closeAssignWineryModal">{{ __('Cancelar') }}</flux:button>
                @if($availableWineries->count() > 0)
                    <flux:button variant="primary" wire:click="assignWinery" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="assignWinery">{{ __('Asignar') }}</span>
                        <span wire:loading wire:target="assignWinery">{{ __('Asignando...') }}</span>
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>

</div>
