@extends('layouts.tools')

@section('title', 'Buscador de fitosanitarios autorizados para viñedo')
@section('description', 'Busca gratis los productos fitosanitarios autorizados por cultivo y plaga: dosis, plazo de seguridad, nº de aplicaciones y condiciones, con datos del registro oficial del MAPA.')
@section('breadcrumb', $tool['title'])

@section('content')
    <div class="mb-8">
        <h1 class="text-4xl sm:text-5xl font-bold text-[var(--color-agro-green-dark)] mb-4">Buscador de fitosanitarios autorizados</h1>
        <p class="text-lg text-gray-600 max-w-3xl">Elige cultivo y plaga y verás qué productos están autorizados, con su dosis, su plazo de seguridad y sus condiciones de uso, según el registro oficial del Ministerio de Agricultura.</p>
    </div>

    <livewire:tools.phyto-search />

    <article class="prose max-w-none mt-14">
        <h2>Qué es el plazo de seguridad</h2>
        <p>Es el número mínimo de días que deben pasar entre el último tratamiento y la vendimia. Si vendimias antes, la uva puede superar el límite máximo de residuos y la bodega puede rechazarla. Cuando aparece «No procede», el registro no fija plazo para ese uso (por ejemplo, porque se aplica antes de que haya fruto); revisa igualmente sus condiciones de uso.</p>
        <h2>Cómo leer la dosis</h2>
        <p>La dosis puede venir por hectárea (L/ha, kg/ha) o por concentración en el caldo (%, ml/hL, g/hL). Si necesitas pasarla a cantidad por cuba, usa nuestra <a href="{{ route('tools.show', 'calculadora-caldo-fitosanitario') }}">calculadora de caldo fitosanitario</a>.</p>
        <p>Recuerda que desde 2027 los tratamientos deben anotarse en el <a href="{{ content_route('content.cuaderno-campo-digital-2027') }}">cuaderno de campo digital</a> con el nº de registro del producto.</p>
    </article>

    @include('tools.partials.related')
@endsection
