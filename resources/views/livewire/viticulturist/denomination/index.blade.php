<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Mi Denominación de Origen"
        description="Información sobre tu denominación de origen y tus asignaciones."
        icon="building-office-2"
    />

    @if(! $supervisor)
        <x-agro.empty-state
            icon="building-office-2"
            title="Sin denominación asignada"
            description="No estás adscrito a ninguna denominación de origen. Si crees que es un error, contacta con tu DO."
        />
    @else

        {{-- Card supervisor --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="building-office-2" class="size-4 text-indigo-500" />
                    <span>Denominación de Origen</span>
                </div>
            </x-slot:header>

            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <flux:icon icon="building-office-2" class="size-6 text-indigo-600" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-base font-semibold text-zinc-900">{{ $supervisor->name }}</p>
                    <p class="text-sm text-zinc-500">{{ $supervisor->email }}</p>
                    @if($supervisorJoined)
                        <p class="text-xs text-zinc-400 mt-1">Adscrito desde {{ $supervisorJoined->format('d/m/Y') }}</p>
                    @endif
                </div>
                <div class="flex-shrink-0">
                    @if($notebookGranted)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-green-50 text-green-700 border border-green-200 rounded-full">
                            <flux:icon icon="book-open" class="size-3.5" />
                            Acceso cuaderno concedido
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-zinc-50 text-zinc-500 border border-zinc-200 rounded-full">
                            <flux:icon icon="lock-closed" class="size-3.5" />
                            Sin acceso al cuaderno
                        </span>
                    @endif
                </div>
            </div>
        </x-agro.card>

        {{-- Bodegas asignadas por la DO --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="home-modern" class="size-4 text-blue-500" />
                    <span>Bodegas asignadas por la DO</span>
                    @if($supervisorWineries->count() > 0)
                        <span class="px-1.5 py-0.5 text-[10px] font-bold bg-zinc-100 text-zinc-500 rounded-full">{{ $supervisorWineries->count() }}</span>
                    @endif
                </div>
            </x-slot:header>

            @forelse($supervisorWineries as $winery)
                <div class="flex items-center gap-3 py-3 border-b border-zinc-100 last:border-0">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <flux:icon icon="home-modern" class="size-4 text-blue-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-zinc-800">{{ $winery->name }}</p>
                        <p class="text-xs text-zinc-400">{{ $winery->email }}</p>
                    </div>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-violet-50 text-violet-600 border border-violet-200">
                        Asignada por DO
                    </span>
                </div>
            @empty
                <div class="py-6 text-center">
                    <flux:icon icon="home-modern" class="size-8 mx-auto text-zinc-300 mb-2" />
                    <p class="text-sm text-zinc-400">Aún no tienes bodegas asignadas por tu denominación.</p>
                </div>
            @endforelse
        </x-agro.card>

        {{-- Info --}}
        <flux:callout variant="info" icon="information-circle">
            <flux:callout.text>
                Las parcelas vinculadas a bodegas asignadas por la DO son de solo lectura. Para cualquier cambio en tu adscripción, contacta directamente con tu denominación de origen.
            </flux:callout.text>
        </flux:callout>

    @endif

</div>
