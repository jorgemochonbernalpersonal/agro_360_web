@props([
    'cancelUrl',
    'submitLabel' => 'Guardar',
    'submitVariant' => 'primary',
    'cancelVariant' => 'secondary',
])

<div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
    @if($cancelUrl)
        <x-button href="{{ $cancelUrl }}" :variant="$cancelVariant" data-cy="cancel-button">
            Cancelar
        </x-button>
    @endif
    <x-button type="submit" :variant="$submitVariant" data-cy="submit-button">
        {{ $submitLabel }}
    </x-button>
</div>

