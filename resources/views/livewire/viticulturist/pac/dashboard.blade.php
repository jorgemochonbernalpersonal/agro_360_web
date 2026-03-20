<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="PAC — Política Agrícola Común"
        description="Resumen de superficies admisibles, declaraciones y eco-regímenes de tu explotación."
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" href="{{ route('viticulturist.pac.declarations.create') }}" wire:navigate>
                Nueva declaración
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats superficies --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card label="Parcelas activas" :value="$stats['total_plots']" icon="map" color="zinc" />
        <x-agro.stat-card label="Superficie total" :value="number_format($stats['total_area'], 2) . ' ha'" icon="globe-alt" color="agro" />
        <x-agro.stat-card label="Superficie admisible PAC" :value="number_format($stats['total_eligible'], 2) . ' ha'" icon="check-circle" color="green" />
        <x-agro.stat-card label="Sin datos PAC" :value="$stats['plots_without_pac']" icon="exclamation-triangle" color="amber" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Elegibilidad global --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="chart-bar" class="size-4 text-agro-600" />
                    <span class="font-semibold text-zinc-900 text-sm">Admisibilidad global</span>
                </div>
            </x-slot:header>
            <div class="flex flex-col items-center py-4">
                <div class="relative w-32 h-32">
                    <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e4e4e7" stroke-width="3" />
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#22c55e" stroke-width="3"
                            stroke-dasharray="{{ $stats['eligibility_pct'] }} {{ 100 - $stats['eligibility_pct'] }}"
                            stroke-linecap="round" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-bold text-zinc-900">{{ $stats['eligibility_pct'] }}%</span>
                        <span class="text-xs text-zinc-400">admisible</span>
                    </div>
                </div>
                <div class="mt-4 space-y-1 w-full text-sm">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Admisible</span>
                        <span class="font-medium text-green-600">{{ number_format($stats['total_eligible'], 2) }} ha</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">No admisible</span>
                        <span class="font-medium text-red-500">{{ number_format($stats['total_non_eligible'], 2) }} ha</span>
                    </div>
                    <div class="flex justify-between border-t border-zinc-100 pt-1 mt-1">
                        <span class="text-zinc-500">Total</span>
                        <span class="font-medium text-zinc-900">{{ number_format($stats['total_area'], 2) }} ha</span>
                    </div>
                </div>
            </div>
        </x-agro.card>

        {{-- Declaración año actual --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="document-text" class="size-4 text-blue-600" />
                    <span class="font-semibold text-zinc-900 text-sm">Declaración {{ $currentYear }}</span>
                </div>
            </x-slot:header>
            @if($currentDeclaration)
                <div class="space-y-3 py-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-500">Estado</span>
                        <x-agro.status-badge
                            :color="$currentDeclaration->statusColor()"
                            :label="$currentDeclaration->statusLabel()"
                        />
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-zinc-500">Superficie declarada</span>
                        <span class="font-medium">{{ number_format($currentDeclaration->total_declared_area, 2) }} ha</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-zinc-500">Superficie admisible</span>
                        <span class="font-medium text-green-600">{{ number_format($currentDeclaration->total_eligible_area, 2) }} ha</span>
                    </div>
                    @if($currentDeclaration->reference_number)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500">Referencia</span>
                            <span class="font-mono text-xs text-zinc-600">{{ $currentDeclaration->reference_number }}</span>
                        </div>
                    @endif
                    <div class="pt-2">
                        <flux:button size="sm" variant="ghost" icon="eye"
                            href="{{ route('viticulturist.pac.declarations.show', $currentDeclaration) }}"
                            wire:navigate class="w-full">
                            Ver declaración
                        </flux:button>
                    </div>
                </div>
            @else
                <div class="py-6 text-center">
                    <flux:icon icon="document-plus" class="size-10 text-zinc-300 mx-auto mb-2" />
                    <p class="text-sm text-zinc-500 mb-3">No hay declaración para {{ $currentYear }}</p>
                    <flux:button size="sm" variant="primary" icon="plus"
                        href="{{ route('viticulturist.pac.declarations.create') }}" wire:navigate>
                        Crear declaración
                    </flux:button>
                </div>
            @endif
        </x-agro.card>

        {{-- Historial declaraciones --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="clock" class="size-4 text-zinc-500" />
                    <span class="font-semibold text-zinc-900 text-sm">Historial</span>
                </div>
            </x-slot:header>
            @if($declarations->isEmpty())
                <p class="text-sm text-zinc-400 py-4 text-center">Sin declaraciones previas</p>
            @else
                <div class="space-y-2 py-1">
                    @foreach($declarations as $decl)
                        <a href="{{ route('viticulturist.pac.declarations.show', $decl) }}"
                           wire:navigate
                           class="flex items-center justify-between px-2 py-1.5 rounded-lg hover:bg-zinc-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-zinc-800 text-sm">{{ $decl->year }}</span>
                                <x-agro.status-badge :color="$decl->statusColor()" :label="$decl->statusLabel()" />
                            </div>
                            <span class="text-xs text-zinc-500">{{ number_format($decl->total_eligible_area, 2) }} ha</span>
                        </a>
                    @endforeach
                </div>
                <div class="mt-2 pt-2 border-t border-zinc-100">
                    <flux:button size="sm" variant="ghost" href="{{ route('viticulturist.pac.declarations.index') }}" wire:navigate class="w-full">
                        Ver todas
                    </flux:button>
                </div>
            @endif
        </x-agro.card>
    </div>

    {{-- Alerta parcelas sin datos PAC --}}
    @if($plotsWithoutPac->isNotEmpty())
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>{{ $plotsWithoutPac->count() }} parcela(s) sin superficie admisible PAC</flux:callout.heading>
            <flux:callout.text>
                {{ $plotsWithoutPac->pluck('name')->join(', ') }}.
                Edita cada parcela para añadir su superficie admisible PAC antes de crear la declaración.
                <a href="{{ route('viticulturist.pac.surfaces.index') }}" wire:navigate class="underline ml-1">Ver superficies →</a>
            </flux:callout.text>
        </flux:callout>
    @endif

</div>
