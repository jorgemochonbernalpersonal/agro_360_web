@props(['placeholder' => 'Buscar...'])

<div class="flex-1 relative">
    <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
        <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
    </div>
    <input
        {{ $attributes->merge(['type' => 'text', 'class' => 'w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition']) }}
        placeholder="{{ $placeholder }}"
    />
</div>
