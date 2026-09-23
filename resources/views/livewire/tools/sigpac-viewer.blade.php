@php
    $fmt = fn (float $n, int $d = 2) => number_format($n, $d, ',', '.');
    $fields = [
        'province' => 'Provincia',
        'municipality' => 'Municipio',
        'aggregate' => 'Agregado',
        'zone' => 'Zona',
        'polygon' => 'Polígono',
        'parcel' => 'Parcela',
        'enclosure' => 'Recinto',
    ];
@endphp

<div class="space-y-8">
    <form wire:submit="search" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <div class="grid grid-cols-4 sm:grid-cols-7 gap-3">
            @foreach ($fields as $field => $label)
                <div @class(['col-span-2 sm:col-span-1' => in_array($field, ['province', 'municipality', 'polygon', 'parcel'], true)])>
                    <label for="sigpac-{{ $field }}" class="block text-xs font-medium text-gray-700 mb-1">{{ $label }}</label>
                    <input
                        id="sigpac-{{ $field }}"
                        type="text"
                        inputmode="numeric"
                        autocomplete="off"
                        @if ($field === 'province') wire:model.blur="province" placeholder="13" @else wire:model="{{ $field }}" @endif
                        class="block w-full rounded-lg border-gray-300 shadow-sm text-center"
                    >
                </div>
            @endforeach
        </div>
        <p class="mt-2 text-xs text-gray-500">Truco: pega la referencia completa (p. ej. <code>13:87:0:0:10:20:1</code>) en el campo Provincia y se reparte sola.</p>

        @if ($errors->any())
            <ul class="mt-3 text-sm text-red-600 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <button type="submit" class="mt-4 w-full sm:w-auto px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white font-semibold">
            <span wire:loading.remove wire:target="search">Ver recinto</span>
            <span wire:loading wire:target="search">Consultando SIGPAC…</span>
        </button>
    </form>

    @if ($status === \App\Services\Tools\SigpacParcelLookup::STATUS_NOT_FOUND)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800">
            No existe ningún recinto con esa referencia. Revisa los números con tu declaración de la PAC: el agregado y la zona suelen ser 0.
        </div>
    @elseif ($status === \App\Services\Tools\SigpacParcelLookup::STATUS_UNAVAILABLE)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
            El servicio público de SIGPAC no responde ahora mismo. Inténtalo de nuevo en unos minutos.
        </div>
    @endif

    <div @class(['grid lg:grid-cols-5 gap-6', 'hidden' => ! $result])>
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div wire:ignore id="sigpac-viewer-map" class="w-full h-80 lg:h-[28rem] bg-gray-100"></div>
        </div>

        @if ($result)
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm p-6" aria-live="polite">
                <p class="text-xs text-gray-500">Recinto {{ $result['reference'] }}{{ $result['province'] ? ' · '.$result['province'] : '' }}</p>
                <div class="grid grid-cols-2 gap-3 my-4">
                    <div class="rounded-xl bg-[var(--color-agro-green-dark)] text-white p-4">
                        <div class="text-sm text-white/70">Superficie</div>
                        <div class="text-2xl font-bold">{{ $result['area_ha'] !== null ? $fmt($result['area_ha'], 4) : '—' }} ha</div>
                    </div>
                    <div class="rounded-xl bg-[var(--color-agro-green-dark)] text-white p-4">
                        <div class="text-sm text-white/70">Uso SIGPAC</div>
                        <div class="text-2xl font-bold">{{ $result['use_code'] ?? '—' }}</div>
                        @if ($result['use_label'])
                            <div class="text-xs text-white/80">{{ $result['use_label'] }}</div>
                        @endif
                    </div>
                </div>

                <dl class="divide-y text-sm">
                    @if ($result['area_ha'] !== null)
                        <div class="flex justify-between py-2"><dt class="text-gray-600">Superficie en m²</dt><dd class="font-medium">{{ $fmt($result['area_ha'] * 10000, 0) }} m²</dd></div>
                    @endif
                    <div class="flex justify-between py-2"><dt class="text-gray-600">Pendiente media</dt><dd class="font-medium">{{ $result['slope_percent'] !== null ? $fmt($result['slope_percent'], 1).' %' : '—' }}</dd></div>
                    <div class="flex justify-between py-2"><dt class="text-gray-600">Coeficiente de regadío</dt><dd class="font-medium">{{ $result['irrigation_coef'] !== null ? $fmt($result['irrigation_coef'], 0).' %' : '—' }}</dd></div>
                    <div class="flex justify-between py-2"><dt class="text-gray-600">Altitud</dt><dd class="font-medium">{{ $result['altitude_m'] !== null ? $fmt($result['altitude_m'], 0).' m' : '—' }}</dd></div>
                    @if ($result['eligibility'] !== null)
                        <div class="flex justify-between py-2"><dt class="text-gray-600">Admisibilidad pastos (CAP)</dt><dd class="font-medium">{{ $fmt($result['eligibility'], 0) }} %</dd></div>
                    @endif
                    <div class="flex justify-between py-2">
                        <dt class="text-gray-600">Incidencias</dt>
                        <dd class="font-medium">{{ $result['incidences'] ? implode(', ', $result['incidences']) : 'Ninguna' }}</dd>
                    </div>
                </dl>
                @if ($result['incidences'])
                    <p class="mt-2 text-xs text-gray-500">Códigos de incidencia de SIGPAC. Consulta su significado en el visor oficial de tu comunidad o con tu entidad de la PAC.</p>
                @endif

                <a href="{{ route('register') }}" rel="nofollow" class="mt-6 block text-center px-5 py-3 rounded-lg bg-[var(--color-agro-green-dark)] text-white font-semibold">Guardar esta parcela en mi cuaderno</a>
            </div>
        @endif
    </div>
</div>

@script
<script>
    let map = null;
    let layer = null;

    $wire.on('sigpac-parcel-found', async ({ wkt }) => {
        const L = await window.loadLeaflet();
        const coords = window.parseWKT(wkt);
        if (!coords.length) return;

        // El contenedor acaba de hacerse visible: esperar al repintado antes de medirlo.
        await new Promise(requestAnimationFrame);

        if (!map) {
            map = L.map('sigpac-viewer-map');
            const pnoa = L.tileLayer.wms('https://www.ign.es/wms-inspire/pnoa-ma', {
                layers: 'OI.OrthoimageCoverage',
                format: 'image/jpeg',
                attribution: '© <a href="https://www.ign.es">IGN España – PNOA</a>',
                maxZoom: 20,
                version: '1.3.0',
            });
            const street = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
                maxZoom: 19,
            });
            pnoa.addTo(map);
            L.control.layers({ 'Ortofoto PNOA': pnoa, 'Mapa': street }).addTo(map);
        }

        map.invalidateSize();
        if (layer) layer.remove();
        layer = L.polygon(coords, { color: '#facc15', weight: 3, fillOpacity: 0.15 }).addTo(map);
        map.fitBounds(layer.getBounds(), { padding: [30, 30] });
    });
</script>
@endscript
