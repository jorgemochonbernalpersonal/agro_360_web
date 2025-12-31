@props([
    'items',
    'emptyMessage' => 'No hay elementos',
    'emptyDescription' => 'Comienza agregando el primer elemento',
    'emptyIcon' => null,
])

@if($items->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
        {{ $slot }}
    </div>

    @if(isset($pagination))
        <div class="mt-6">
            {{ $pagination }}
        </div>
    @endif
@else
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $emptyIcon ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>' !!}
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">{{ $emptyMessage }}</h3>
        <p class="mt-2 text-gray-600">{{ $emptyDescription }}</p>
        @if(isset($emptyAction))
            <div class="mt-6">
                {{ $emptyAction }}
            </div>
        @endif
    </div>
@endif
