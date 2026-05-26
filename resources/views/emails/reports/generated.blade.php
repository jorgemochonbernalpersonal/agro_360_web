@component('mail::message')
# ✅ {{ __('Tu Informe Está Listo') }}

{{ __('Hola') }} **{{ $report->user->name }}**,

{{ __('Tu informe oficial ha sido generado exitosamente y está listo para descargar.') }}

@component('mail::panel')
**{{ __('Tipo:') }}** {{ $report->report_type_name }}
**{{ __('Periodo:') }}** {{ $report->period_start->format('d/m/Y') }} - {{ $report->period_end->format('d/m/Y') }}
**{{ __('Generado:') }}** {{ $report->created_at->format('d/m/Y H:i') }}
@if($report->pdfExists())
**{{ __('Tamaño:') }}** {{ number_format($report->pdf_size / 1024, 2) }} KB
@endif
@endcomponent

@component('mail::button', ['url' => route('viticulturist.official-reports.index')])
{{ __('Ver Mis Informes') }}
@endcomponent

{{ __('Puedes descargar, compartir o verificar tu informe desde el panel de control.') }}

**{{ __('Código de Verificación:') }}** `{{ $report->verification_code }}`

{{ __('Gracias por usar :app.', ['app' => config('app.name')]) }}

{{ __('Saludos,') }}<br>
{{ __('El equipo de :app', ['app' => config('app.name')]) }}
@endcomponent
