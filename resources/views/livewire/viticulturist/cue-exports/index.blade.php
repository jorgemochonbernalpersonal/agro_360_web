<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        :title="__('Exportaciones CUE / SIEX')"
        :subtitle="__('Historial de envíos del Cuaderno de Explotación Único al MAPA')"
        icon="arrow-up-tray"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.cue-exports.create') }}" variant="primary" icon="plus">
                {{ __('Nueva Exportación') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <flux:callout variant="info" icon="information-circle">
        {{ __('El CUE (Cuaderno de Explotación Único) es de envío obligatorio al MAPA desde 2022 (RD 1337/2021). Crea aquí los registros de envío y haz seguimiento de su estado. El envío electrónico directo al SIEX estará disponible próximamente.') }}
    </flux:callout>

    @if($exploitations->isEmpty())
        <x-agro.empty-state
            icon="building-office"
            :title="__('Sin explotaciones registradas')"
            :description="__('Antes de crear una exportación CUE, debes registrar al menos una explotación agraria en el módulo Explotación SIEX/REA.')"
        >
            <x-slot:action>
                <flux:button variant="primary" :href="roleRoute('viticulturist.exploitations.index')" wire:navigate>
                    {{ __('Ir a Explotaciones') }}
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @elseif($exports->isEmpty())
        <x-agro.empty-state
            icon="arrow-up-tray"
            :title="__('Sin exportaciones registradas')"
            :description="__('Crea tu primera exportación CUE para registrar el historial de envíos al MAPA.')"
        >
            <x-slot:action>
                <flux:button href="{{ roleRoute('viticulturist.cue-exports.create') }}" variant="primary" icon="plus">
                    {{ __('Nueva Exportación') }}
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @else
        {{-- Tabla de exportaciones --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="arrow-up-tray" class="w-5 h-5 text-agro-600" />
                    <span class="font-semibold text-zinc-900">{{ __('Historial de Exportaciones') }}</span>
                </div>
            </x-slot:header>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100">
                            <th class="text-left py-3 px-4 text-zinc-500 font-medium">{{ __('Explotación') }}</th>
                            <th class="text-left py-3 px-4 text-zinc-500 font-medium">{{ __('Campaña') }}</th>
                            <th class="text-left py-3 px-4 text-zinc-500 font-medium">{{ __('Período') }}</th>
                            <th class="text-left py-3 px-4 text-zinc-500 font-medium">{{ __('Fechas') }}</th>
                            <th class="text-left py-3 px-4 text-zinc-500 font-medium">{{ __('Estado') }}</th>
                            <th class="text-left py-3 px-4 text-zinc-500 font-medium">{{ __('Enviado') }}</th>
                            <th class="text-right py-3 px-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exports as $export)
                            <tr class="border-b border-zinc-50 hover:bg-zinc-50 transition-colors">
                                <td class="py-3 px-4 font-medium text-zinc-900">
                                    {{ $export->exploitation->exploitation_name ?? '-' }}
                                </td>
                                <td class="py-3 px-4">{{ $export->campaign_year }}</td>
                                <td class="py-3 px-4">
                                    {{ $export->period_type === 'annual' ? __('Anual') : __('Trimestral') }}
                                </td>
                                <td class="py-3 px-4 text-zinc-500">
                                    {{ $export->from_date->format('d/m/Y') }} — {{ $export->to_date->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4">
                                    <x-agro.status-badge
                                        :label="$export->status_label"
                                        :color="$export->status_color"
                                    />
                                </td>
                                <td class="py-3 px-4 text-zinc-500 text-xs">
                                    @if($export->sent_at)
                                        {{ $export->sent_at->format('d/m/Y H:i') }}
                                    @elseif($export->generated_at)
                                        <span class="text-blue-600">{{ __('Generado') }} {{ $export->generated_at->format('d/m/Y') }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($export->status === 'draft')
                                            <x-agro.action-button
                                                variant="edit"
                                                href="{{ roleRoute('viticulturist.cue-exports.edit', $export) }}"
                                                :title="__('Editar')"
                                            />
                                            <x-agro.action-button
                                                variant="activate"
                                                wire:click="markAsGenerated({{ $export->id }})"
                                                wire:confirm="{{ __('¿Marcar esta exportación como generada?') }}"
                                                :title="__('Marcar como generado')"
                                            />
                                            <x-agro.action-button
                                                variant="delete"
                                                wire:click="delete({{ $export->id }})"
                                                wire:confirm="{{ __('¿Eliminar esta exportación? Esta acción no se puede deshacer.') }}"
                                                :title="__('Eliminar')"
                                            />
                                        @elseif($export->status === 'generated')
                                            <x-agro.action-button
                                                variant="primary"
                                                icon="paper-airplane"
                                                wire:click="markAsSent({{ $export->id }})"
                                                wire:confirm="{{ __('¿Marcar esta exportación como enviada al MAPA?') }}"
                                                :title="__('Marcar como enviado')"
                                            />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-agro.card>

        {{-- Paginación --}}
        <x-agro-pagination :paginator="$exports" />

        {{-- Resumen por estado --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach(\App\Models\CueExport::statusOptions() as $key => $label)
                <x-agro.stat-card
                    :label="$label"
                    :value="$statusCounts[$key] ?? 0"
                    :color="\App\Models\CueExport::STATUS_COLORS[$key]"
                />
            @endforeach
        </div>
    @endif

</div>
