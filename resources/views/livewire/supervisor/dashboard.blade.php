<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900">
                {{ now()->hour < 14 ? __('Buenos días') : __('Buenas tardes') }}, {{ Auth::user()->name }}
            </h1>
            <p class="text-sm text-zinc-400 mt-0.5">
                {{ __('Panel de la Denominación de Origen') }}
                &mdash; {{ now()->format('d/m/Y') }}
            </p>
        </div>
    </div>

    {{-- Onboarding checklist --}}
    @livewire('supervisor.onboarding-checklist')

    {{-- Empty state --}}
    @if($wineryCount === 0 && $viticulturistCount === 0)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('Tu denominación de origen aún no tiene entidades vinculadas') }}</flux:callout.heading>
            <flux:callout.text>
                {{ __('Para empezar a trabajar necesitas añadir al menos una bodega o un viticultor.') }}
                <span class="flex flex-wrap gap-3 mt-3">
                    <a href="{{ route('supervisor.oversight.wineries.index') }}" wire:navigate class="text-sm font-medium text-amber-800 underline underline-offset-2 hover:text-amber-900">{{ __('Añadir bodegas') }} →</a>
                    <a href="{{ route('supervisor.growers.index') }}" wire:navigate class="text-sm font-medium text-amber-800 underline underline-offset-2 hover:text-amber-900">{{ __('Añadir viticultores') }} →</a>
                </span>
            </flux:callout.text>
        </flux:callout>
    @endif

    {{-- KPI stat cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <x-agro.kpi-tile color="indigo" icon="building-office-2" :value="$wineryCount" :label="__('Bodegas')" href="{{ route('supervisor.oversight.wineries.index') }}" />
        <x-agro.kpi-tile color="emerald" icon="users" :value="$viticulturistCount" :label="__('Viticultores')" href="{{ route('supervisor.growers.index') }}" />
        <x-agro.kpi-tile color="amber" icon="star" :value="$pendingQualifications" :label="__('Calificaciones')" href="{{ route('supervisor.qualification.index') }}" />
        <x-agro.kpi-tile color="rose" icon="tag" :value="number_format($issuedLabelsThisYear)" :label="__('Emitidas') . ' ' . now()->year" href="{{ route('supervisor.labels.index', ['tab' => 'issued']) }}">
            @if($pendingLabels > 0)
                <p class="text-[10px] text-rose-500 mt-0.5 font-medium">{{ $pendingLabels }} {{ $pendingLabels !== 1 ? __('pendientes') : __('pendiente') }}</p>
            @endif
        </x-agro.kpi-tile>
        <x-agro.kpi-tile color="blue" icon="inbox" :value="$pendingRequests" :label="__('Solicitudes')" href="{{ route('supervisor.requests.index') }}" />
        <x-agro.kpi-tile color="red" icon="clock" :value="$overdueRequests" :label="__('Vencidas')" href="{{ route('supervisor.requests.index') }}" :active="$overdueRequests > 0" />
    </div>

    {{-- Alerts row --}}
    @if($overdueRequests > 0 || $pendingNotebookCount > 0 || $expiringCertifications->count() > 0 || $pendingLabels > 0 || $nonCompliantInspections > 0)
        <div class="flex flex-wrap gap-3">
            @if($overdueRequests > 0)
                <x-agro.alert-chip color="red" icon="exclamation-circle" href="{{ route('supervisor.requests.index') }}" wire:navigate>
                    {{ $overdueRequests }} {{ $overdueRequests !== 1 ? __('solicitudes vencidas') : __('solicitud vencida') }}
                </x-agro.alert-chip>
            @endif
            @if($nonCompliantInspections > 0)
                <x-agro.alert-chip color="red" icon="shield-exclamation" href="{{ route('supervisor.inspection.index') }}" wire:navigate>
                    {{ $nonCompliantInspections }} {{ $nonCompliantInspections !== 1 ? __('inspecciones no conformes') : __('inspección no conforme') }}
                </x-agro.alert-chip>
            @endif
            @if($pendingLabels > 0)
                <x-agro.alert-chip color="rose" icon="tag" href="{{ route('supervisor.labels.index') }}" wire:navigate>
                    {{ $pendingLabels }} {{ $pendingLabels !== 1 ? __('contraetiquetas pendientes') : __('contraetiqueta pendiente') }}
                </x-agro.alert-chip>
            @endif
            @if($pendingNotebookCount > 0)
                <x-agro.alert-chip color="amber" icon="book-open" href="{{ route('supervisor.notebook.index') }}" wire:navigate>
                    {{ $pendingNotebookCount }} {{ $pendingNotebookCount !== 1 ? __('solicitudes de cuaderno pendientes') : __('solicitud de cuaderno pendiente') }}
                </x-agro.alert-chip>
            @endif
            @if($expiringCertifications->count() > 0)
                <x-agro.alert-chip color="orange" icon="check-badge" href="{{ route('supervisor.oversight.certifications.index') }}" wire:navigate>
                    {{ $expiringCertifications->count() }} {{ $expiringCertifications->count() !== 1 ? __('certificaciones por vencer') : __('certificación por vencer') }}
                </x-agro.alert-chip>
            @endif
        </div>
    @endif

    {{-- Main content grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left column: inspections + requests --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Upcoming inspections --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <flux:icon icon="shield-check" class="size-4 text-zinc-400" />
                            <span class="font-medium text-zinc-700">{{ __('Próximas inspecciones') }}</span>
                            <span class="text-xs text-zinc-400">(21 {{ __('días') }})</span>
                        </div>
                        <a href="{{ route('supervisor.inspection.index') }}" wire:navigate
                           class="text-xs text-indigo-600 hover:underline">{{ __('Ver todas') }}</a>
                    </div>
                </x-slot:header>

                @forelse($upcomingInspections as $insp)
                    <x-agro.list-row class="flex items-center gap-3 py-2.5">
                        @php
                            $daysLeft = today()->diffInDays($insp->inspection_date, false);
                            $urgency  = $daysLeft <= 3 ? 'bg-red-50 text-red-700 border-red-200'
                                      : ($daysLeft <= 7 ? 'bg-amber-50 text-amber-700 border-amber-200'
                                      : 'bg-blue-50 text-blue-700 border-blue-200');
                        @endphp
                        <div class="w-10 h-10 rounded-xl {{ $urgency }} border flex flex-col items-center justify-center shrink-0">
                            <span class="text-[10px] font-bold leading-none">{{ $insp->inspection_date->format('d') }}</span>
                            <span class="text-[9px] uppercase leading-none">{{ $insp->inspection_date->format('M') }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-800 truncate">
                                {{ $insp->reference_number ?? __('Inspección') . ' #' . $insp->id }}
                            </p>
                            <p class="text-xs text-zinc-400">
                                {{ ucfirst($insp->subject_type ?? __('Sin sujeto')) }}
                                @if($daysLeft === 0) · {{ __('Hoy') }}
                                @elseif($daysLeft === 1) · {{ __('Mañana') }}
                                @else · {{ __('En :n días', ['n' => $daysLeft]) }}
                                @endif
                            </p>
                        </div>
                        <x-agro.status-badge :status="$insp->status" />
                    </x-agro.list-row>
                @empty
                    <div class="py-6 text-center text-sm text-zinc-400">
                        <flux:icon icon="calendar" class="size-8 mx-auto mb-2 text-zinc-300" />
                        {{ __('Sin inspecciones programadas en los próximos 21 días.') }}
                    </div>
                @endforelse
            </x-agro.card>

            {{-- Recent active requests --}}
            @if($recentRequests->count() > 0)
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <flux:icon icon="inbox" class="size-4 text-zinc-400" />
                                <span class="font-medium text-zinc-700">{{ __('Solicitudes activas') }}</span>
                            </div>
                            <a href="{{ route('supervisor.requests.index') }}" wire:navigate
                               class="text-xs text-indigo-600 hover:underline">{{ __('Ver todas') }}</a>
                        </div>
                    </x-slot:header>

                    @foreach($recentRequests as $req)
                        <x-agro.list-row class="flex items-center gap-3 py-2.5">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-800 truncate">{{ $req->title }}</p>
                                <p class="text-xs text-zinc-400">
                                    @if($req->due_date)
                                        {{ __('Vence') }} {{ $req->due_date->format('d/m/Y') }}
                                        @if($req->due_date->isPast()) &nbsp;<span class="text-red-500 font-medium">· {{ __('Vencida') }}</span> @endif
                                    @else
                                        {{ __('Sin fecha límite') }}
                                    @endif
                                </p>
                            </div>
                            <x-agro.status-badge :status="$req->status" />
                        </x-agro.list-row>
                    @endforeach
                </x-agro.card>
            @endif

        </div>

        {{-- Right column: alerts + nav shortcuts --}}
        <div class="space-y-4">

            {{-- Pending notebook requests --}}
            @if($pendingNotebookRequests->count() > 0)
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="book-open" class="size-4 text-amber-500" />
                            <span class="font-medium text-zinc-700">{{ __('Cuaderno pendiente') }}</span>
                        </div>
                    </x-slot:header>

                    @foreach($pendingNotebookRequests as $req)
                        <x-agro.list-row class="flex items-center gap-3 py-2.5">
                            <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                                <flux:icon icon="user" class="size-4 text-amber-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-800 truncate">{{ $req->viticulturist?->name }}</p>
                                <p class="text-xs text-zinc-400">{{ __('Solicitado') }} {{ $req->requested_at->diffForHumans() }}</p>
                            </div>
                        </x-agro.list-row>
                    @endforeach

                    <div class="pt-2">
                        <a href="{{ route('supervisor.notebook.index') }}" wire:navigate
                           class="block text-center text-xs text-indigo-600 hover:underline py-1">
                            {{ __('Gestionar accesos al cuaderno') }} →
                        </a>
                    </div>
                </x-agro.card>
            @endif

            {{-- Expiring certifications --}}
            @if($expiringCertifications->count() > 0)
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="check-badge" class="size-4 text-orange-500" />
                            <span class="font-medium text-zinc-700">{{ __('Certificaciones por vencer') }}</span>
                        </div>
                    </x-slot:header>

                    @foreach($expiringCertifications as $cert)
                        @php
                            $daysLeft = today()->diffInDays($cert->expiry_date, false);
                            $color = $daysLeft <= 15 ? 'text-red-600' : 'text-orange-600';
                        @endphp
                        <x-agro.list-row class="flex items-center gap-3 py-2.5">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-800 truncate">{{ $cert->viticulturist?->name }}</p>
                                <p class="text-xs text-zinc-500 truncate">{{ $cert->certification_type_label }}</p>
                            </div>
                            <span class="text-xs font-semibold {{ $color }} shrink-0">
                                {{ $daysLeft === 0 ? __('Hoy') : __('En :nd', ['n' => $daysLeft]) }}
                            </span>
                        </x-agro.list-row>
                    @endforeach
                </x-agro.card>
            @endif

            {{-- Quick nav --}}
            <div class="bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm">
                <h2 class="text-xs font-semibold text-zinc-400 uppercase tracking-widest mb-3">{{ __('Acceso rápido') }}</h2>
                <div class="space-y-0.5">
                    @foreach([
                        ['route' => 'supervisor.census.index',              'icon' => 'users',                'label' => __('Censo')],
                        ['route' => 'supervisor.growers.index',             'icon' => 'user-group',           'label' => __('Viticultores DO')],
                        ['route' => 'supervisor.oversight.wineries.index',  'icon' => 'building-office-2',    'label' => __('Bodegas')],
                        ['route' => 'supervisor.requests.index',            'icon' => 'inbox',                'label' => __('Solicitudes')],
                        ['route' => 'supervisor.qualification.index',       'icon' => 'star',                 'label' => __('Calificación')],
                        ['route' => 'supervisor.labels.index',              'icon' => 'tag',                  'label' => __('Contraetiquetas')],
                        ['route' => 'supervisor.inspection.index',          'icon' => 'shield-check',         'label' => __('Inspecciones')],
                        ['route' => 'supervisor.documents.index',           'icon' => 'document-duplicate',   'label' => __('Documentos DO')],
                        ['route' => 'supervisor.regulation.index',          'icon' => 'document-text',        'label' => __('Normativa')],
                        ['route' => 'supervisor.statistics.index',          'icon' => 'chart-bar',            'label' => __('Estadísticas')],
                    ] as $link)
                        <a href="{{ route($link['route']) }}" wire:navigate
                           class="flex items-center gap-3 px-2 py-1.5 rounded-lg text-sm text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition group">
                            <flux:icon icon="{{ $link['icon'] }}" class="size-4 text-zinc-400 group-hover:text-indigo-500 flex-shrink-0" />
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</div>
