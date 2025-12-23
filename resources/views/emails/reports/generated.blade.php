@component('mail::message')
# ✅ Tu Informe Está Listo

Hola **{{ $report->user->name }}**,

Tu informe oficial ha sido generado exitosamente y está listo para descargar.

@component('mail::panel')
**Tipo:** {{ $report->report_type_name }}  
**Periodo:** {{ $report->period_start->format('d/m/Y') }} - {{ $report->period_end->format('d/m/Y') }}  
**Generado:** {{ $report->created_at->format('d/m/Y H:i') }}  
@if($report->pdfExists())
**Tamaño:** {{ number_format($report->pdf_size / 1024, 2) }} KB
@endif
@endcomponent

@component('mail::button', ['url' => route('viticulturist.official-reports.index')])
Ver Mis Informes
@endcomponent

Puedes descargar, compartir o verificar tu informe desde el panel de control.

**Código de Verificación:** `{{ $report->verification_code }}`

Gracias por usar {{ config('app.name') }}.

Saludos,<br>
El equipo de {{ config('app.name') }}
@endcomponent
