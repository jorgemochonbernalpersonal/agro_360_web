<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Organizaciones') }}"
        :description="__('Bodegas y Denominaciones de Origen registradas en el sistema')"
    >
        <x-slot:actions>
            <flux:button wire:click="openCreate" variant="primary" icon="plus">{{ __('Nueva Organización') }}</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-agro.stat-card :label="__('Total')"            :value="$stats['total']"         icon="building-office-2" color="purple" />
        <x-agro.stat-card :label="__('Bodegas')"          :value="$stats['wineries']"      icon="beaker"            color="agro"   />
        <x-agro.stat-card :label="__('DOs')"              :value="$stats['denominations']" icon="shield-check"      color="blue"   />
        <x-agro.stat-card :label="__('Sin organización')" :value="$stats['orphans']"       icon="exclamation-circle" color="orange" />
    </div>

    {{-- Table --}}
    <x-agro.card :padding="false">
        {{-- Filters --}}
        <div class="px-6 py-4">
            <x-agro.filter-bar>
                <x-agro.filter-input wire:model.live="search" :placeholder="__('Buscar por nombre, CIF, ciudad...')" />
                <x-agro.filter-select wire:model.live="typeFilter">
                    <option value="">{{ __('Todos los tipos') }}</option>
                    <option value="winery">{{ __('Bodegas') }}</option>
                    <option value="denomination_of_origin">{{ __('Denominaciones de Origen') }}</option>
                </x-agro.filter-select>
            </x-agro.filter-bar>
        </div>

        <x-agro.table>
            <x-slot:head>
                <x-agro.th>{{ __('Nombre') }}</x-agro.th>
                <x-agro.th>{{ __('Tipo') }}</x-agro.th>
                <x-agro.th>{{ __('CIF / NIF') }}</x-agro.th>
                <x-agro.th>{{ __('Ciudad') }}</x-agro.th>
                <x-agro.th>{{ __('Usuario principal') }}</x-agro.th>
                <x-agro.th>{{ __('Miembros') }}</x-agro.th>
                <x-agro.th>{{ __('Estado') }}</x-agro.th>
                <x-agro.th></x-agro.th>
            </x-slot:head>
            <x-slot:body>
                @forelse($organizations as $org)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-white">
                            <div class="flex items-center gap-1.5">
                                <span>{{ $org->name }}</span>
                                @if($org->isInternal())
                                    <flux:badge color="yellow" size="sm">{{ __('Interno') }}</flux:badge>
                                @endif
                            </div>
                            @if($org->parent)
                                <div class="text-xs text-zinc-400">↳ {{ $org->parent->name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($org->isWinery())
                                <flux:badge color="pink" size="sm">{{ __('Bodega') }}</flux:badge>
                            @else
                                <flux:badge color="indigo" size="sm">DO</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 font-mono">
                            {{ $org->vat_number ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $org->city ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                            @if($org->ownerUser)
                                <div>{{ $org->ownerUser->name }}</div>
                                <div class="text-xs text-zinc-400">{{ $org->ownerUser->email }}</div>
                            @else
                                <span class="text-zinc-300 dark:text-zinc-600">{{ __('Sin usuario') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-zinc-500">
                            {{ $org->members_count }}
                        </td>
                        <td class="px-4 py-3">
                            @if($org->active)
                                <flux:badge color="green" size="sm">{{ __('Activa') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ __('Inactiva') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 justify-end">
                                <flux:button wire:click="openEdit({{ $org->id }})" variant="ghost" size="sm" icon="pencil" />
                                <flux:button
                                    wire:click="delete({{ $org->id }})"
                                    wire:confirm="{{ __('¿Eliminar \':name\'? Los usuarios vinculados quedarán sin organización.', ['name' => $org->name]) }}"
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    class="text-red-500 hover:text-red-600"
                                />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-sm text-zinc-400">{{ __('No se encontraron organizaciones.') }}</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-agro.table>

        <x-agro-pagination :paginator="$organizations" />
    </x-agro.card>

    {{-- Create / Edit Modal --}}
    @if($showModal)
        <flux:modal name="org-modal" :show="true" wire:close="closeModal" class="max-w-2xl">
            <div class="p-6 space-y-5">
                <div>
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-white">
                        {{ $editingId ? __('Editar organización') : __('Nueva organización') }}
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nombre --}}
                    <div class="md:col-span-2">
                        <flux:input
                            wire:model="name"
                            :label="__('Nombre')"
                            placeholder="{{ __('Bodega / Denominación de Origen') }}"
                        />
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tipo --}}
                    <div>
                        <flux:select wire:model.live="type" :label="__('Tipo')">
                            <option value="winery">{{ __('Bodega') }}</option>
                            <option value="denomination_of_origin">{{ __('Denominación de Origen') }}</option>
                        </flux:select>
                        @error('type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- CIF/NIF --}}
                    <div>
                        <flux:input wire:model="vat_number" :label="__('CIF / NIF')" placeholder="B12345678" />
                        @error('vat_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <flux:input wire:model="email" type="email" :label="__('Email')" placeholder="info@bodega.es" />
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Teléfono --}}
                    <div>
                        <flux:input wire:model="phone" :label="__('Teléfono')" placeholder="+34 600 000 000" />
                    </div>

                    {{-- Dirección --}}
                    <div class="md:col-span-2">
                        <flux:input wire:model="address" :label="__('Dirección')" :placeholder="__('Calle Mayor, 1')" />
                    </div>

                    {{-- Ciudad --}}
                    <div>
                        <flux:input wire:model="city" :label="__('Ciudad')" :placeholder="__('Logroño')" />
                    </div>

                    {{-- CP --}}
                    <div>
                        <flux:input wire:model="postal_code" :label="__('Código postal')" placeholder="26001" />
                    </div>

                    {{-- Provincia --}}
                    <div>
                        <flux:select wire:model="province_id" :label="__('Provincia')">
                            <option value="">{{ __('— Sin provincia —') }}</option>
                            @foreach($provinces as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                            @endforeach
                        </flux:select>
                    </div>

                    {{-- Web --}}
                    <div>
                        <flux:input wire:model="website" :label="__('Web')" placeholder="https://bodega.es" />
                        @error('website') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Usuario principal --}}
                    <div class="md:col-span-2">
                        <flux:select wire:model="owner_user_id" :label="__('Usuario principal (propietario)')">
                            <option value="">{{ __('— Sin usuario —') }}</option>
                            @foreach($ownerUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }}) · {{ $u->role }}</option>
                            @endforeach
                        </flux:select>
                    </div>

                    {{-- DO padre (solo para bodegas) --}}
                    @if($type === 'winery')
                        <div class="md:col-span-2">
                            <flux:select wire:model="parent_id" :label="__('Denominación de Origen (DO padre)')">
                                <option value="">{{ __('— Sin DO —') }}</option>
                                @foreach($denominations as $do)
                                    <option value="{{ $do->id }}">{{ $do->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                    @endif

                    {{-- Activa --}}
                    <div class="md:col-span-2 flex items-center gap-2">
                        <flux:checkbox wire:model="active" id="active-check" />
                        <label for="active-check" class="text-sm text-zinc-700 dark:text-zinc-300 cursor-pointer">{{ __('Organización activa') }}</label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button wire:click="closeModal" variant="ghost">{{ __('Cancelar') }}</flux:button>
                    <flux:button wire:click="save" variant="primary">
                        {{ $editingId ? __('Guardar cambios') : __('Crear organización') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
