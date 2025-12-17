<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Credenciales de Acceso - {{ $viticulturist->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            padding: 30px;
            color: #111827;
            background: #ffffff;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #1e40af;
        }
        .header h1 {
            font-size: 28px;
            color: #1e40af;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .header p {
            font-size: 14px;
            color: #6b7280;
        }
        .credentials {
            background: #f3f4f6;
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
            border: 2px solid #e5e7eb;
        }
        .credentials h2 {
            font-size: 20px;
            color: #1e40af;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .field {
            margin: 15px 0;
            padding: 12px;
            background: #ffffff;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }
        .label {
            font-weight: bold;
            color: #374151;
            font-size: 14px;
            display: block;
            margin-bottom: 5px;
        }
        .value {
            color: #111827;
            font-size: 16px;
        }
        .password-value {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            color: #dc2626;
            font-weight: bold;
            letter-spacing: 2px;
            background: #fef2f2;
            padding: 8px 12px;
            border-radius: 6px;
            display: inline-block;
            margin-top: 5px;
        }
        .warning {
            margin-top: 30px;
            padding: 20px;
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
        }
        .warning p {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .warning ul {
            margin-left: 20px;
            color: #78350f;
            font-size: 13px;
        }
        .warning li {
            margin: 5px 0;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #6b7280;
            font-size: 11px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Agro365</div>
        <h1>Credenciales de Acceso</h1>
        <p>Sistema de Gestión Vitícola</p>
    </div>
    
    <div class="credentials">
        <h2>Datos del Viticultor</h2>
        <div class="field">
            <span class="label">Nombre:</span>
            <span class="value">{{ $viticulturist->name }}</span>
        </div>
        <div class="field">
            <span class="label">Email:</span>
            <span class="value">{{ $viticulturist->email }}</span>
        </div>
        <div class="field">
            <span class="label">Contraseña Temporal:</span>
            <div class="password-value">{{ $password }}</div>
        </div>
    </div>
    
    <div class="warning">
        <p>⚠️ Importante:</p>
        <ul>
            <li>Guarda este documento de forma segura</li>
            <li><strong>La contraseña es temporal y DEBES cambiarla en tu primer acceso</strong></li>
            <li>Al iniciar sesión, el sistema te pedirá cambiar tu contraseña</li>
            <li>Una vez que cambies la contraseña, tu email será verificado automáticamente</li>
            <li>Accede al sistema en: {{ config('app.url') }}/login</li>
            <li>No compartas estas credenciales con terceros</li>
        </ul>
    </div>
    
    <div class="footer">
        <p>Creado por: <strong>{{ $creator->name }}</strong></p>
        <p>Fecha de creación: {{ now()->format('d/m/Y H:i') }}</p>
        <p style="margin-top: 10px; font-size: 10px; color: #9ca3af;">
            Este documento contiene información confidencial. Manténlo seguro.
        </p>
    </div>
</body>
</html>

