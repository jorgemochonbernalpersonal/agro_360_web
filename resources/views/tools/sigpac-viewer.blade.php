@extends('layouts.tools')

@section('title', 'Visor SIGPAC: consulta tu recinto por referencia')
@section('description', 'Consulta gratis cualquier recinto SIGPAC: superficie, uso, pendiente media, coeficiente de regadío, altitud e incidencias, con el recinto dibujado sobre la ortofoto.')
@section('breadcrumb', $tool['title'])

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl sm:text-5xl font-bold text-[var(--color-agro-green-dark)] mb-4">Visor de recinto SIGPAC</h1>
        <p class="text-lg text-gray-600 max-w-3xl">Mete la referencia SIGPAC de tu recinto y verás su superficie, uso, pendiente y regadío, dibujado sobre la ortofoto del PNOA.</p>
    </div>

    <livewire:tools.sigpac-viewer />

    <article class="prose max-w-none mt-14">
        <h2>Cómo leer una referencia SIGPAC</h2>
        <p>Cada recinto se identifica con 7 números: <strong>provincia : municipio : agregado : zona : polígono : parcela : recinto</strong>. Los encontrarás en tu declaración de la PAC o en el visor oficial. El agregado y la zona suelen ser 0.</p>
        <p>Los datos se consultan en tiempo real en el servicio público de consultas de SIGPAC. Son informativos: para cualquier trámite vale siempre la información oficial.</p>
        <p>¿Quieres saber más? Lee nuestra guía <a href="{{ content_route('content.que-es-sigpac') }}">qué es SIGPAC y cómo funciona</a>.</p>
    </article>

    @include('tools.partials.related')
@endsection
