<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Gestión Territorial"
        description="Administra los catálogos de parcelas: parajes, valles, suelos, riegos y más"
    />

    {{-- Tabs --}}
    <div class="border-b border-zinc-200">
        <nav class="-mb-px flex gap-1 overflow-x-auto">
            @foreach ([
                'sites'          => ['label' => 'Parajes',         'icon' => 'map-pin'],
                'valleys'        => ['label' => 'Valles',          'icon' => 'globe-europe-africa'],
                'soil_types'     => ['label' => 'Tipos de Suelo',  'icon' => 'squares-2x2'],
                'irrigation_types' => ['label' => 'Tipos de Riego', 'icon' => 'beaker'],
                'topographies'   => ['label' => 'Topografía',      'icon' => 'chart-bar'],
                'property_types' => ['label' => 'Tipos de Propiedad', 'icon' => 'document-text'],
                'training_systems' => ['label' => 'Sistemas de Conducción', 'icon' => 'arrow-trending-up'],
            ] as $key => $cfg)
                <button
                    wire:click="$set('tab', '{{ $key }}')"
                    class="flex items-center gap-1.5 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors
                        {{ $tab === $key
                            ? 'border-agro-600 text-agro-700'
                            : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}"
                >
                    <flux:icon icon="{{ $cfg['icon'] }}" class="size-4" />
                    {{ $cfg['label'] }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- ── TAB: PARAJES ── --}}
    @if ($tab === 'sites')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Panel izquierdo: selección en cascada --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-agro-50 flex items-center justify-center">
                            <flux:icon icon="map-pin" class="size-4 text-agro-600" />
                        </div>
                        <h3 class="font-semibold text-zinc-900">Seleccionar ubicación</h3>
                    </div>
                </x-slot:header>

                <div class="space-y-3">
                    <flux:field>
                        <flux:label>Comunidad Autónoma</flux:label>
                        <flux:select wire:model.live="selectedCommunityId">
                            <option value="">Seleccionar...</option>
                            @foreach ($communities as $community)
                                <option value="{{ $community->id }}">{{ $community->name }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Provincia</flux:label>
                        <flux:select wire:model.live="selectedProvinceId" :disabled="!$selectedCommunityId">
                            <option value="">Seleccionar...</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <div wire:key="mun-wrapper-{{ $selectedProvinceId }}">
                        <flux:field>
                            <flux:label>Municipio</flux:label>
                            <flux:select wire:model.live="selectedMunicipalityId" :disabled="!$selectedProvinceId">
                                <option value="">Seleccionar...</option>
                                @foreach ($municipalities as $mun)
                                    <option value="{{ $mun->id }}">{{ $mun->name }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    </div>
                </div>
            </x-agro.card>

            {{-- Panel derecho: parajes del municipio --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
                            <flux:icon icon="home" class="size-4 text-blue-600" />
                        </div>
                        <h3 class="font-semibold text-zinc-900">
                            @if ($selectedMunicipalityId)
                                Parajes de {{ $municipalities->firstWhere('id', $selectedMunicipalityId)?->name ?? '…' }}
                            @elseif ($selectedProvinceId)
                                Parajes — selecciona municipio
                            @else
                                Parajes
                            @endif
                        </h3>
                        @if ($selectedMunicipalityId)
                            <flux:badge color="zinc" size="sm">{{ $sites->count() }}</flux:badge>
                        @endif
                    </div>
                </x-slot:header>

                @if ($selectedMunicipalityId)
                    {{-- Añadir paraje --}}
                    <div class="flex gap-2">
                        <flux:input
                            wire:model="newSiteName"
                            placeholder="Nombre del paraje..."
                            class="flex-1"
                        />
                        <flux:button wire:click="addSite" variant="primary" size="sm" icon="plus">
                            Añadir
                        </flux:button>
                    </div>
                    @error('newSiteName')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    {{-- Lista de parajes --}}
                    <div class="mt-4 space-y-1 max-h-72 overflow-y-auto">
                        @forelse ($sites as $site)
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-zinc-50 group">
                                @if ($editingSiteId === $site->id)
                                    <flux:input wire:model="editingSiteName" class="flex-1 text-sm" />
                                    <flux:button wire:click="saveEditSite" variant="primary" size="xs">Guardar</flux:button>
                                    <flux:button wire:click="cancelEditSite" variant="ghost" size="xs">Cancelar</flux:button>
                                @else
                                    <span class="flex-1 text-sm text-zinc-700">{{ $site->name }}</span>
                                    @if ($site->user_id)
                                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            wire:click="startEditSite({{ $site->id }}, '{{ addslashes($site->name) }}')"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                            title="Editar"
                                        >
                                            <flux:icon icon="pencil" class="size-3.5" />
                                        </button>
                                        <button
                                            wire:click="deleteSite({{ $site->id }})"
                                            wire:confirm="¿Eliminar el paraje '{{ $site->name }}'?"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                            title="Eliminar"
                                        >
                                            <flux:icon icon="trash" class="size-3.5" />
                                        </button>
                                    </div>
                                    @else
                                    @php $siteHidden = in_array($site->id, $hiddenIds) @endphp
                                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            wire:click="toggleSiteVisibility({{ $site->id }})"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded transition-colors {{ $siteHidden ? 'text-zinc-300 hover:text-zinc-500' : 'text-agro-500 hover:text-agro-700' }}"
                                            title="{{ $siteHidden ? 'Activar en mis selects' : 'Ocultar de mis selects' }}"
                                        >
                                            <flux:icon icon="{{ $siteHidden ? 'eye-slash' : 'eye' }}" class="size-3.5" />
                                        </button>
                                    </div>
                                    @endif
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-zinc-400 text-center py-6">No hay parajes. Añade el primero.</p>
                        @endforelse
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-zinc-400">
                        <flux:icon icon="map-pin" class="size-10 mb-3 opacity-30" />
                        <p class="text-sm">Selecciona comunidad → provincia → municipio</p>
                    </div>
                @endif
            </x-agro.card>
        </div>

    {{-- ── TABS CATÁLOGOS SIMPLES ── --}}
    @else
        @php
            $tabLabels = [
                'valleys'          => ['title' => 'Valles',                   'desc' => 'Zonas vitícolas y valles geográficos', 'hasCode' => true,  'hasDesc' => true],
                'soil_types'       => ['title' => 'Tipos de Suelo',           'desc' => 'Clasificación de suelos de parcelas',  'hasCode' => false, 'hasDesc' => true],
                'irrigation_types' => ['title' => 'Tipos de Riego',           'desc' => 'Métodos de riego disponibles',         'hasCode' => false, 'hasDesc' => true],
                'topographies'     => ['title' => 'Topografía',               'desc' => 'Tipos de relieve del terreno',         'hasCode' => false, 'hasDesc' => true],
                'property_types'   => ['title' => 'Tipos de Propiedad',       'desc' => 'Régimen de tenencia de la parcela',    'hasCode' => false, 'hasDesc' => false],
                'training_systems' => ['title' => 'Sistemas de Conducción',   'desc' => 'Espaldera, vaso, lira, etc.',          'hasCode' => false, 'hasDesc' => true],
            ];
            $cfg = $tabLabels[$tab] ?? ['title' => '', 'desc' => '', 'hasCode' => false, 'hasDesc' => false];
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- Formulario añadir --}}
            <div class="lg:col-span-2">
                <x-agro.card>
                    <x-slot:header>
                        <h3 class="font-semibold text-zinc-900">Añadir {{ $cfg['title'] }}</h3>
                    </x-slot:header>

                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>Nombre *</flux:label>
                            <flux:input wire:model="newName" placeholder="Nombre..." />
                            <flux:error name="newName" />
                        </flux:field>

                        @if ($cfg['hasCode'])
                            <flux:field>
                                <flux:label>Código</flux:label>
                                <flux:input wire:model="newCode" placeholder="Ej: V01" />
                            </flux:field>
                        @endif

                        @if ($cfg['hasDesc'])
                            <flux:field>
                                <flux:label>Descripción</flux:label>
                                <flux:input wire:model="newDescription" placeholder="Descripción opcional..." />
                            </flux:field>
                        @endif

                        <flux:button wire:click="addCatalogItem('{{ $tab }}')" variant="primary" icon="plus" class="w-full">
                            Añadir
                        </flux:button>
                    </div>
                </x-agro.card>
            </div>

            {{-- Lista --}}
            <div class="lg:col-span-3">
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-zinc-900">{{ $cfg['title'] }}</h3>
                                <p class="text-xs text-zinc-400 mt-0.5">{{ $cfg['desc'] }}</p>
                            </div>
                            <flux:badge color="zinc" size="sm">{{ $catalogItems->count() }}</flux:badge>
                        </div>
                    </x-slot:header>

                    <flux:input
                        wire:model.live.debounce.300ms="catalogSearch"
                        placeholder="Buscar..."
                        icon="magnifying-glass"
                        class="mb-4"
                    />

                    <div class="space-y-1 max-h-[500px] overflow-y-auto">
                        @forelse ($catalogItems as $item)
                            <div class="flex items-center gap-2 px-3 py-2.5 rounded-lg hover:bg-zinc-50 group">
                                @if ($editingId === $item->id)
                                    <div class="flex-1 flex gap-2 flex-wrap">
                                        <flux:input wire:model="editingName" placeholder="Nombre" class="flex-1 min-w-0 text-sm" />
                                        @if ($cfg['hasDesc'])
                                            <flux:input wire:model="editingDescription" placeholder="Descripción" class="flex-1 min-w-0 text-sm" />
                                        @endif
                                    </div>
                                    <flux:button wire:click="saveEdit('{{ $tab }}')" variant="primary" size="xs">Guardar</flux:button>
                                    <flux:button wire:click="cancelEdit" variant="ghost" size="xs">Cancelar</flux:button>
                                @else
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm text-zinc-800 font-medium">{{ $item->name }}</span>
                                        @if (!empty($item->description))
                                            <span class="text-xs text-zinc-400 ml-2">{{ $item->description }}</span>
                                        @endif
                                        @if (!empty($item->code))
                                            <span class="ml-2 text-xs font-mono bg-zinc-100 text-zinc-600 px-1.5 py-0.5 rounded">{{ $item->code }}</span>
                                        @endif
                                    </div>
                                    @if ($item->user_id)
                                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            wire:click="startEdit({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ addslashes($item->description ?? '') }}')"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                            title="Editar"
                                        >
                                            <flux:icon icon="pencil" class="size-3.5" />
                                        </button>
                                        <button
                                            wire:click="deleteCatalogItem('{{ $tab }}', {{ $item->id }})"
                                            wire:confirm="¿Eliminar '{{ $item->name }}'? Esta acción no se puede deshacer."
                                            class="inline-flex items-center justify-center w-7 h-7 rounded text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                            title="Eliminar"
                                        >
                                            <flux:icon icon="trash" class="size-3.5" />
                                        </button>
                                    </div>
                                    @else
                                    @php $itemHidden = in_array($item->id, $hiddenIds) @endphp
                                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            wire:click="toggleCatalogVisibility('{{ $tab }}', {{ $item->id }})"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded transition-colors {{ $itemHidden ? 'text-zinc-300 hover:text-zinc-500' : 'text-agro-500 hover:text-agro-700' }}"
                                            title="{{ $itemHidden ? 'Activar en mis selects' : 'Ocultar de mis selects' }}"
                                        >
                                            <flux:icon icon="{{ $itemHidden ? 'eye-slash' : 'eye' }}" class="size-3.5" />
                                        </button>
                                    </div>
                                    @endif
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-10 text-zinc-400">
                                <flux:icon icon="inbox" class="size-10 mx-auto mb-2 opacity-30" />
                                <p class="text-sm">No hay elementos. Añade el primero.</p>
                            </div>
                        @endforelse
                    </div>
                </x-agro.card>
            </div>
        </div>
    @endif
</div>
