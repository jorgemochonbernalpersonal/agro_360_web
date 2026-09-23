@extends('layouts.tools')

@section('title', 'Baja de las alertas de mildiu y oídio')
@section('description', 'Darse de baja de las alertas de mildiu y oídio de Agro365.')
@section('breadcrumb', 'Baja de alertas')

@push('schema')
    <meta name="robots" content="noindex">
@endpush

@section('content')
    <div class="max-w-2xl bg-white rounded-2xl border border-gray-200 shadow-sm p-8">
        @if (! $subscription)
            <h1 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-3">Enlace no válido</h1>
            <p class="text-gray-700 mb-6">Este enlace de baja no existe o ya no es válido.</p>
        @elseif ($subscription->unsubscribed_at)
            <h1 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-3">Te has dado de baja</h1>
            <p class="text-gray-700 mb-6">Ya no recibirás más alertas de mildiu y oídio para {{ $subscription->municipality?->name ?? 'tu municipio' }}.</p>
        @else
            <h1 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-3">¿Dejar de recibir las alertas?</h1>
            <p class="text-gray-700 mb-6">Dejarás de recibir los avisos de mildiu y oídio para {{ $subscription->municipality?->name ?? 'tu municipio' }}.</p>
            <form method="POST" action="{{ route('tools.disease-alerts.unsubscribe.confirm', $subscription->token) }}" class="mb-6">
                @csrf
                <button type="submit" class="px-6 py-3 rounded-lg bg-[var(--color-agro-green-dark)] text-white font-semibold">Sí, darme de baja</button>
            </form>
        @endif
        <a href="{{ route('tools.show', 'alertas-mildiu-oidio') }}" class="text-[var(--color-agro-green-dark)] font-semibold hover:underline">Volver a las alertas</a>
    </div>
@endsection
