@props([
    'src',
    'alt' => '',
    'width' => null,
    'height' => null,
    'lazy' => true,
    'class' => '',
    'priority' => false, // Para imágenes críticas (above the fold)
])

@php
    $isAboveFold = $priority || !$lazy;
    $loading = $isAboveFold ? 'eager' : 'lazy';
    $fetchPriority = $priority ? 'high' : 'auto';
@endphp

<img 
    src="{{ $src }}" 
    alt="{{ $alt }}"
    @if($width) width="{{ $width }}" @endif
    @if($height) height="{{ $height }}" @endif
    loading="{{ $loading }}"
    decoding="async"
    fetchpriority="{{ $fetchPriority }}"
    class="{{ $class }}"
    {{ $attributes }}
>

