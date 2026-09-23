@props(['model', 'label', 'suffix' => null, 'step' => 'any', 'help' => null, 'placeholder' => null])

<div>
    <label for="{{ $model }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
    <div class="relative">
        <input
            id="{{ $model }}"
            type="number"
            inputmode="decimal"
            step="{{ $step }}"
            min="0"
            wire:model.live.debounce.300ms="{{ $model }}"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--color-agro-green)] focus:ring-[var(--color-agro-green)] {{ $suffix ? 'pr-16' : '' }}"
        >
        @if ($suffix)
            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-500 pointer-events-none">{{ $suffix }}</span>
        @endif
    </div>
    @if ($help)
        <p class="mt-1 text-xs text-gray-500">{{ $help }}</p>
    @endif
    @error($model)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
