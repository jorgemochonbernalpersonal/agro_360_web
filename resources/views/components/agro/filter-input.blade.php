@props(['placeholder' => 'Buscar...'])

<flux:input icon="magnifying-glass" :placeholder="$placeholder" autocomplete="off" {{ $attributes }} />
