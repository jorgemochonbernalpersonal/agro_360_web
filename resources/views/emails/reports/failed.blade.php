@component('mail::message')
# ❌ {{ __('Error al Generar Informe') }}

{{ __('Hola') }} **{{ $report->user->name }}**,

{{ __('Lamentablemente hubo un error al generar tu informe oficial.') }}

@component('mail::panel')
**{{ __('Tipo:') }}** {{ $report->report_type_name }}
**{{ __('Periodo:') }}** {{ $report->period_start->format('d/m/Y') }} - {{ $report->period_end->format('d/m/Y') }}
**{{ __('Error:') }}** {{ Str::limit($errorMessage, 200) }}
@endcomponent

@component('mail::button', ['url' => route('viticulturist.official-reports.create')])
{{ __('Intentar de Nuevo') }}
@endcomponent

**{{ __('¿Qué puedes hacer?') }}**
- {{ __('Verifica que el periodo seleccionado tenga datos registrados') }}
- {{ __('Reduce el rango de fechas si es muy amplio') }}
- {{ __('Contacta con soporte si el problema persiste') }}

{{ __('Gracias por tu paciencia.') }}

{{ __('Saludos,') }}<br>
{{ __('El equipo de :app', ['app' => config('app.name')]) }}
@endcomponent
