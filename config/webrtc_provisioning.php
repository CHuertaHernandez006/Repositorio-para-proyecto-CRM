<?php

return [
    // Nunca habilitar sin un helper privado autenticado, autorizado y auditado.
    'enabled' => (bool) env('WEBRTC_PROVISIONING_ENABLED', false),
    'url' => (string) env('WEBRTC_PROVISIONING_URL', ''),
    'key' => (string) env('WEBRTC_PROVISIONING_KEY', ''),
    'timeout' => (int) env('WEBRTC_PROVISIONING_TIMEOUT', 6),

    // Por seguridad, las extensiones antiguas de Zoiper no se modifican.
    // Redes habilita solo extensiones WebRTC administradas; ejemplo: 1099.
    'managed_extensions' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('WEBRTC_MANAGED_EXTENSIONS', ''))
    ))),
];
