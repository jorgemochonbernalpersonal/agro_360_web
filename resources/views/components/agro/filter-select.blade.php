@props(['label' => null, 'placeholder' => null])

<div class="flex flex-col gap-1">
    @if($label)
        <x-agro.field-label>{{ $label }}</x-agro.field-label>
    @endif
    <flux:select {{ $attributes }}>
        @if($placeholder)
            <flux:select.option value="">{{ $placeholder }}</flux:select.option>
        @endif
        {{ $slot }}
    </flux:select>
</div>
