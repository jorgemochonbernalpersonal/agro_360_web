@php
    use App\Services\Tools\DiseaseRiskModel as Risk;

    $levels = [
        Risk::HIGH => ['Alto', 'bg-red-100 text-red-800 border-red-200'],
        Risk::MODERATE => ['Moderado', 'bg-amber-100 text-amber-800 border-amber-200'],
        Risk::LOW => ['Bajo', 'bg-green-100 text-green-800 border-green-200'],
    ];
    $fmt = fn (float $n, int $d = 1) => number_format($n, $d, ',', '.');
@endphp

<div class="space-y-8">
    {{-- 1. Municipio --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <label for="disease-search" class="block text-lg font-bold text-gray-900 mb-2">1. ¿Dónde está tu viña?</label>
        <div class="relative">
            <input
                id="disease-search"
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Escribe tu municipio (p. ej. Valdepeñas)"
                autocomplete="off"
                class="block w-full rounded-lg border-gray-300 shadow-sm"
            >
            @if ($this->suggestions->isNotEmpty())
                <ul class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg divide-y">
                    @foreach ($this->suggestions as $option)
                        <li wire:key="m-{{ $option->id }}">
                            <button type="button" wire:click="selectMunicipality({{ $option->id }})" class="w-full text-left px-4 py-2 hover:bg-gray-50">
                                <span class="font-medium">{{ $option->name }}</span>
                                <span class="text-sm text-gray-500">· {{ $option->province?->name }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @elseif (! $municipalityId && mb_strlen(trim($search)) >= 3)
                <p class="mt-2 text-sm text-gray-500">No encontramos ese municipio.</p>
            @endif
        </div>
    </div>

    {{-- 2. Riesgo --}}
    @if ($this->municipality)
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6" aria-live="polite">
            <h2 class="text-lg font-bold text-gray-900 mb-1">2. Riesgo en {{ $this->municipality->name }}</h2>
            <p class="text-sm text-gray-500 mb-4">Previsión de Open-Meteo para las coordenadas del municipio. Se actualiza cada pocas horas.</p>

            @if ($this->forecast)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2 pr-2 font-medium">Día</th>
                                <th class="py-2 px-2 font-medium">Mildiu</th>
                                <th class="py-2 px-2 font-medium">Oídio</th>
                                <th class="py-2 px-2 font-medium text-right">T. media</th>
                                <th class="py-2 pl-2 font-medium text-right">Lluvia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->forecast as $day)
                                @php $date = \Carbon\Carbon::parse($day['date'])->locale('es'); @endphp
                                <tr wire:key="d-{{ $day['date'] }}" class="border-b last:border-0">
                                    <td class="py-2 pr-2 font-medium text-gray-900 whitespace-nowrap">{{ $loop->first ? 'Hoy' : ucfirst($date->isoFormat('ddd D')) }}</td>
                                    <td class="py-2 px-2"><span class="inline-block px-2 py-0.5 rounded border text-xs font-semibold {{ $levels[$day['mildew']][1] }}">{{ $levels[$day['mildew']][0] }}</span></td>
                                    <td class="py-2 px-2"><span class="inline-block px-2 py-0.5 rounded border text-xs font-semibold {{ $levels[$day['powdery']][1] }}">{{ $levels[$day['powdery']][0] }} · {{ $day['powdery_index'] }}</span></td>
                                    <td class="py-2 px-2 text-right">{{ $fmt($day['temp_mean']) }} °C</td>
                                    <td class="py-2 pl-2 text-right">{{ $fmt($day['rain']) }} mm</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-xs text-gray-500">
                    Mildiu: regla de los tres dieces (≥10 mm en 48 h y ≥10 °C; requiere brotes de más de 10 cm) y condiciones de humectación para infecciones secundarias.
                    Oídio: índice de Gubler-Thomas (0–100). Son modelos orientativos: confirma siempre en campo antes de tratar.
                </p>
            @else
                <p class="text-red-700">No hemos podido obtener la previsión meteorológica ahora mismo. Inténtalo de nuevo en unos minutos.</p>
            @endif
        </div>

        {{-- 3. Suscripción --}}
        <div class="rounded-2xl border border-[var(--color-agro-green)]/30 bg-[var(--color-agro-green)]/5 p-6">
            @if ($subscribed)
                <p class="text-lg font-semibold text-[var(--color-agro-green-dark)]">¡Listo! Te avisaremos por email cuando el riesgo sea alto en {{ $this->municipality->name }}.</p>
                <p class="text-sm text-gray-600 mt-1">Cada email incluye un enlace para darte de baja cuando quieras.</p>
            @else
                <h2 class="text-lg font-bold text-gray-900 mb-1">3. Recibe el aviso por email</h2>
                <p class="text-gray-600 mb-4">Gratis. Solo te escribimos cuando el riesgo de mildiu u oídio sea <strong>alto</strong> en tu municipio, durante la campaña.</p>

                <form wire:submit="subscribe" class="space-y-4">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <label for="disease-email" class="sr-only">Email</label>
                        <input id="disease-email" type="email" wire:model="email" placeholder="tu@email.com" autocomplete="email" required class="flex-1 rounded-lg border-gray-300 shadow-sm">
                        <button type="submit" class="px-6 py-3 rounded-lg bg-[var(--color-agro-green-dark)] text-white font-semibold">Avísame</button>
                    </div>
                    @error('email') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('municipalityId') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                    <div class="hidden" aria-hidden="true">
                        <label>Web <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
                    </div>

                    <label class="flex items-start gap-2 text-sm text-gray-600">
                        <input type="checkbox" wire:model="consent" class="mt-1 rounded border-gray-300">
                        <span>Acepto recibir las alertas y que Agro365 me escriba sobre este tema. He leído la <a href="{{ route('privacy') }}" class="underline" target="_blank">política de privacidad</a>.</span>
                    </label>
                    @error('consent') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </form>
            @endif
        </div>
    @endif
</div>
