@props(['cancelUrl' => null, 'submitLabel' => 'Guardar'])

<div class="flex items-center justify-end gap-3 pt-6 border-t border-zinc-200">
    @if($cancelUrl)
        <flux:button href="{{ $cancelUrl }}" variant="outline" data-cy="cancel-button">
            Cancelar
        </flux:button>
    @endif
    <flux:button type="submit" variant="primary" data-cy="submit-button">
        {{ $submitLabel }}
    </flux:button>
</div>
