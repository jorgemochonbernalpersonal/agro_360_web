<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Normativa DO"
        description="Autorizaciones de plantación, certificaciones ecológicas y documentos regulatorios."
    >
        @if(in_array($currentTab, ['pliego', 'reglamento']))
        <x-slot name="actions">
            <button wire:click="openDocForm('{{ $currentTab }}')"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <flux:icon icon="plus" class="w-4 h-4" />
                Nuevo documento
            </button>
        </x-slot>
        @endif
    </x-agro.page-header>

    {{-- Tabs --}}
    <div class="border-b border-zinc-200">
        <nav class="-mb-px flex gap-1 overflow-x-auto">
            @foreach($tabs as $key => $tab)
                <button wire:click="switchTab('{{ $key }}')"
                    class="whitespace-nowrap px-4 py-2.5 text-sm font-medium border-b-2 transition
                        {{ $currentTab === $key
                            ? 'border-indigo-500 text-indigo-600'
                            : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- ── TAB: Autorizaciones de plantación ─────────────────────────────── --}}
    @if($currentTab === 'autorizaciones')

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <x-agro.stat-card label="Total autorizaciones" :value="$stats['total']" icon="check-circle" color="indigo" />
            <x-agro.stat-card label="Nueva plantación"    :value="$stats['nueva']"         icon="sparkles"   color="emerald" />
            <x-agro.stat-card label="Replantación"        :value="$stats['replantacion']"   icon="arrow-path" color="blue" />
            <x-agro.stat-card label="Conversión"          :value="$stats['conversion']"     icon="arrows-right-left" color="amber" />
            <x-agro.stat-card label="Transferencia"       :value="$stats['transferencia']"  icon="arrow-right-circle" color="violet" />
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="filterVit"
                class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none">
                <option value="">Todos los viticultores</option>
                @foreach($viticulturists as $v)
                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterRightType"
                class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none">
                <option value="">Todos los tipos de derecho</option>
                <option value="nueva">Nueva plantación</option>
                <option value="replantacion">Replantación</option>
                <option value="conversion">Conversión</option>
                <option value="transferencia">Transferencia</option>
            </select>

            <div class="relative">
                <flux:icon icon="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none" />
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar autorización o parcela..."
                    class="pl-9 pr-3 py-1.5 text-sm border border-zinc-200 rounded-lg focus:outline-none w-60" />
            </div>
        </div>

        {{-- Table --}}
        <x-agro.data-table
            :headers="['Viticultor', 'Parcela', 'Variedad', 'Nº autorización', 'Tipo derecho', 'Fecha autorización', 'Arranque']"
            emptyMessage="No hay plantaciones con autorización registrada."
        >
            @foreach($items as $planting)
                @php
                    $rightLabels = ['nueva' => 'Nueva', 'replantacion' => 'Replantación', 'conversion' => 'Conversión', 'transferencia' => 'Transferencia'];
                    $rightColors = ['nueva' => 'emerald', 'replantacion' => 'blue', 'conversion' => 'amber', 'transferencia' => 'violet'];
                    $rc = $rightColors[$planting->right_type] ?? 'zinc';
                @endphp
                <tr class="hover:bg-zinc-50 transition">
                    <td class="px-6 py-3 text-sm font-medium text-zinc-800">{{ $planting->plot?->viticulturist?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-zinc-600">{{ $planting->plot?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-zinc-500">{{ $planting->grapeVariety?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm font-mono text-zinc-700">{{ $planting->planting_authorization }}</td>
                    <td class="px-6 py-3 text-sm">
                        @if($planting->right_type)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $rc }}-100 text-{{ $rc }}-700">
                                {{ $rightLabels[$planting->right_type] ?? $planting->right_type }}
                            </span>
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-sm text-zinc-500">
                        {{ $planting->authorization_date ? $planting->authorization_date->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-6 py-3 text-sm text-zinc-500">
                        {{ $planting->uprooting_date ? $planting->uprooting_date->format('d/m/Y') : '—' }}
                    </td>
                </tr>
            @endforeach

            <x-slot name="pagination">{{ $items->links() }}</x-slot>
        </x-agro.data-table>

    {{-- ── TAB: Certificaciones ecológicas ────────────────────────────────── --}}
    @elseif($currentTab === 'certificaciones')

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <x-agro.stat-card label="Total certificaciones" :value="$stats['total']"   icon="check-badge"        color="indigo" />
            <x-agro.stat-card label="Vigentes"              :value="$stats['active']"  icon="check-circle"       color="emerald" />
            <x-agro.stat-card label="Por vencer (60 días)"  :value="$stats['expiring']" icon="clock"             color="amber" />
            <x-agro.stat-card label="Caducadas"             :value="$stats['expired']" icon="exclamation-circle" color="red" />
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="filterVit"
                class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none">
                <option value="">Todos los viticultores</option>
                @foreach($viticulturists as $v)
                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterStatus"
                class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none">
                <option value="">Todos los estados</option>
                <option value="active">Vigentes</option>
                <option value="expiring">Por vencer</option>
                <option value="expired">Caducadas</option>
            </select>

            <div class="relative">
                <flux:icon icon="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none" />
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar nº certificado u organismo..."
                    class="pl-9 pr-3 py-1.5 text-sm border border-zinc-200 rounded-lg focus:outline-none w-64" />
            </div>
        </div>

        {{-- Table --}}
        <x-agro.data-table
            :headers="['Viticultor', 'Organismo certificador', 'Nº certificado', 'Emisión', 'Caducidad', 'Estado']"
            emptyMessage="No hay certificaciones ecológicas registradas."
        >
            @foreach($items as $cert)
                @php
                    $isExpired  = $cert->is_expired;
                    $isExpiring = $cert->is_expiring_soon;
                    $statusColor = $isExpired ? 'red' : ($isExpiring ? 'amber' : 'emerald');
                    $statusLabel = $isExpired ? 'Caducada' : ($isExpiring ? 'Por vencer' : 'Vigente');
                @endphp
                <tr class="hover:bg-zinc-50 transition">
                    <td class="px-6 py-3 text-sm font-medium text-zinc-800">{{ $cert->viticulturist?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-zinc-600">{{ $cert->certifying_body ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm font-mono text-zinc-700">{{ $cert->certificate_number ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-zinc-500">
                        {{ $cert->issue_date ? $cert->issue_date->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-6 py-3 text-sm {{ $isExpired ? 'text-red-600 font-medium' : ($isExpiring ? 'text-amber-600 font-medium' : 'text-zinc-500') }}">
                        {{ $cert->expiry_date ? $cert->expiry_date->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-6 py-3 text-sm">
                        @if($cert->active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700">
                                {{ $statusLabel }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-500">Inactiva</span>
                        @endif
                    </td>
                </tr>
            @endforeach

            <x-slot name="pagination">{{ $items->links() }}</x-slot>
        </x-agro.data-table>

    {{-- ── TAB: Pliego de condiciones / Reglamento interno ─────────────────── --}}
    @elseif(in_array($currentTab, ['pliego', 'reglamento']))

        @php
            $typeLabel  = $currentTab === 'pliego' ? 'pliego de condiciones' : 'reglamento interno';
            $typeIcon   = $currentTab === 'pliego' ? 'document-text' : 'clipboard-document-list';
        @endphp

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-3">
            <x-agro.stat-card label="Borradores" :value="$stats['draft']"    icon="pencil"       color="zinc" />
            <x-agro.stat-card label="Vigentes"   :value="$stats['active']"   icon="check-circle" color="emerald" />
            <x-agro.stat-card label="Archivados" :value="$stats['archived']" icon="archive-box"  color="amber" />
        </div>

        {{-- Filter --}}
        <div class="flex items-center gap-3">
            <select wire:model.live="filterStatus"
                class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none">
                <option value="">Todos los estados</option>
                <option value="draft">Borradores</option>
                <option value="active">Vigentes</option>
                <option value="archived">Archivados</option>
            </select>
        </div>

        {{-- Document list --}}
        @if($items->isEmpty())
            <x-agro.empty-state
                :icon="$typeIcon"
                :title="'Sin documentos de ' . $typeLabel"
                :description="'Crea el primer documento de ' . $typeLabel . ' para esta denominación.'"
            />
        @else
            <div class="space-y-3">
                @foreach($items as $doc)
                    @php
                        $statusColors = ['draft' => 'zinc', 'active' => 'emerald', 'archived' => 'amber'];
                        $sc = $statusColors[$doc->status] ?? 'zinc';
                    @endphp
                    <x-agro.card class="hover:shadow-sm transition">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <flux:icon icon="{{ $typeIcon }}" class="w-4 h-4 text-indigo-500" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-zinc-800">{{ $doc->title }}</p>
                                    <div class="flex flex-wrap items-center gap-3 mt-1">
                                        @if($doc->version)
                                            <span class="text-xs text-zinc-400">v{{ $doc->version }}</span>
                                        @endif
                                        @if($doc->effective_date)
                                            <span class="text-xs text-zinc-400">Vigente desde {{ $doc->effective_date->format('d/m/Y') }}</span>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">
                                            {{ \App\Models\DoDocument::STATUS_LABELS[$doc->status] }}
                                        </span>
                                    </div>
                                    @if($doc->content)
                                        <p class="text-xs text-zinc-500 mt-2 line-clamp-2">{{ $doc->content }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if($doc->status === 'draft')
                                    <button wire:click="activateDocument({{ $doc->id }})"
                                        class="text-xs text-emerald-600 hover:underline">Activar</button>
                                @endif
                                @if($doc->status === 'active')
                                    <button wire:click="archiveDocument({{ $doc->id }})"
                                        class="text-xs text-amber-600 hover:underline">Archivar</button>
                                @endif
                                <button wire:click="openDocForm('{{ $currentTab }}', {{ $doc->id }})"
                                    class="text-xs text-zinc-500 hover:text-zinc-700 hover:underline">Editar</button>
                                <button wire:click="deleteDocument({{ $doc->id }})"
                                    wire:confirm="¿Eliminar este documento?"
                                    class="text-xs text-red-400 hover:text-red-600 hover:underline">Eliminar</button>
                            </div>
                        </div>
                    </x-agro.card>
                @endforeach
            </div>
            <div class="mt-4">{{ $items->links() }}</div>
        @endif

    @endif

    {{-- ── Document form modal ─────────────────────────────────────────────── --}}
    @if($showDocForm)
        @php
            $modalTitle = ($editDocId ? 'Editar' : 'Nuevo') . ' documento — ' . ($docFormType === 'pliego' ? 'Pliego de condiciones' : 'Reglamento interno');
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:key="doc-form-{{ $editDocId ?? 'new' }}">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100">
                    <h3 class="text-base font-semibold text-zinc-800">{{ $modalTitle }}</h3>
                    <button wire:click="closeDocForm" class="text-zinc-400 hover:text-zinc-600">
                        <flux:icon icon="x-mark" class="w-5 h-5" />
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Título <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="docTitle"
                            placeholder="Ej: Pliego de condiciones DO Rioja 2026"
                            class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        @error('docTitle') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Versión</label>
                            <input type="text" wire:model="docVersion" placeholder="Ej: 2.1"
                                class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Fecha de vigencia</label>
                            <input type="date" wire:model="docEffectiveDate"
                                class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Estado</label>
                        <select wire:model="docStatus"
                            class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            <option value="draft">Borrador</option>
                            <option value="active">Vigente</option>
                            <option value="archived">Archivado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Contenido / resumen</label>
                        <textarea wire:model="docContent" rows="5"
                            placeholder="Descripción, puntos clave o texto completo del documento..."
                            class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-zinc-100">
                    <button wire:click="closeDocForm"
                        class="px-4 py-2 text-sm font-medium text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50 transition">
                        Cancelar
                    </button>
                    <button wire:click="saveDocument"
                        class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        {{ $editDocId ? 'Guardar cambios' : 'Crear documento' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
