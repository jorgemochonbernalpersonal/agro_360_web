<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe Compartido</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 24px;">📄 Informe Oficial Compartido</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <p style="font-size: 16px; margin-bottom: 20px;">
            <strong>{{ $senderName }}</strong> te ha compartido un informe oficial:
        </p>

        @if($customMessage)
            <div style="background: #dbeafe; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #3b82f6;">
                <p style="margin: 0; font-size: 14px; color: #1e40af; font-style: italic;">
                    "{{ $customMessage }}"
                </p>
            </div>
        @endif
        
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #3b82f6;">
            <h3 style="margin-top: 0; color: #3b82f6;">📋 Detalles del Informe</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; width: 40%;">Tipo:</td>
                    <td style="padding: 8px 0;">{{ $report->report_type_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Periodo:</td>
                    <td style="padding: 8px 0;">{{ $report->period_start->format('d/m/Y') }} - {{ $report->period_end->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Generado:</td>
                    <td style="padding: 8px 0;">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Titular:</td>
                    <td style="padding: 8px 0;">{{ $report->user->name }}</td>
                </tr>
            </table>
        </div>

        <div style="background: #dcfce7; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #059669;">
            <p style="margin: 0; font-size: 14px; color: #14532d;">
                <strong>🔐 Firma Electrónica Válida:</strong> Este documento está firmado digitalmente y es legalmente válido.
            </p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $report->verification_url }}" 
               style="display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                🔍 Verificar Autenticidad
            </a>
        </div>

        <div style="background: #fef3c7; padding: 15px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #f59e0b;">
            <p style="margin: 0; font-size: 13px; color: #92400e;">
                <strong>⚠️ Importante:</strong> El PDF adjunto contiene un código QR que puedes escanear para verificar su autenticidad en cualquier momento.
            </p>
        </div>
        
        <p style="font-size: 14px; color: #6b7280; margin-top: 30px; text-align: center;">
            Este correo ha sido enviado desde Agro365
        </p>
    </div>
</body>
</html>
