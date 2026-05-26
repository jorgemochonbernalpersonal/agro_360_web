<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header :title="$title" :description="$description" />

    {{-- Coming soon card --}}
    <div class="bg-white border border-zinc-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-zinc-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                <flux:icon icon="{{ $icon }}" class="w-5 h-5 text-indigo-500" />
            </div>
            <div>
                <p class="text-sm font-semibold text-zinc-800">{{ $title }}</p>
                <p class="text-xs text-zinc-400">{{ __('Denominación de Origen · En desarrollo') }}</p>
            </div>
            <span class="ml-auto px-2.5 py-1 text-[10px] font-bold bg-amber-100 text-amber-700 rounded-full uppercase tracking-wide">{{ __('Próximamente') }}</span>
        </div>

        <div class="px-6 py-5">
            <p class="text-xs font-semibold text-zinc-400 uppercase tracking-widest mb-3">{{ __('Funciones de este módulo') }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($items as $item)
                    <div class="flex items-center gap-2.5 text-sm text-zinc-500">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-300 flex-shrink-0"></span>
                        {{ $item }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <flux:callout variant="info" icon="information-circle">
        <flux:callout.heading>Módulo en construcción</flux:callout.heading>
        <flux:callout.text>
            Este módulo está en fase de implementación activa. Las funcionalidades se irán activando progresivamente.
        </flux:callout.text>
    </flux:callout>

</div>
