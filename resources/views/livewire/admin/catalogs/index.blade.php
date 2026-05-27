<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Catálogos multilingüe') }}"
        :description="__('Gestiona las traducciones ES / EN / CA de los catálogos del sistema.')"
    />

    {{-- Tabs de catálogo --}}
    <div class="flex gap-1 border-b border-zinc-200">
        @foreach($catalogs as $key => $cat)
            <button
                wire:click="$set('activeTab', '{{ $key }}')"
                class="px-4 py-2 text-sm font-medium rounded-t-lg transition
                    {{ $activeTab === $key
                        ? 'bg-white border border-b-white border-zinc-200 text-zinc-900 -mb-px'
                        : 'text-zinc-500 hover:text-zinc-700' }}"
            >
                {{ $cat['label'] }}
            </button>
        @endforeach
    </div>

    {{-- Tabla de registros --}}
    @if($records->isEmpty())
        <x-agro.empty-state
            icon="language"
            :message="__('Sin registros')"
            :description="__('Este catálogo no tiene entradas todavía.')"
        />
    @else
        <x-agro.card>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100">
                        <th class="text-left py-2 px-3 font-medium text-zinc-500 w-8">#</th>
                        @foreach($locales as $locale)
                            <th class="text-left py-2 px-3 font-medium text-zinc-500 uppercase text-xs">
                                {{ $locale }}
                            </th>
                        @endforeach
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50">
                    @foreach($records as $record)
                        <tr class="hover:bg-zinc-50 transition">
                            <td class="py-2 px-3 text-zinc-400 text-xs">{{ $record->id }}</td>
                            @foreach($locales as $locale)
                                <td class="py-2 px-3">
                                    @php $val = $record->getTranslation('name', $locale, false); @endphp
                                    @if($val)
                                        <span class="text-zinc-800">{{ $val }}</span>
                                    @else
                                        <span class="text-zinc-300 italic text-xs">{{ __('Sin traducción') }}</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="py-2 px-3 text-right">
                                <flux:button
                                    wire:click="openEdit({{ $record->id }}, '{{ $activeTab }}')"
                                    variant="ghost"
                                    size="sm"
                                    icon="pencil"
                                />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-agro.card>
    @endif

    {{-- Modal de edición --}}
    @if($showModal)
        <flux:modal name="catalog-edit" :show="true" wire:close="closeModal" class="max-w-2xl">
            <div class="p-6 space-y-5">
                <h3 class="text-base font-semibold text-zinc-900">
                    {{ __('Editar traducciones') }}
                    <span class="text-zinc-400 font-normal ml-1">#{{ $editingId }}</span>
                </h3>

                @foreach($catalog['fields'] as $field => $fieldLabel)
                    <div class="space-y-2">
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">
                            {{ $fieldLabel }}
                        </p>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach($locales as $locale)
                                <div>
                                    <label class="block text-xs text-zinc-400 mb-1 uppercase">{{ $locale }}</label>
                                    @if(in_array($field, ['description', 'symptoms', 'lifecycle', 'prevention_methods']))
                                        <flux:textarea
                                            wire:model="translations.{{ $field }}.{{ $locale }}"
                                            rows="3"
                                            class="text-sm"
                                        />
                                    @else
                                        <flux:input
                                            wire:model="translations.{{ $field }}.{{ $locale }}"
                                            class="text-sm"
                                        />
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if(!$loop->last)
                        <div class="border-t border-zinc-100"></div>
                    @endif
                @endforeach

                <div class="flex justify-end gap-3 pt-2 border-t border-zinc-100">
                    <flux:button wire:click="closeModal" variant="ghost">
                        {{ __('Cancelar') }}
                    </flux:button>
                    <flux:button wire:click="save" variant="primary">
                        {{ __('Guardar') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

</div>
