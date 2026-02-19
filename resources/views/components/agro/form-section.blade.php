@props(['title', 'description' => null])

<flux:fieldset>
    <flux:legend>{{ $title }}</flux:legend>
    @if($description)
        <flux:description>{{ $description }}</flux:description>
    @endif
    {{ $slot }}
</flux:fieldset>
