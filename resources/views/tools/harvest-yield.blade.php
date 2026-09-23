@extends('layouts.tools')

@section('title', 'Estimador de vendimia: kg por hectárea y litros de vino')
@section('description', 'Estima gratis tu cosecha de uva por conteo de racimos: kg por cepa, kg por hectárea, kilos totales y litros de vino. Compara con el límite de tu D.O.')
@section('breadcrumb', $tool['title'])

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl sm:text-5xl font-bold text-[var(--color-agro-green-dark)] mb-4">Estimador de vendimia</h1>
        <p class="text-lg text-gray-600 max-w-3xl">Cuenta racimos en unas cuantas cepas, pesa algunos y te estimamos la cosecha de la parcela antes de vendimiar.</p>
    </div>

    <livewire:tools.harvest-yield-calculator />

    <article class="prose max-w-none mt-14">
        <h2>Cómo hacer un buen aforo</h2>
        <ol>
            <li>Elige <strong>al menos 20–30 cepas</strong> repartidas por la parcela (no solo las de la cabecera).</li>
            <li>Cuenta los racimos de cada una y calcula la media de <strong>racimos por cepa</strong>.</li>
            <li>Cerca de vendimia, pesa una muestra de racimos para obtener el <strong>peso medio</strong>. Antes del envero, ten en cuenta que el racimo aún ganará peso.</li>
            <li>Estima el porcentaje de <strong>marras</strong> (cepas muertas o improductivas).</li>
        </ol>
        <p>La fórmula es: cepas/ha × (1 − marras) × racimos por cepa × peso medio del racimo. Las cepas por hectárea salen del marco de plantación: 10.000 ÷ (distancia entre filas × distancia entre cepas).</p>
        <p>Los litros de vino son orientativos: el rendimiento de transformación habitual está en torno a 65–75 L por cada 100 kg de uva, y cada D.O. fija su máximo.</p>
    </article>

    @include('tools.partials.related')

    @push('schema')
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "WebApplication",
            "name": "Estimador de vendimia",
            "applicationCategory": "UtilitiesApplication",
            "operatingSystem": "Web",
            "offers": {"@@type": "Offer", "price": "0", "priceCurrency": "EUR"},
            "url": "{{ url()->current() }}"
        }
        </script>
    @endpush
@endsection
