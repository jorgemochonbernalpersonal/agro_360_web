@props(['placeholder' => 'Buscar...'])

<flux:input icon="magnifying-glass" :placeholder="$placeholder" autocomplete="one-time-code" {{ $attributes }} />
