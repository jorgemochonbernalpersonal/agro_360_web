<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>403 — Acceso denegado</title>
    <style>
        body { font-family: sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; background:#f9fafb; }
        .box { text-align:center; }
        .code { font-size:6rem; font-weight:800; color:#dc2626; line-height:1; }
        .msg  { font-size:1.25rem; color:#374151; margin-top:1rem; }
        .sub  { font-size:0.9rem; color:#6b7280; margin-top:.5rem; }
        a     { color:#2563eb; text-decoration:none; margin-top:1.5rem; display:inline-block; }
    </style>
</head>
<body>
    <div class="box">
        <div class="code">403</div>
        <div class="msg">{{ $exception->getMessage() ?: 'Acceso denegado' }}</div>
        <div class="sub">No tienes permiso para acceder a esta página.</div>
        <a href="{{ url()->previous('/') }}">← Volver</a>
    </div>
</body>
</html>
