{{-- Thin wrapper → canonical layout is layouts/app.blade.php --}}
{{-- This component exists for backward compatibility with views using <x-app-layout> --}}
@include('layouts.app', ['slot' => $slot, 'title' => $title ?? null])
