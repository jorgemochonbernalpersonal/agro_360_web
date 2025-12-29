<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            🛰️ Historial de Imágenes Satélite
        </h3>
        <select wire:model.live="months" class="text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            <option value="3">Últimos 3 meses</option>
            <option value="6">Últimos 6 meses</option>
            <option value="12">Último año</option>
            <option value="24">Últimos 2 años</option>
        </select>
    </div>

    @if(count($history) > 0)
        {{-- Timeline Slider --}}
        <div class="mb-6">
            <div class="relative">
                {{-- Timeline bar --}}
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full flex">
                        @foreach($history as $index => $record)
                            <div 
                                wire:click="selectRecord({{ $index }})"
                                class="h-full cursor-pointer transition-all hover:opacity-80"
                                style="flex: 1; background-color: {{ $record['ndvi_color'] }};"
                                title="{{ $record['date'] }}: NDVI {{ $record['ndvi_formatted'] }}"
                            ></div>
                        @endforeach
                    </div>
                </div>
                
                {{-- Timeline markers --}}
                <div class="flex justify-between mt-2 text-xs text-gray-500">
                    @if(count($history) > 0)
                        <span>{{ $history[count($history) - 1]['date'] }}</span>
                        <span>{{ $history[0]['date'] }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Selected Record Details --}}
        @if($selectedIndex !== null && isset($history[$selectedIndex]))
            @php $selected = $history[$selectedIndex]; @endphp
            <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl p-6 border border-gray-100 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl" 
                             style="background-color: {{ $selected['ndvi_color'] }}20;">
                            {{ $selected['health_emoji'] }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $selected['date'] }}</div>
                            <div class="text-sm text-gray-500">{{ $selected['image_source'] }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Estado</div>
                        <div class="font-semibold" style="color: {{ $selected['ndvi_color'] }}">
                            {{ $selected['health_text'] }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-3 bg-white rounded-lg shadow-sm">
                        <div class="text-xs text-gray-500 mb-1">NDVI</div>
                        <div class="text-xl font-bold" style="color: {{ $selected['ndvi_color'] }}">
                            {{ $selected['ndvi_formatted'] }}
                        </div>
                        <div class="text-xs text-gray-400">{{ $selected['trend_icon'] }}</div>
                    </div>
                    <div class="text-center p-3 bg-white rounded-lg shadow-sm">
                        <div class="text-xs text-gray-500 mb-1">NDWI</div>
                        <div class="text-xl font-bold text-blue-600">
                            {{ $selected['ndwi_formatted'] }}
                        </div>
                    </div>
                    <div class="text-center p-3 bg-white rounded-lg shadow-sm">
                        <div class="text-xs text-gray-500 mb-1">Temperatura</div>
                        <div class="text-xl font-bold text-gray-900">
                            {{ $selected['temperature'] !== null ? number_format($selected['temperature'], 1) . '°C' : 'N/A' }}
                        </div>
                    </div>
                    <div class="text-center p-3 bg-white rounded-lg shadow-sm">
                        <div class="text-xs text-gray-500 mb-1">Nubosidad</div>
                        <div class="text-xl font-bold text-gray-900">
                            {{ $selected['cloud_coverage'] !== null ? $selected['cloud_coverage'] . '%' : 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- History Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-600">Fecha</th>
                        <th class="px-4 py-2 text-center text-gray-600">NDVI</th>
                        <th class="px-4 py-2 text-center text-gray-600">NDWI</th>
                        <th class="px-4 py-2 text-center text-gray-600">Estado</th>
                        <th class="px-4 py-2 text-center text-gray-600">Tendencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($history as $index => $record)
                        <tr wire:click="selectRecord({{ $index }})" 
                            class="cursor-pointer transition-colors {{ $selectedIndex === $index ? 'bg-green-50' : 'hover:bg-gray-50' }}">
                            <td class="px-4 py-3 font-medium">{{ $record['date'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1">
                                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $record['ndvi_color'] }}"></span>
                                    {{ $record['ndvi_formatted'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-blue-600">{{ $record['ndwi_formatted'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1">
                                    {{ $record['health_emoji'] }}
                                    <span class="text-{{ $record['health_color'] }}-600">{{ $record['health_text'] }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-xl">{{ $record['trend_icon'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-12 text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h4 class="text-lg font-medium mb-1">Sin historial de imágenes</h4>
            <p class="text-sm">No hay datos de satélite para el período seleccionado</p>
        </div>
    @endif
</div>
