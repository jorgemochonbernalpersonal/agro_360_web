@component('mail::message')
# ❌ Error al Generar Informe

Hola **{{ $report->user->name }}**,

Lamentablemente hubo un error al generar tu informe oficial.

@component('mail::panel')
**Tipo:** {{ $report->report_type_name }}  
**Periodo:** {{ $report->period_start->format('d/m/Y') }} - {{ $report->period_end->format('d/m/Y') }}  
**Error:** {{ Str::limit($errorMessage, 200) }}
@endcomponent

@component('mail::button', ['url' => route('viticulturist.official-reports.create')])
Intentar de Nuevo
@endcomponent

**¿Qué puedes hacer?**
- Verifica que el periodo seleccionado tenga datos registrados
- Reduce el rango de fechas si es muy amplio
- Contacta con soporte si el problema persiste

Gracias por tu paciencia.

Saludos,<br>
El equipo de {{ config('app.name') }}
@endcomponent
