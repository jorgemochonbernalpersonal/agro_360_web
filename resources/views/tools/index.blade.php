@extends('layouts.tools')

@section('title', 'Herramientas gratis para viticultores y agricultores')
@section('description', 'Calculadora de caldo fitosanitario, estimador de vendimia, visor SIGPAC y test del cuaderno digital 2027. Herramientas gratuitas y sin registro para el campo.')

@section('content')
    <div class="mb-10">
        <h1 class="text-4xl sm:text-5xl font-bold text-[var(--color-agro-green-dark)] mb-4">Herramientas gratis para el campo</h1>
        <p class="text-lg text-gray-600 max-w-3xl">Calculadoras y tests pensados para viticultores, bodegas y técnicos. Gratis, sin registro y funcionan en el móvil.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-14">
        @foreach ($tools as $slug => $tool)
            <a href="{{ route('tools.show', $slug) }}" class="group block bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-[var(--color-agro-green)] transition">
                <div class="text-3xl mb-3" aria-hidden="true">{{ $tool['icon'] }}</div>
                <h2 class="text-lg font-bold text-gray-900 group-hover:text-[var(--color-agro-green-dark)] mb-2">{{ $tool['title'] }}</h2>
                <p class="text-sm text-gray-600 mb-4">{{ $tool['summary'] }}</p>
                <span class="text-sm font-semibold text-[var(--color-agro-green-dark)]">Usar gratis →</span>
            </a>
        @endforeach
    </div>

    <h2 class="text-2xl font-bold text-gray-900 mb-4">Próximamente</h2>
    <div class="grid sm:grid-cols-2 gap-4 mb-14">
        @foreach ($upcoming as $item)
            <div class="flex gap-4 bg-gray-50 rounded-xl border border-dashed border-gray-300 p-5">
                <div class="text-2xl" aria-hidden="true">{{ $item['icon'] }}</div>
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $item['title'] }}</h3>
                    <p class="text-sm text-gray-600">{{ $item['summary'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <livewire:tools.lead-capture
        tool="tools-index"
        title="¿Qué herramienta te falta?"
        subtitle="Déjanos tu email y te avisamos cuando publiquemos las siguientes. Cero spam."
    />
@endsection
