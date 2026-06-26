{{--
    Badge oficial "Disponible en Google Play" (imagen de Google — uso bajo Google Play badge guidelines).
    Variables opcionales:
      $extraClass — clases extra para el <a>.
      $eager      — true si el badge está above the fold (evita loading=lazy en el hero).
--}}
@php
    $playStoreUrl = config('app.play_store_url', 'https://play.google.com/store/apps/details?id=com.agro365.mobile');
    $extraClass   = $extraClass ?? '';
    $eager        = $eager ?? false;
@endphp
<a href="{{ $playStoreUrl }}"
   target="_blank" rel="noopener noreferrer"
   class="inline-block {{ $extraClass }}"
   aria-label="{{ __('Descargar Agro365 en Google Play') }}">
    <img src="{{ asset('images/google-play-badge-es.png') }}"
         alt="{{ __('Disponible en Google Play') }}"
         width="200" height="59"
         class="h-[52px] w-auto"
         loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async">
</a>
