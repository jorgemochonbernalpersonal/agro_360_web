<div class="space-y-6">
    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 grid md:grid-cols-3 gap-4">
        <div>
            <label for="phyto-crop" class="block text-sm font-medium text-gray-700 mb-1">Cultivo</label>
            <select id="phyto-crop" wire:model.live="crop" class="block w-full rounded-lg border-gray-300 shadow-sm">
                <optgroup label="Viñedo">
                    @foreach ($this->cropOptions['vine'] as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </optgroup>
                <optgroup label="Otros cultivos">
                    @foreach ($this->cropOptions['other'] as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </optgroup>
            </select>
        </div>
        <div>
            <label for="phyto-agent" class="block text-sm font-medium text-gray-700 mb-1">Plaga, enfermedad o mala hierba</label>
            <select id="phyto-agent" wire:model.live="agent" class="block w-full rounded-lg border-gray-300 shadow-sm">
                <option value="">Todas ({{ $this->agentOptions->count() }})</option>
                @foreach ($this->agentOptions as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="phyto-query" class="block text-sm font-medium text-gray-700 mb-1">Producto o materia activa</label>
            <input id="phyto-query" type="search" wire:model.live.debounce.400ms="query" placeholder="Ej.: azufre, cobre, nº de registro…" class="block w-full rounded-lg border-gray-300 shadow-sm">
        </div>
    </div>

    {{-- Resultados --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm" aria-live="polite">
        <div class="px-6 py-4 border-b flex flex-wrap justify-between gap-2 text-sm">
            <span class="font-semibold text-gray-900">{{ number_format($uses->total(), 0, ',', '.') }} usos autorizados</span>
            <span wire:loading class="text-gray-500">Buscando…</span>
        </div>

        @if ($uses->isEmpty())
            <p class="p-6 text-gray-500">
                @if (! $this->sourceDate)
                    El catálogo todavía no se ha cargado. Vuelve a intentarlo en unos minutos.
                @else
                    No hay usos autorizados con esos filtros.
                @endif
            </p>
        @else
            <ul class="divide-y">
                @foreach ($uses as $use)
                    <li wire:key="use-{{ $use->id }}" x-data="{ open: false }" class="px-6 py-4">
                        <div class="grid md:grid-cols-12 gap-3 items-start">
                            <div class="md:col-span-4">
                                <p class="font-semibold text-gray-900">{{ $use->product->name }}</p>
                                <p class="text-xs text-gray-500">Nº registro {{ $use->product->registration_number }} · {{ $use->product->activeSubstances() }}</p>
                            </div>
                            <div class="md:col-span-3 text-sm text-gray-800">
                                {{ $use->agent }}
                                @if ($use->crop_code !== $crop)
                                    <p class="text-xs text-amber-700">Autorizado para: {{ $use->crop }}</p>
                                @endif
                            </div>
                            <div class="md:col-span-2 text-sm">
                                <span class="text-gray-500 md:hidden">Dosis: </span>{{ $use->doseLabel() }}
                                @if ($use->application_method)
                                    <p class="text-xs text-gray-500">{{ $use->application_method }}</p>
                                @endif
                            </div>
                            <div class="md:col-span-2 text-sm">
                                <span class="text-gray-500">Plazo seg.:</span> <strong>{{ $use->safetyLabel() }}</strong>
                            </div>
                            <div class="md:col-span-1 md:text-right">
                                <button type="button" @click="open = !open" class="text-sm font-semibold text-[var(--color-agro-green-dark)] hover:underline" x-text="open ? 'Menos' : 'Más'">Más</button>
                            </div>
                        </div>

                        <div x-show="open" x-cloak class="mt-3 rounded-xl bg-gray-50 border border-gray-200 p-4 text-sm text-gray-700 space-y-2">
                            <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-1">
                                @if ($use->applications)<div><dt class="inline text-gray-500">Nº de aplicaciones:</dt> <dd class="inline">{{ $use->applications }}</dd></div>@endif
                                @if ($use->application_interval)<div><dt class="inline text-gray-500">Intervalo:</dt> <dd class="inline">{{ $use->application_interval }}</dd></div>@endif
                                @if ($use->broth_volume)<div><dt class="inline text-gray-500">Volumen de caldo:</dt> <dd class="inline">{{ $use->broth_volume }}</dd></div>@endif
                                @if ($use->bbch)<div><dt class="inline text-gray-500">Estado fenológico (BBCH):</dt> <dd class="inline">{{ $use->bbch }}</dd></div>@endif
                                @if ($use->user_type)<div><dt class="inline text-gray-500">Usuario:</dt> <dd class="inline">{{ $use->user_type }}</dd></div>@endif
                                @if ($use->product->holder)<div><dt class="inline text-gray-500">Titular:</dt> <dd class="inline">{{ $use->product->holder }}</dd></div>@endif
                                @if ($use->product->expires_on)<div><dt class="inline text-gray-500">Autorización hasta:</dt> <dd class="inline">{{ $use->product->expires_on->format('d/m/Y') }}</dd></div>@endif
                            </dl>
                            @if ($use->specific_conditions)
                                <p class="whitespace-pre-line"><span class="text-gray-500">Condiciones de este uso:</span> {{ $use->specific_conditions }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="px-6 py-4 border-t">{{ $uses->links() }}</div>
        @endif
    </div>

    <p class="text-xs text-gray-500">
        Fuente: <a href="https://servicio.mapa.gob.es/regfiweb" target="_blank" rel="noopener" class="underline">Registro Oficial de Productos Fitosanitarios</a>, Ministerio de Agricultura, Pesca y Alimentación.
        @if ($this->sourceDate)
            Datos actualizados a {{ \Carbon\Carbon::parse($this->sourceDate)->format('d/m/Y') }}.
        @endif
        Se incluyen los usos autorizados para grupos que engloban al cultivo elegido (p. ej. «Leñosas»), marcados en cada fila.
        Antes de aplicar, lee siempre la etiqueta del producto: es la que manda.
    </p>
</div>
