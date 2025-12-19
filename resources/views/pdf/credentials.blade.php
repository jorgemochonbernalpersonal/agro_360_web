<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Credenciales de Acceso - Agro365</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            padding: 40px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #22c55e;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #16a34a;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            font-size: 14px;
        }
        .content {
            margin: 30px 0;
        }
        .welcome {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .credentials-box {
            background-color: #f9fafb;
            border: 2px solid #22c55e;
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
        }
        .credentials-title {
            font-size: 18px;
            font-weight: bold;
            color: #16a34a;
            margin-bottom: 20px;
            text-align: center;
        }
        .credential-item {
            margin: 15px 0;
            padding: 15px;
            background-color: white;
            border-radius: 4px;
        }
        .credential-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .credential-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            font-family: 'Courier New', monospace;
        }
        .instructions {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px 20px;
            margin: 30px 0;
        }
        .instructions-title {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 10px;
        }
        .instructions-text {
            font-size: 13px;
            color: #78350f;
            line-height: 1.6;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
        .login-url {
            background-color: #eff6ff;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
            margin: 20px 0;
        }
        .login-url-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .login-url-value {
            font-size: 14px;
            color: #2563eb;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">AGRO365</div>
            <div class="subtitle">Sistema de Gestión Agrícola</div>
        </div>

        <div class="content">
            <div class="welcome">¡Bienvenido a Agro365!</div>
            
            <div class="message">
                <p>Se ha creado una cuenta para ti en nuestra plataforma. A continuación encontrarás tus credenciales de acceso:</p>
            </div>

            <div class="credentials-box">
                <div class="credentials-title">🔐 TUS CREDENCIALES DE ACCESO</div>
                
                <div class="credential-item">
                    <div class="credential-label">Email / Usuario</div>
                    <div class="credential-value">{{ $email }}</div>
                </div>

                <div class="credential-item">
                    <div class="credential-label">Contraseña Temporal</div>
                    <div class="credential-value">{{ $password }}</div>
                </div>
            </div>

            <div class="login-url">
                <div class="login-url-label">Accede a la plataforma en:</div>
                <div class="login-url-value">{{ url('/login') }}</div>
            </div>

            <div class="instructions">
                <div class="instructions-title">⚠️ Instrucciones Importantes</div>
                <div class="instructions-text">
                    <ul style="margin-left: 20px; margin-top: 10px;">
                        <li style="margin-bottom: 8px;">Por seguridad, <strong>deberás cambiar tu contraseña</strong> al iniciar sesión por primera vez.</li>
                        <li style="margin-bottom: 8px;">Tu email quedará automáticamente verificado al cambiar la contraseña.</li>
                        <li style="margin-bottom: 8px;">Guarda estas credenciales en un lugar seguro.</li>
                        <li>Si tienes problemas para acceder, contacta con el administrador.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="footer">
            <p><strong>Agro365</strong> - Sistema de Gestión Agrícola</p>
            <p style="margin-top: 5px;">Documento generado el {{ $created_at }}</p>
        </div>
    </div>
</body>
</html>
