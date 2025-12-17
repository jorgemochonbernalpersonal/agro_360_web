@props(['message' => 'No hay registros', 'description' => null, 'icon' => null])

<div class="p-16 text-center">
    @if($icon)
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-[var(--color-agro-green-bg)] mb-6 animate-scale-in">
            {!! $icon !!}
        </div>
    @else
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
    @endif
    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $message }}</h3>
    @if($description)
        <p class="text-gray-500 mb-6">{{ $description }}</p>
    @endif
    @if(isset($action))
        <div class="mt-6">
            {{ $action }}
        </div>
    @endif
</div>

