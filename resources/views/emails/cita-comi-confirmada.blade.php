<!doctype html>
<html lang="es"><head><meta charset="utf-8"><title>Confirmación de cita</title></head>
<body style="margin:0;padding:24px;background:#f1f5f9;font-family:Arial,sans-serif;color:#1e293b;">
    <div style="max-width:600px;margin:auto;background:#fff;border:1px solid #cbd5e1;border-radius:12px;padding:28px;">
        <p style="color:#0284c7;font-weight:bold;">COMICenter / InterAcom</p>
        <h1 style="font-size:24px;">Tu cita está confirmada</h1>
        <p>Hola, {{ $cita['contacto'] }}:</p>
        <p>Confirmamos tu cita con nuestro equipo.</p>
        <p><strong>Fecha y hora:</strong> {{ $cita['fecha'] }}<br><strong>Zona horaria:</strong> {{ $cita['zona'] }}</p>
        <p><strong>Tu asesor:</strong> {{ $cita['asesor'] }}</p>
        @if($cita['motivo'])<p><strong>Motivo:</strong> {{ $cita['motivo'] }}</p>@endif
        <p>Gracias por tu interés. Nuestro equipo te atenderá en el horario indicado.</p>
        <p style="font-size:12px;color:#64748b;">Referencia de cita: {{ $cita['id'] }}</p>
    </div>
</body></html>
