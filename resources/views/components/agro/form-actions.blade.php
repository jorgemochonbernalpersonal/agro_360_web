@props(['cancelUrl' => null, 'submitLabel' => null])

@php $submitLabel ??= __('Guardar'); @endphp

<div class="flex items-center justify-end gap-3 pt-6 border-t border-zinc-200">
    @if($cancelUrl)
        <flux:button href="{{ $cancelUrl }}" variant="outline" data-cy="cancel-button">
            {{ __('Cancelar') }}
        </flux:button>
    @endif
    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:loading.class="opacity-60 cursor-not-allowed" data-cy="submit-button">
        <span wire:loading.remove>{{ $submitLabel }}</span>
        <span wire:loading class="flex items-center gap-2">
            <flux:icon icon="arrow-path" class="w-4 h-4 animate-spin" />
            {{ __('Guardando...') }}
        </span>
    </flux:button>
</div>
