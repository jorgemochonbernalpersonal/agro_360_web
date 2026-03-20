<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900">
                {{ now()->hour < 14 ? 'Buenos días' : 'Buenas tardes' }}, {{ Auth::user()->name }}
            </h1>
            <p class="text-sm text-zinc-400 mt-0.5">
                Panel de la <span class="font-semibold text-indigo-600">Denominación de Origen</span>
            </p>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">Bodegas adscritas</p>
            <p class="text-2xl font-bold text-indigo-600 leading-none">{{ $wineryCount }}</p>
            <p class="text-xs text-zinc-400 mt-0.5">Supervisadas por la DO</p>
        </div>
        <div class="bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">Viticultores DO</p>
            <p class="text-2xl font-bold text-emerald-600 leading-none">{{ $viticulturistCount }}</p>
            <p class="text-xs text-zinc-400 mt-0.5">Adscritos a la denominación</p>
        </div>
        <div class="bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">Calificaciones pendientes</p>
            <p class="text-2xl font-bold text-amber-600 leading-none">—</p>
            <p class="text-xs text-zinc-400 mt-0.5">Pendiente de datos</p>
        </div>
        <div class="bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">Contraetiquetas emitidas</p>
            <p class="text-2xl font-bold text-rose-600 leading-none">—</p>
            <p class="text-xs text-zinc-400 mt-0.5">Campaña actual</p>
        </div>
    </div>

    {{-- Bloques de acceso rápido --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Gestión de la denominación --}}
        <div class="bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-zinc-700 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <flux:icon icon="users" class="w-3.5 h-3.5 text-indigo-600" />
                </span>
                Gestión de la denominación
            </h2>
            <div class="space-y-1.5">
                <a href="{{ route('supervisor.census.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                    <flux:icon icon="users" class="w-4 h-4 text-zinc-400 group-hover:text-indigo-500 flex-shrink-0" />
                    Censo
                </a>
                <a href="{{ route('supervisor.growers.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                    <flux:icon icon="user-group" class="w-4 h-4 text-zinc-400 group-hover:text-emerald-500 flex-shrink-0" />
                    Viticultores DO
                </a>
                <a href="{{ route('supervisor.campaigns.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                    <flux:icon icon="flag" class="w-4 h-4 text-zinc-400 group-hover:text-amber-500 flex-shrink-0" />
                    Campañas
                </a>
            </div>
        </div>

        {{-- Supervisión --}}
        <div class="bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-zinc-700 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <flux:icon icon="eye" class="w-3.5 h-3.5 text-blue-600" />
                </span>
                Supervisión
            </h2>
            <div class="space-y-1.5">
                <a href="{{ route('supervisor.oversight.wineries.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                    <flux:icon icon="building-office-2" class="w-4 h-4 text-zinc-400 group-hover:text-blue-500 flex-shrink-0" />
                    Bodegas
                </a>
                <a href="{{ route('supervisor.oversight.growers.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                    <flux:icon icon="eye" class="w-4 h-4 text-zinc-400 group-hover:text-cyan-500 flex-shrink-0" />
                    Viticultores
                </a>
            </div>
        </div>

        {{-- Operativa regulatoria --}}
        <div class="bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-zinc-700 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-rose-100 flex items-center justify-center flex-shrink-0">
                    <flux:icon icon="shield-check" class="w-3.5 h-3.5 text-rose-600" />
                </span>
                Operativa regulatoria
            </h2>
            <div class="space-y-1.5">
                <a href="{{ route('supervisor.qualification.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                    <flux:icon icon="star" class="w-4 h-4 text-zinc-400 group-hover:text-yellow-500 flex-shrink-0" />
                    Calificación
                </a>
                <a href="{{ route('supervisor.labels.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                    <flux:icon icon="tag" class="w-4 h-4 text-zinc-400 group-hover:text-rose-500 flex-shrink-0" />
                    Contraetiquetas
                </a>
                <a href="{{ route('supervisor.inspection.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                    <flux:icon icon="shield-check" class="w-4 h-4 text-zinc-400 group-hover:text-red-500 flex-shrink-0" />
                    Control e Inspección
                </a>
                <a href="{{ route('supervisor.regulation.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                    <flux:icon icon="document-text" class="w-4 h-4 text-zinc-400 group-hover:text-violet-500 flex-shrink-0" />
                    Normativa DO
                </a>
                <a href="{{ route('supervisor.territory.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                    <flux:icon icon="map" class="w-4 h-4 text-zinc-400 group-hover:text-teal-500 flex-shrink-0" />
                    Territorio
                </a>
            </div>
        </div>

        {{-- Administración --}}
        <div class="bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-zinc-700 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                    <flux:icon icon="chart-bar" class="w-3.5 h-3.5 text-purple-600" />
                </span>
                Administración DO
            </h2>
            <div class="space-y-1.5">
                <a href="{{ route('supervisor.statistics.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                    <flux:icon icon="chart-bar" class="w-4 h-4 text-zinc-400 group-hover:text-purple-500 flex-shrink-0" />
                    Estadísticas
                </a>
                <a href="{{ route('supervisor.finance.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                    <flux:icon icon="banknotes" class="w-4 h-4 text-zinc-400 group-hover:text-green-500 flex-shrink-0" />
                    Negocio DO
                </a>
                <a href="{{ route('supervisor.settings.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                    <flux:icon icon="cog-6-tooth" class="w-4 h-4 text-zinc-400 group-hover:text-slate-500 flex-shrink-0" />
                    Sistema
                </a>
            </div>
        </div>

    </div>

    {{-- Info callout --}}
    <flux:callout variant="info" icon="information-circle">
        <flux:callout.heading>Módulo en construcción</flux:callout.heading>
        <flux:callout.text>
            El rol Denominación de Origen está en fase de implementación. Los módulos están disponibles para navegación y se irán activando progresivamente. 13 capítulos · 88 funciones · 4 bloques funcionales.
        </flux:callout.text>
    </flux:callout>

</div>
