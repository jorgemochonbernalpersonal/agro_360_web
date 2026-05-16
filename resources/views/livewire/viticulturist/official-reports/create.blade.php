<div class="space-y-6 animate-fade-in">
    {{-- Header --}}
    <x-agro.page-header
        title="Generar Nuevo Informe"
        description="Crea informes firmados electrónicamente para administración y certificaciones"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.official-reports.index') }}" wire:navigate variant="outline" icon="arrow-left">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Formulario de Generación --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-agro-50">
                    <flux:icon icon="document-text" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Configurar Informe</span>
            </div>
        </x-slot:header>

            {{-- Selector de Tipo de Informe --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-zinc-700 mb-3">Tipo de Informe</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Tratamientos Fitosanitarios --}}
                    <div
                        wire:click="$set('reportType', 'phytosanitary_treatments')"
                        class="cursor-pointer border-2 rounded-xl p-4 transition-all duration-200 hover:shadow-lg
                               {{ $reportType === 'phytosanitary_treatments' ? 'border-green-500 bg-green-50' : 'border-zinc-300 hover:border-green-300' }}"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                    <span class="text-2xl">🧪</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-bold text-zinc-900">Tratamientos Fitosanitarios</h3>
                                <p class="text-sm text-zinc-600">Informe obligatorio para inspecciones</p>
                            </div>
                        </div>
                    </div>

                    {{-- Cuaderno Digital Completo --}}
                    <div
                        wire:click="$set('reportType', 'full_digital_notebook')"
                        class="cursor-pointer border-2 rounded-xl p-4 transition-all duration-200 hover:shadow-lg
                               {{ $reportType === 'full_digital_notebook' ? 'border-green-500 bg-green-50' : 'border-zinc-300 hover:border-green-300' }}"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-2xl">📔</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-bold text-zinc-900">Cuaderno Digital Completo</h3>
                                <p class="text-sm text-zinc-600">Todas las actividades de una campaña</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulario según tipo --}}
            @if($reportType === 'phytosanitary_treatments')
                {{-- Plantillas de Periodos Rápidos --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-zinc-700 mb-2">Periodos Rápidos</label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            wire:click="setQuickPeriod('last_week')"
                            class="px-3 py-1.5 text-sm font-medium text-zinc-700 bg-white border border-zinc-300 rounded-lg hover:bg-zinc-50 hover:border-green-500 transition-colors"
                        >
                            Última semana
                        </button>
                        <button
                            type="button"
                            wire:click="setQuickPeriod('this_month')"
                            class="px-3 py-1.5 text-sm font-medium text-zinc-700 bg-white border border-zinc-300 rounded-lg hover:bg-zinc-50 hover:border-green-500 transition-colors"
                        >
                            Este mes
                        </button>
                        <button
                            type="button"
                            wire:click="setQuickPeriod('last_month')"
                            class="px-3 py-1.5 text-sm font-medium text-zinc-700 bg-white border border-zinc-300 rounded-lg hover:bg-zinc-50 hover:border-green-500 transition-colors"
                        >
                            Mes pasado
                        </button>
                        <button
                            type="button"
                            wire:click="setQuickPeriod('last_quarter')"
                            class="px-3 py-1.5 text-sm font-medium text-zinc-700 bg-white border border-zinc-300 rounded-lg hover:bg-zinc-50 hover:border-green-500 transition-colors"
                        >
                            Últimos 3 meses
                        </button>
                        <button
                            type="button"
                            wire:click="setQuickPeriod('this_year')"
                            class="px-3 py-1.5 text-sm font-medium text-zinc-700 bg-white border border-zinc-300 rounded-lg hover:bg-zinc-50 hover:border-green-500 transition-colors"
                        >
                            Este año
                        </button>
                    </div>
                </div>

                {{-- Rango de Fechas --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-2">Fecha Inicio</label>
                        <input
                            type="date"
                            wire:model.live="startDate"
                            class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                        @error('startDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-700 mb-2">Fecha Fin</label>
                        <input
                            type="date"
                            wire:model.live="endDate"
                            class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                        @error('endDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Contador de Registros --}}
                @if($startDate && $endDate)
                    <flux:callout :variant="$recordCount > 0 ? 'success' : 'warning'" class="mb-6">
                        <flux:callout.text>
                            @if($recordCount > 0)
                                <strong>{{ $recordCount }} tratamiento{{ $recordCount != 1 ? 's' : '' }}</strong> encontrado{{ $recordCount != 1 ? 's' : '' }} en este periodo
                            @else
                                No hay tratamientos en este periodo
                            @endif
                        </flux:callout.text>
                    </flux:callout>
                @endif
            @else
                {{-- Selector de Campaña --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-zinc-700 mb-2">Campaña</label>
                    <select
                        wire:model.live="campaignId"
                        class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                        <option value="">Selecciona una campaña</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ $campaign->name }} ({{ $campaign->year }})</option>
                        @endforeach
                    </select>
                    @error('campaignId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Contador de Actividades --}}
                @if($campaignId)
                    <flux:callout :variant="$activitiesCount > 0 ? 'success' : 'warning'" class="mb-6">
                        <flux:callout.text>
                            @if($activitiesCount > 0)
                                <strong>{{ $activitiesCount }} actividad{{ $activitiesCount != 1 ? 'es' : '' }}</strong> registrada{{ $activitiesCount != 1 ? 's' : '' }} en esta campaña
                            @else
                                No hay actividades en esta campaña
                            @endif
                        </flux:callout.text>
                    </flux:callout>
                @endif
            @endif

            {{-- Aviso sobre firma digital --}}
            <flux:callout variant="info" class="mb-6">
                <flux:callout.text>
                    <strong>Firma Digital:</strong> Se te pedirá tu contraseña de firma digital al confirmar la generación del informe.
                    Si no la tienes configurada, créala en <a href="{{ roleRoute('viticulturist.settings', ['tab' => 'signature']) }}" class="underline font-semibold">Configuración - Firma Digital</a>.
                </flux:callout.text>
            </flux:callout>

            {{-- Errores de generación --}}
            @error('generation')
                <flux:callout variant="danger" class="mb-4">
                    <flux:callout.text>{{ $message }}</flux:callout.text>
                </flux:callout>
            @enderror

            {{-- Botón Generar --}}
            <flux:button
                wire:click="calculateSummary"
                wire:loading.attr="disabled"
                wire:target="calculateSummary"
                variant="primary"
                icon="document-text"
            >
                <span wire:loading.remove wire:target="calculateSummary">Generar y Firmar Informe</span>
                <span wire:loading wire:target="calculateSummary">Calculando...</span>
            </flux:button>
    </x-agro.card>

    {{-- Include modals --}}
    @include('livewire.viticulturist.official-reports.partials._summary-modal')
    @include('livewire.viticulturist.official-reports.partials._success-modal')

    {{-- Indicador de carga mientras se genera el informe --}}
    <div wire:loading wire:target="confirmAndGenerateReport,generateReport" class="fixed z-50 inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-zinc-500 bg-opacity-75 transition-opacity"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 z-10">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                        <flux:icon icon="arrow-path" class="animate-spin size-10 text-green-600" />
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900 mb-2">Generando Informe</h3>
                    <p class="text-zinc-600 mb-4">
                        Por favor, espera mientras se genera y firma tu informe oficial...
                    </p>
                    <p class="text-sm text-zinc-500">
                        Este proceso puede tardar varios segundos.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
