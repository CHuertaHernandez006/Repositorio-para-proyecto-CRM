<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>
        Recuperación de contraseña
    </title>

</head>


<body
    style="
        margin:0;
        padding:0;
        background:#0f172a;
        font-family:Arial,Helvetica,sans-serif;
        color:#e2e8f0;
    "
>

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    role="presentation"
>

<tr>

<td
    align="center"
    style="padding:40px 16px;"
>

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    role="presentation"
    style="
        max-width:580px;
        background:#172033;
        border:1px solid #334155;
        border-radius:14px;
    "
>

<tr>

<td
    style="
        padding:30px;
    "
>

    <div
        style="
            font-size:24px;
            font-weight:700;
            color:#ffffff;
            margin-bottom:25px;
        "
    >

        COMI<span style="color:#38bdf8;">Center</span>

    </div>


    <h1
        style="
            margin:0 0 16px;
            font-size:22px;
            color:#ffffff;
        "
    >
        Recuperación de contraseña
    </h1>


    <p
        style="
            color:#cbd5e1;
            font-size:14px;
            line-height:1.7;
        "
    >

        Hola {{ $usuario->nombre }}.

    </p>


    <p
        style="
            color:#cbd5e1;
            font-size:14px;
            line-height:1.7;
        "
    >

        Recibimos una solicitud para restablecer
        la contraseña de tu cuenta en COMICenter.

    </p>


    <div
        style="
            margin:28px 0;
            text-align:center;
        "
    >

        <a
            href="{{ $url }}"
            style="
                display:inline-block;
                padding:13px 22px;
                background:#0284c7;
                color:#ffffff;
                text-decoration:none;
                border-radius:8px;
                font-size:14px;
                font-weight:700;
            "
        >
            Restablecer contraseña
        </a>

    </div>


    <p
        style="
            color:#94a3b8;
            font-size:12px;
            line-height:1.6;
        "
    >

        Este enlace es válido durante
        <strong style="color:#e2e8f0;">
            30 minutos
        </strong>
        y solo puede utilizarse una vez.

    </p>


    <p
        style="
            color:#94a3b8;
            font-size:12px;
            line-height:1.6;
        "
    >

        Si tú no solicitaste este cambio,
        puedes ignorar este mensaje.
        Tu contraseña actual seguirá funcionando.

    </p>


    <hr
        style="
            border:0;
            border-top:1px solid #334155;
            margin:26px 0;
        "
    >


    <p
        style="
            margin:0;
            color:#64748b;
            font-size:11px;
        "
    >

        © {{ date('Y') }} Plataforma COMI

    </p>

</td>

</tr>

</table>

</td>

</tr>

</table>

</body>

</html>