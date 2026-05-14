@props(['placeholder' => 'Buscar...'])

<flux:input icon="magnifying-glass" :placeholder="$placeholder" autocomplete="nope" role="presentation" {{ $attributes }} />
