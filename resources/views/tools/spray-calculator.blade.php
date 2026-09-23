@extends('layouts.tools')

@section('title', 'Calculadora de caldo fitosanitario: cubas y dosis por cuba')
@section('description', 'Calcula gratis los litros de caldo, el número de cubas y cuánto producto echar en cada cuba a partir de la dosis por hectárea, en % o en cc/hL.')
@section('breadcrumb', $tool['title'])

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl sm:text-5xl font-bold text-[var(--color-agro-green-dark)] mb-4">Calculadora de caldo fitosanitario</h1>
        <p class="text-lg text-gray-600 max-w-3xl">Mete la superficie, el volumen de caldo y la dosis de la etiqueta. Te decimos cuántas cubas salen y cuánto producto echar en cada una.</p>
    </div>

    <livewire:tools.spray-calculator />

    <article class="prose max-w-none mt-14">
        <h2>Cómo se calcula el caldo</h2>
        <p><strong>Litros de caldo</strong> = superficie (ha) × volumen de caldo (L/ha). El número de cubas sale de dividir ese total entre la capacidad de tu cuba.</p>
        <p>La cantidad de producto depende de cómo venga la dosis en la etiqueta:</p>
        <ul>
            <li><strong>L o kg por hectárea:</strong> producto por cuba = dosis × (capacidad de cuba ÷ L de caldo por ha).</li>
            <li><strong>Porcentaje (%):</strong> un 0,3 % son 0,3 L (o kg) por cada 100 L de caldo.</li>
            <li><strong>cc o g por hectolitro:</strong> 300 cc/hL son 300 cc por cada 100 L de caldo.</li>
        </ul>
        <p>Respeta siempre la dosis máxima por hectárea, los plazos de seguridad y las condiciones de uso de la etiqueta del producto. Esta calculadora solo hace la aritmética.</p>
    </article>

    @include('tools.partials.related')

    @push('schema')
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "WebApplication",
            "name": "Calculadora de caldo fitosanitario",
            "applicationCategory": "UtilitiesApplication",
            "operatingSystem": "Web",
            "offers": {"@@type": "Offer", "price": "0", "priceCurrency": "EUR"},
            "url": "{{ url()->current() }}"
        }
        </script>
    @endpush
@endsection
