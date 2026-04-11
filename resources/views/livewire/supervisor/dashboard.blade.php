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

    {{-- Onboarding checklist --}}
    @livewire('supervisor.onboarding-checklist')

    {{-- Empty state: DO sin entidades vinculadas --}}
    @if($wineryCount === 0 && $viticulturistCount === 0)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>Tu denominación de origen aún no tiene entidades vinculadas</flux:callout.heading>
            <flux:callout.text>
                Para empezar a trabajar necesitas añadir al menos una bodega o un viticultor a tu denominación.
                <span class="flex flex-wrap gap-3 mt-3">
                    <a href="{{ route('supervisor.oversight.wineries.index') }}" wire:navigate
                       class="inline-flex items-center gap-1.5 text-sm font-medium text-amber-800 underline underline-offset-2 hover:text-amber-900">
                        Añadir bodegas →
                    </a>
                    <a href="{{ route('supervisor.growers.index') }}" wire:navigate
                       class="inline-flex items-center gap-1.5 text-sm font-medium text-amber-800 underline underline-offset-2 hover:text-amber-900">
                        Añadir viticultores →
                    </a>
                </span>
            </flux:callout.text>
        </flux:callout>
    @endif

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
        <a href="{{ route('supervisor.qualification.index') }}" wire:navigate
            class="block bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm hover:border-amber-300 transition-colors">
            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">Calificaciones pendientes</p>
            <p class="text-2xl font-bold text-amber-600 leading-none">{{ $pendingQualifications }}</p>
            <p class="text-xs text-zinc-400 mt-0.5">En espera de revisión</p>
        </a>
        <a href="{{ route('supervisor.labels.index') }}" wire:navigate
            class="block bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm hover:border-rose-300 transition-colors">
            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">Contraetiquetas emitidas</p>
            <p class="text-2xl font-bold text-rose-600 leading-none">{{ number_format($issuedLabelsThisYear) }}</p>
            <p class="text-xs text-zinc-400 mt-0.5">Este año ({{ now()->year }})</p>
        </a>
        <a href="{{ route('supervisor.requests.index') }}" wire:navigate
            class="block bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm hover:border-blue-300 transition-colors">
            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">Solicitudes activas</p>
            <p class="text-2xl font-bold text-blue-600 leading-none">{{ $pendingRequests }}</p>
            <p class="text-xs text-zinc-400 mt-0.5">Pendientes + en revisión</p>
        </a>
        <a href="{{ route('supervisor.requests.index') }}" wire:navigate
            class="block bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm {{ $overdueRequests > 0 ? 'border-red-300 bg-red-50/30' : '' }} hover:border-red-300 transition-colors">
            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">Vencidas</p>
            <p class="text-2xl font-bold {{ $overdueRequests > 0 ? 'text-red-600' : 'text-zinc-400' }} leading-none">{{ $overdueRequests }}</p>
            <p class="text-xs text-zinc-400 mt-0.5">Con fecha límite superada</p>
        </a>
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
                <a href="{{ route('supervisor.requests.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                    <flux:icon icon="inbox" class="w-4 h-4 text-zinc-400 group-hover:text-blue-500 flex-shrink-0" />
                    Solicitudes y actas
                    @if($pendingRequests > 0)
                        <span class="ml-auto px-1.5 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-700 rounded-full">{{ $pendingRequests }}</span>
                    @endif
                </a>
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
                <a href="{{ route('supervisor.notebook.index') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                    <flux:icon icon="book-open" class="w-4 h-4 text-zinc-400 group-hover:text-indigo-500 flex-shrink-0" />
                    Acceso cuaderno
                    @if($pendingNotebookRequests > 0)
                        <span class="ml-auto px-1.5 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-700 rounded-full">{{ $pendingNotebookRequests }}</span>
                    @endif
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
    @if($pendingNotebookRequests > 0)
        <flux:callout variant="warning" icon="book-open">
            <flux:callout.heading>Solicitudes de cuaderno pendientes</flux:callout.heading>
            <flux:callout.text>
                Tienes {{ $pendingNotebookRequests }} solicitud{{ $pendingNotebookRequests > 1 ? 'es' : '' }} de acceso al cuaderno pendiente{{ $pendingNotebookRequests > 1 ? 's' : '' }} de respuesta por parte de los viticultores.
                <a href="{{ route('supervisor.notebook.index') }}" wire:navigate class="underline font-medium">Ver solicitudes →</a>
            </flux:callout.text>
        </flux:callout>
    @endif

</div>
