<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Informe Oficial Generado') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 24px;">{{ __('✅ Informe Generado') }}</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <p style="font-size: 16px; margin-bottom: 20px;">{{ __('Hola') }} <strong>{{ $report->user->name }}</strong>,</p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">{{ __('Tu informe oficial ha sido generado y firmado electrónicamente con éxito.') }}</p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #059669;">
            <h3 style="margin-top: 0; color: #059669;">{{ __('📄 Detalles del Informe') }}</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; width: 40%;">{{ __('Tipo:') }}</td>
                    <td style="padding: 8px 0;">{{ $report->report_type_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">{{ __('Periodo:') }}</td>
                    <td style="padding: 8px 0;">{{ $report->period_start->format('d/m/Y') }} - {{ $report->period_end->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">{{ __('Generado:') }}</td>
                    <td style="padding: 8px 0;">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">{{ __('Tamaño:') }}</td>
                    <td style="padding: 8px 0;">{{ $report->formatted_pdf_size }}</td>
                </tr>
            </table>
        </div>

        <div style="background: #fef3c7; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #f59e0b;">
            <p style="margin: 0; font-size: 14px; color: #92400e;">
                <strong>{{ __('🔐 Firma Electrónica:') }}</strong> {{ __('Este documento ha sido firmado con SHA-256 y es legalmente válido.') }}
            </p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('viticulturist.official-reports.index') }}" 
               style="display: inline-block; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                {{ __('Ver en Agro365') }}
            </a>
        </div>

        <div style="background: #eff6ff; padding: 15px; border-radius: 8px; margin-top: 20px;">
            <p style="margin: 0; font-size: 13px; color: #1e40af;">
                <strong>{{ __('Código de Verificación:') }}</strong><br>
                <code style="background: white; padding: 5px 10px; border-radius: 4px; display: inline-block; margin-top: 5px;">{{ $report->verification_code }}</code>
            </p>
            <p style="margin: 10px 0 0 0; font-size: 12px; color: #6b7280;">
                {{ __('Puedes verificar este informe en:') }} <a href="{{ $report->verification_url }}" style="color: #059669;">{{ $report->verification_url }}</a>
            </p>
        </div>
        
        <p style="font-size: 14px; color: #6b7280; margin-top: 30px; text-align: center;">{{ __('Este es un correo automático. Por favor, no respondas a este mensaje.') }}</p>
    </div>
</body>
</html>
