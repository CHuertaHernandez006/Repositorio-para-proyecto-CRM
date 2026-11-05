<?php

return [
    // Deshabilitado por defecto. Activar solamente después de pruebas de redes.
    'enabled' => (bool) env('WEBRTC_ENABLED', false),

    // URL pública accesible desde el NAVEGADOR: wss://sip.ejemplo.mx/ws
    'wss_url' => env('WEBRTC_WSS_URL', ''),

    // Dominio SIP público/certificado, por ejemplo sip.ejemplo.mx
    'sip_domain' => env('WEBRTC_SIP_DOMAIN', ''),
];
