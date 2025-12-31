@props([
    'activeCount' => 0,
    'inactiveCount' => 0,
    'currentTab' => 'active',
    'onSwitch' => 'switchTab',
    'showStats' => true,
])

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-1 inline-flex gap-1 mb-6">
    <button wire:click="{{ $onSwitch }}('active')" 
            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $currentTab === 'active' ? 'bg-[var(--color-agro-green)] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
        Activos 
        <span class="ml-1 {{ $currentTab === 'active' ? 'text-white/80' : 'text-gray-500' }}">({{ $activeCount }})</span>
    </button>
    <button wire:click="{{ $onSwitch }}('inactive')" 
            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $currentTab === 'inactive' ? 'bg-[var(--color-agro-green)] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
        Inactivos 
        <span class="ml-1 {{ $currentTab === 'inactive' ? 'text-white/80' : 'text-gray-500' }}">({{ $inactiveCount }})</span>
    </button>
    @if($showStats)
        <button wire:click="{{ $onSwitch }}('statistics')" 
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $currentTab === 'statistics' ? 'bg-[var(--color-agro-green)] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Estadísticas
        </button>
    @endif
</div>
