<div class="p-8 bg-white dark:bg-zinc-800 rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold mb-4 text-zinc-800 dark:text-white">{{ __('Contador Livewire') }}</h2>
    <div class="flex items-center gap-4">
        <button 
            wire:click="decrement" 
            class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition"
        >
            -
        </button>
        <span class="text-3xl font-bold text-zinc-800 dark:text-white">{{ $count }}</span>
        <button 
            wire:click="increment" 
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition"
        >
            +
        </button>
    </div>
    <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Este es un componente Livewire funcionando con PHP puro') }}</p>
</div>
