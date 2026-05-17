@props([
    'feature'     => 'esta funcionalidad',
    'description' => null,
    'price'       => null,
    'icon'        => 'lock-closed',
    'compact'     => false,
])

@if($compact)
{{-- Versión compacta: banner horizontal --}}
<div {{ $attributes->merge(['class' => 'flex items-center gap-3 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-sm']) }}>
    <svg class="w-4 h-4 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
    </svg>
    <span class="flex-1 text-amber-800">
        <strong>{{ ucfirst($feature) }}</strong> requiere el Plan Completo.
        @if($price) <span class="text-amber-600">Desde {{ $price }}€/mes.</span> @endif
    </span>
    <a href="{{ route('subscription.manage') }}" wire:navigate
       class="flex-shrink-0 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-lg transition-colors">
        Activar
    </a>
</div>
@else
{{-- Versión card centrada --}}
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-4 py-12 px-6 text-center']) }}>
    <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center">
        <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
    </div>

    <div class="max-w-xs">
        <h3 class="text-base font-bold text-zinc-800">Plan Completo requerido</h3>
        <p class="mt-1 text-sm text-zinc-500">
            {{ $description ?? ucfirst($feature) . ' está disponible en el Plan Completo.' }}
        </p>
    </div>

    @if($price)
        <span class="px-3 py-1 text-xs font-bold rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-200">
            Desde {{ $price }}€/mes
        </span>
    @endif

    <a href="{{ route('subscription.manage') }}" wire:navigate
       class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl transition-colors shadow-sm">
        Activar Plan Completo
    </a>
</div>
@endif
