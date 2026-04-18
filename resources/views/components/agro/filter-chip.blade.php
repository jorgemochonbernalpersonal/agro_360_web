@props(['icon' => null, 'label', 'wireRemove'])

<span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
    @if($icon)
        <flux:icon :$icon class="size-3" />
    @endif
    {{ $label }}
    <button
        wire:click="{{ $wireRemove }}"
        class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors"
    >
        <flux:icon icon="x-mark" class="size-3" />
    </button>
</span>
