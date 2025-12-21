<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Informe Oficial - Agro365</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-3xl mx-auto">
            {{-- Header --}}
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">🔍 Verificación de Informe</h1>
                <p class="text-gray-600">Sistema de Verificación Pública - Agro365</p>
            </div>

            @if($found)
                {{-- Informe Encontrado --}}
                <div class="bg-white rounded-2xl shadow-2xl p-8 mb-6">
                    {{-- Estado del Informe --}}
                    @if($is_valid && ($integrity_valid ?? true))
                        <div class="mb-6 p-6 bg-green-50 border-2 border-green-500 rounded-xl flex items-center">
                            <svg class="w-12 h-12 text-green-600 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h2 class="text-2xl font-bold text-green-800">{{ $message }}</h2>
                                <p class="text-green-700">Documento firmado electrónicamente y sin modificaciones</p>
                                @if(isset($integrity_valid) && $integrity_valid)
                                    <p class="text-green-600 text-sm mt-1">✓ Integridad verificada: El contenido coincide con la firma original</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="mb-6 p-6 bg-red-50 border-2 border-red-500 rounded-xl flex items-center">
                            <svg class="w-12 h-12 text-red-600 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h2 class="text-2xl font-bold text-red-800">{{ $message }}</h2>
                                <p class="text-red-700">Este informe ya no es válido</p>
                                @if(isset($integrity_valid) && !$integrity_valid)
                                    <p class="text-red-600 text-sm mt-1">✗ Integridad comprometida: El contenido ha sido modificado después de la firma</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Información del Informe --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Información del Informe</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-semibold text-gray-600">Tipo:</span>
                                    <p class="text-gray-900">{{ $report->report_icon }} {{ $report->report_type_name }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-semibold text-gray-600">Periodo:</span>
                                    <p class="text-gray-900">{{ $report->period_start->format('d/m/Y') }} - {{ $report->period_end->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-semibold text-gray-600">Generado:</span>
                                    <p class="text-gray-900">{{ $report->created_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-semibold text-gray-600">Tamaño:</span>
                                    <p class="text-gray-900">{{ $report->formatted_pdf_size }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Titular de la Explotación</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-semibold text-gray-600">Nombre:</span>
                                    <p class="text-gray-900">{{ $report->user->name }}</p>
                                </div>
                                @if($report->user->profile)
                                <div>
                                    <span class="text-sm font-semibold text-gray-600">NIF/CIF:</span>
                                    <p class="text-gray-900">{{ $report->user->profile->nif ?? 'No especificado' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-semibold text-gray-600">Ubicación:</span>
                                    <p class="text-gray-900">
                                        @if($report->user->profile->municipality)
                                            {{ $report->user->profile->municipality->name }}
                                            @if($report->user->profile->municipality->province)
                                                ({{ $report->user->profile->municipality->province->name }})
                                            @endif
                                        @else
                                            No especificado
                                        @endif
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Firma Electrónica --}}
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Firma Electrónica
                        </h3>
                        <div class="space-y-2 font-mono text-sm">
                            <div>
                                <span class="text-gray-600">Hash:</span>
                                <p class="text-gray-900 break-all">{{ $report->signature_hash }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Firmado:</span>
                                <p class="text-gray-900">{{ $report->signed_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">IP:</span>
                                <p class="text-gray-900">{{ $report->signed_ip }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Verificaciones:</span>
                                <p class="text-gray-900">{{ $report->verification_count }} veces</p>
                            </div>
                        </div>
                    </div>

                    {{-- Metadata si es válido --}}
                    @if($is_valid && $report->report_metadata)
                        <div class="mt-6 bg-blue-50 rounded-xl p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Estadísticas del Informe</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($report->report_metadata as $key => $value)
                                    @if(is_numeric($value) || is_string($value))
                                        <div>
                                            <span class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                            <p class="text-lg font-bold text-gray-900">{{ $value }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Información Legal --}}
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">ℹ️ Información Legal</h3>
                    <p class="text-sm text-gray-600 mb-2">
                        Este documento ha sido generado automáticamente por el sistema certificado Agro365 
                        y firmado electrónicamente conforme al Real Decreto 1311/2012 sobre uso sostenible de productos fitosanitarios.
                    </p>
                    <p class="text-sm text-gray-600">
                        La firma electrónica garantiza:
                    </p>
                    <ul class="list-disc list-inside text-sm text-gray-600 mt-2 space-y-1">
                        <li><strong>Autenticidad:</strong> El documento fue creado por la persona titular indicada</li>
                        <li><strong>Integridad:</strong> El contenido no ha sido modificado desde su firma</li>
                        <li><strong>No repudio:</strong> La persona firmante no puede negar haberlo firmado</li>
                    </ul>
                </div>

            @else
                {{-- Informe No Encontrado --}}
                <div class="bg-white rounded-2xl shadow-2xl p-8">
                    <div class="text-center">
                        <svg class="mx-auto h-16 w-16 text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Informe No Encontrado</h2>
                        <p class="text-gray-600 mb-4">{{ $message }}</p>
                        <div class="bg-gray-100 rounded-lg p-4 inline-block">
                            <span class="text-sm text-gray-600">Código buscado:</span>
                            <p class="font-mono text-gray-900">{{ $code }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Footer --}}
            <div class="mt-8 text-center text-sm text-gray-600">
                <p>Sistema de Verificación Pública - Agro365</p>
                <p>{{ now()->format('Y') }} © Todos los derechos reservados</p>
            </div>
        </div>
    </div>
</body>
</html>
