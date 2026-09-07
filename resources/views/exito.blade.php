<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Permitido</title>
    <style>
        body { font-family: sans-serif; text-align: center; margin-top: 50px; background-color: #f4f4f9; }
        .card { display: inline-block; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .success { color: #28a745; }
    </style>
</head>
<body>
    <div class="card">
        <h1 class="success">¡Acceso Correcto!</h1>
        <p>Seguimos trabajando en el desarrollo del CRM, sea paciente.</p>
        <p><strong>Usuario verificado:</strong> {{ $usuario->correo }}</p>
    </div>
</body>
</html>