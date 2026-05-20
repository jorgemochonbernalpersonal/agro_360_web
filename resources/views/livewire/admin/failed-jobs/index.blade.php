<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Jobs Fallidos"
        description="Monitor y gestión de trabajos fallidos en la cola"
    >
        <x-slot:actions>
            @if($totalCount > 0)
                <flux:button
                    wire:click="retryAll"
                    wire:confirm="¿Reencolar los {{ $totalCount }} job(s) fallidos? Volverán a ejecutarse."
                    variant="ghost"
                    icon="arrow-path"
                >
                    Reintentar todos ({{ $totalCount }})
                </flux:button>
                <flux:button
                    wire:click="deleteAll"
                    wire:confirm="¿Eliminar permanentemente los {{ $totalCount }} job(s) fallidos? Esta acción no se puede deshacer."
                    variant="danger"
                    icon="trash"
                >
                    Vaciar cola
                </flux:button>
            @endif
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card
            label="Jobs fallidos"
            :value="$totalCount"
            :icon="$totalCount > 0 ? 'exclamation-circle' : 'check-circle'"
            :color="$totalCount > 0 ? 'red' : 'agro'"
        />
        <x-agro.stat-card
            label="Mostrando"
            :value="$jobs->total()"
            icon="list-bullet"
            color="blue"
        />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live="search"
            placeholder="Buscar por cola, clase o excepción..."
        />
    </x-agro.filter-bar>

    {{-- Tabla --}}
    <x-agro.card :padding="false">
        @if($jobs->isEmpty())
            <x-agro.empty-state
                icon="check-circle"
                message="Sin jobs fallidos"
                description="La cola de trabajos está limpia — no hay errores pendientes"
            />
        @else
            <div class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                @foreach($jobs as $job)
                    @php
                        $payload     = json_decode($job->payload, true);
                        $jobClass    = $payload['displayName'] ?? ($payload['job'] ?? 'Unknown');
                        $jobClass    = class_basename($jobClass);
                        $failedAt    = \Carbon\Carbon::parse($job->failed_at);
                        $exception   = Str::of($job->exception)->before("\n")->limit(200);
                    @endphp
                    <div class="px-5 py-4">
                        <div class="flex items-start gap-4">
                            {{-- Icon --}}
                            <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <flux:icon icon="x-circle" class="size-4 text-red-500" />
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-semibold text-zinc-900 dark:text-white font-mono">{{ $jobClass }}</span>
                                    <flux:badge color="zinc" size="sm">{{ $job->queue }}</flux:badge>
                                    <span class="text-xs text-zinc-400">{{ $failedAt->format('d/m/Y H:i:s') }} · {{ $failedAt->diffForHumans() }}</span>
                                </div>

                                <p class="mt-1 text-xs text-red-600 font-mono line-clamp-2 break-all">
                                    {{ $exception }}
                                </p>

                                {{-- Connection --}}
                                <p class="mt-1 text-xs text-zinc-400">Conexión: {{ $job->connection }}</p>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <flux:button
                                    wire:click="retryJob({{ $job->id }})"
                                    wire:confirm="¿Reencolar este job?"
                                    variant="ghost"
                                    size="sm"
                                    icon="arrow-path"
                                    tooltip="Reintentar"
                                />
                                <flux:button
                                    wire:click="deleteJob({{ $job->id }})"
                                    wire:confirm="¿Eliminar este job definitivamente?"
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    class="text-red-400 hover:text-red-600"
                                    tooltip="Eliminar"
                                />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($jobs->hasPages())
            <div class="px-5 py-4 border-t border-zinc-100 dark:border-zinc-700/50">
                {{ $jobs->links() }}
            </div>
        @endif
    </x-agro.card>
</div>
