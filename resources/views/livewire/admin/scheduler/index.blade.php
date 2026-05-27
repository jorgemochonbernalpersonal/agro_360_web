<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Tareas Programadas') }}"
        :description="__('Monitor del scheduler de Laravel — comandos definidos en routes/console.php')"
    >
        <x-slot:actions>
            @if($failedCount > 0)
                <a href="{{ route('admin.failed-jobs.index') }}">
                    <flux:badge color="red">{{ $failedCount }} job(s) fallido(s)</flux:badge>
                </a>
            @else
                <flux:badge color="agro">{{ __('Cola limpia') }}</flux:badge>
            @endif
        </x-slot:actions>
    </x-agro.page-header>

    <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 flex items-center gap-2">
        <flux:icon icon="information-circle" class="size-4 flex-shrink-0" />
        <span>{{ __('El "último ejecutado" solo se registra cuando se usa el botón "Ejecutar ahora" desde esta página. Para producción, el cron ejecuta estas tareas automáticamente.') }}</span>
    </div>

    <x-agro.card :padding="false">
        <div class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
            @foreach($tasks as $task)
                <div class="px-5 py-4 flex items-start gap-4">
                    <div class="w-9 h-9 rounded-lg bg-{{ $task['color'] }}-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <flux:icon icon="{{ $task['icon'] }}" class="size-4 text-{{ $task['color'] }}-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-zinc-900 font-mono">{{ $task['command'] }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ $task['desc'] }}</p>
                        <div class="flex items-center gap-3 mt-1.5">
                            <flux:badge color="zinc" size="sm">{{ $task['schedule'] }}</flux:badge>
                            @if($task['last_run'])
                                <span class="text-xs text-zinc-400">Último: {{ $task['last_run'] }}</span>
                            @else
                                <span class="text-xs text-zinc-300">{{ __('Sin registro') }}</span>
                            @endif
                        </div>
                    </div>
                    <flux:button
                        wire:click="runNow('{{ $task['command'] }}')"
                        wire:confirm="{{ __('¿Ejecutar \':command\' ahora? El comando se ejecutará de forma sincrónica.', ['command' => $task['command']]) }}"
                        wire:loading.attr="disabled"
                        variant="ghost"
                        size="sm"
                        icon="play"
                        tooltip="{{ __('Ejecutar ahora') }}"
                    />
                </div>
            @endforeach
        </div>
    </x-agro.card>
</div>
