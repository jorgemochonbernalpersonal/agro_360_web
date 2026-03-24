<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Certificaciones DO"
        description="Certificaciones activas de los viticultores adscritos a la denominación."
    />

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-agro.stat-card label="Vigentes" :value="$totalActive" icon="check-badge" color="emerald" />
        <x-agro.stat-card label="Próximas a vencer (60d)" :value="$totalExpiring" icon="clock" color="amber" />
        <x-agro.stat-card label="Caducadas" :value="$totalExpired" icon="x-circle" color="red" />
    </div>

    {{-- Alerta si hay caducadas o próximas a vencer --}}
    @if($totalExpired > 0 || $totalExpiring > 0)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 flex items-start gap-3">
            <flux:icon icon="exclamation-triangle" class="size-5 text-amber-500 mt-0.5 shrink-0" />
            <div class="text-sm text-amber-700">
                @if($totalExpired > 0)
                    <strong>{{ $totalExpired }} certificación{{ $totalExpired !== 1 ? 'es' : '' }} caducada{{ $totalExpired !== 1 ? 's' : '' }}.</strong>
                @endif
                @if($totalExpiring > 0)
                    {{ $totalExpiring }} vence{{ $totalExpiring !== 1 ? 'n' : '' }} en los próximos 60 días.
                @endif
                Revisa el estado con los viticultores afectados.
            </div>
        </div>
    @endif

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterVit" label="Viticultor">
            <option value="">Todos</option>
            @foreach($viticulturists as $vit)
                <option value="{{ $vit->id }}">{{ $vit->name }}</option>
            @endforeach
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="filterType" label="Tipo">
            <option value="">Todos los tipos</option>
            @foreach($certTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="filterStatus" label="Estado">
            <option value="">Cualquier estado</option>
            <option value="active">Vigentes</option>
            <option value="expiring">Próximas a vencer</option>
            <option value="expired">Caducadas</option>
        </x-agro.filter-select>

        <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition px-2 py-1.5">
            Limpiar
        </button>
    </x-agro.filter-bar>

    {{-- Tabla --}}
    <x-agro.data-table
        :headers="['Viticultor', 'Tipo', 'Organismo', 'Nº certificado', 'Emisión', 'Vencimiento', 'Estado']"
        emptyMessage="No se encontraron certificaciones con los filtros seleccionados."
    >
        @foreach($certifications as $cert)
            <tr class="hover:bg-zinc-50 transition">
                <td class="px-6 py-3 text-sm font-medium text-zinc-800">
                    {{ $cert->viticulturist?->name ?? '—' }}
                </td>
                <td class="px-6 py-3">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs border
                        @switch($cert->certification_type)
                            @case('ecologico') bg-green-50 text-green-700 border-green-200 @break
                            @case('produccion_integrada') bg-teal-50 text-teal-700 border-teal-200 @break
                            @case('globalgap') bg-blue-50 text-blue-700 border-blue-200 @break
                            @case('denominacion_origen') bg-emerald-50 text-emerald-700 border-emerald-200 @break
                            @case('indicacion_geografica') bg-violet-50 text-violet-700 border-violet-200 @break
                            @default bg-zinc-50 text-zinc-700 border-zinc-200
                        @endswitch
                    ">
                        {{ $cert->certification_type_label }}
                    </span>
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    {{ $cert->certifying_body ?? '—' }}
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    {{ $cert->certificate_number ?? '—' }}
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    {{ $cert->issue_date?->format('d/m/Y') ?? '—' }}
                </td>
                <td class="px-6 py-3 text-sm">
                    @if($cert->expiry_date)
                        <span class="{{ $cert->is_expired ? 'text-red-600 font-medium' : ($cert->is_expiring_soon ? 'text-amber-600 font-medium' : 'text-zinc-500') }}">
                            {{ $cert->expiry_date->format('d/m/Y') }}
                        </span>
                    @else
                        <span class="text-zinc-300">Sin caducidad</span>
                    @endif
                </td>
                <td class="px-6 py-3">
                    @if($cert->is_expired)
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-red-50 text-red-700 border border-red-200">Caducada</span>
                    @elseif($cert->is_expiring_soon)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-amber-50 text-amber-700 border border-amber-200">
                            <flux:icon icon="clock" class="size-3" /> Pronto vence
                        </span>
                    @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-700 border border-emerald-200">Vigente</span>
                    @endif
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $certifications->links() }}
        </x-slot>
    </x-agro.data-table>

</div>
