@props([
    'src',
    'alt' => '',
    'width' => null,
    'height' => null,
    'lazy' => true,
    'class' => '',
    'priority' => false, // Para imágenes críticas (above the fold)
    'srcset' => null, // Array de imágenes para srcset: ['/path/image-400w.webp' => '400w', ...]
    'sizes' => null, // Atributo sizes para imágenes responsivas
])

@php
    $isAboveFold = $priority || !$lazy;
    $loading = $isAboveFold ? 'eager' : 'lazy';
    $fetchPriority = $priority ? 'high' : 'auto';
    
    // Generar srcset string si se proporciona
    $srcsetString = null;
    if ($srcset && is_array($srcset)) {
        $srcsetParts = [];
        foreach ($srcset as $imagePath => $descriptor) {
            $srcsetParts[] = asset($imagePath) . ' ' . $descriptor;
        }
        $srcsetString = implode(', ', $srcsetParts);
    }
@endphp

<img 
    src="{{ $src }}" 
    alt="{{ $alt }}"
    @if($width) width="{{ $width }}" @endif
    @if($height) height="{{ $height }}" @endif
    @if($srcsetString) srcset="{{ $srcsetString }}" @endif
    @if($sizes) sizes="{{ $sizes }}" @endif
    loading="{{ $loading }}"
    decoding="async"
    fetchpriority="{{ $fetchPriority }}"
    class="{{ $class }}"
    {{ $attributes }}
>

