@php
    $drNombre = config('app.legal_owner_name');
    $drDni = config('app.legal_owner_dni');
    $drDireccion = config('app.legal_owner_address');
    $drEmail = config('app.legal_contact_email');
    $drVendorNif = config('services.sif_software.vendor_nif');
    $drProductorCompleto = filled($drNombre) && filled($drDni) && filled($drDireccion);
@endphp

<div class="space-y-6">

    {{-- Estado --}}
    @if($drProductorCompleto)
        <div class="flex items-center gap-2 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-800">
            <span class="font-semibold">{{ __('Vigente') }}</span> — {{ __('declaración emitida por el productor identificado más abajo.') }}
        </div>
    @else
        <div class="flex items-center gap-2 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
            <span class="font-semibold">{{ __('Documento en preparación') }}</span> — {{ __('faltan los datos identificativos del productor. No tiene valor de declaración oficial hasta completarse.') }}
        </div>
    @endif

    <p class="text-sm text-gray-500">
        {{ __('Base normativa: artículo 13.4 del Reglamento (Real Decreto 1007/2023, de 5 de diciembre) y artículo 15 de la Orden HAC/1177/2024, de 17 de octubre.') }}
    </p>

    <h2 class="text-lg font-bold text-gray-900 uppercase tracking-wide">{{ __('Declaración responsable del sistema informático de facturación') }}</h2>

    {{-- 1. Datos del sistema --}}
    <section>
        <h3 class="text-base font-semibold text-gray-900 mb-3">{{ __('1. Datos del sistema informático') }}</h3>
        <dl class="divide-y divide-gray-100 border border-gray-200 rounded-xl overflow-hidden text-sm">
            <div class="flex justify-between gap-4 p-3 bg-gray-50"><dt class="text-gray-500">{{ __('Nombre del producto') }}</dt><dd class="font-medium text-gray-900">{{ config('services.sif_software.name', 'Agro365') }}</dd></div>
            <div class="flex justify-between gap-4 p-3"><dt class="text-gray-500">{{ __('Código identificador único') }}</dt><dd class="font-medium text-gray-900">{{ config('services.sif_software.id', 'A3') }}</dd></div>
            <div class="flex justify-between gap-4 p-3 bg-gray-50"><dt class="text-gray-500">{{ __('Versión declarada') }}</dt><dd class="font-medium text-gray-900">{{ config('services.sif_software.version', '1.0.0') }}</dd></div>
            <div class="flex justify-between gap-4 p-3"><dt class="text-gray-500">{{ __('Tipología') }}</dt><dd class="font-medium text-gray-900 text-right">{{ __('Sistema de facturación en la nube (SaaS)') }}</dd></div>
            <div class="flex justify-between gap-4 p-3 bg-gray-50"><dt class="text-gray-500">{{ __('Uso mono/multiusuario') }}</dt><dd class="font-medium text-gray-900 text-right">{{ __('Multiusuario — cada obligado tributario opera bajo su propio número de instalación') }}</dd></div>
            <div class="flex justify-between gap-4 p-3"><dt class="text-gray-500">{{ __('¿Funciona exclusivamente como VERI*FACTU?') }}</dt><dd class="font-medium text-gray-900">{{ __('Sí') }}</dd></div>
        </dl>
    </section>

    {{-- 2. Datos técnicos --}}
    <section>
        <h3 class="text-base font-semibold text-gray-900 mb-3">{{ __('2. Datos técnicos específicos') }}</h3>
        <dl class="divide-y divide-gray-100 border border-gray-200 rounded-xl overflow-hidden text-sm">
            <div class="flex justify-between gap-4 p-3 bg-gray-50"><dt class="text-gray-500">{{ __('Remisión automática a la AEAT') }}</dt><dd class="font-medium text-gray-900 text-right">{{ __('Sí, vía servicio web SOAP') }}</dd></div>
            <div class="flex justify-between gap-4 p-3"><dt class="text-gray-500">{{ __('Encadenamiento de registros') }}</dt><dd class="font-medium text-gray-900 text-right">{{ __('Huella SHA-256 encadenada por NIF de emisor') }}</dd></div>
            <div class="flex justify-between gap-4 p-3 bg-gray-50"><dt class="text-gray-500">{{ __('Firma electrónica') }}</dt><dd class="font-medium text-gray-900">{{ __('XAdES / XMLDSig (RSA-SHA256)') }}</dd></div>
            <div class="flex justify-between gap-4 p-3"><dt class="text-gray-500">{{ __('Código QR de verificación') }}</dt><dd class="font-medium text-gray-900">{{ __('Sí, en cada factura') }}</dd></div>
            <div class="flex justify-between gap-4 p-3 bg-gray-50"><dt class="text-gray-500">{{ __('Trazabilidad e inalterabilidad') }}</dt><dd class="font-medium text-gray-900 text-right">{{ __('Registro inmutable de cada envío y su resultado') }}</dd></div>
        </dl>
    </section>

    {{-- 3. Datos del productor --}}
    <section>
        <h3 class="text-base font-semibold text-gray-900 mb-3">{{ __('3. Datos de la persona o entidad productora') }}</h3>
        <dl class="divide-y divide-gray-100 border border-gray-200 rounded-xl overflow-hidden text-sm">
            <div class="flex justify-between gap-4 p-3 bg-gray-50"><dt class="text-gray-500">{{ __('Razón social') }}</dt><dd class="font-medium text-gray-900">{{ $drNombre ?: __('Pendiente') }}</dd></div>
            <div class="flex justify-between gap-4 p-3"><dt class="text-gray-500">{{ __('NIF/CIF') }}</dt><dd class="font-medium text-gray-900">{{ $drDni ?: __('Pendiente') }}</dd></div>
            <div class="flex justify-between gap-4 p-3 bg-gray-50"><dt class="text-gray-500">{{ __('Domicilio') }}</dt><dd class="font-medium text-gray-900 text-right">{{ $drDireccion ?: __('Pendiente') }}</dd></div>
            <div class="flex justify-between gap-4 p-3"><dt class="text-gray-500">{{ __('NIF declarado como vendor en el sistema') }}</dt><dd class="font-medium text-gray-900">{{ $drVendorNif ?: __('Pendiente') }}</dd></div>
        </dl>
    </section>

    {{-- 4. Certificación --}}
    <section>
        <h3 class="text-base font-semibold text-gray-900 mb-3">{{ __('4. Certificación de cumplimiento') }}</h3>
        <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 border border-gray-200 rounded-xl p-4">
            {{ __('El sistema informático de facturación :nombre, en su versión :version, cumple con lo dispuesto en el artículo 29.2.j) de la Ley 58/2003, General Tributaria, en el Real Decreto 1007/2023, de 5 de diciembre, y en las especificaciones técnicas, funcionales y de contenido establecidas en la Orden HAC/1177/2024, de 17 de octubre, en materia de integridad, conservación, accesibilidad, legibilidad, trazabilidad e inalterabilidad de los registros de facturación.', [
                'nombre' => config('services.sif_software.name', 'Agro365'),
                'version' => config('services.sif_software.version', '1.0.0'),
            ]) }}
        </p>
    </section>

    {{-- 5. Fecha y contacto --}}
    <section>
        <h3 class="text-base font-semibold text-gray-900 mb-3">{{ __('5. Fecha de emisión y contacto') }}</h3>
        <dl class="divide-y divide-gray-100 border border-gray-200 rounded-xl overflow-hidden text-sm">
            <div class="flex justify-between gap-4 p-3 bg-gray-50"><dt class="text-gray-500">{{ __('Fecha y lugar de emisión') }}</dt><dd class="font-medium text-gray-900">{{ __('Pendiente de firma') }}</dd></div>
            <div class="flex justify-between gap-4 p-3"><dt class="text-gray-500">{{ __('Correo de contacto') }}</dt><dd class="font-medium text-gray-900">{{ $drEmail }}</dd></div>
        </dl>
    </section>

</div>
