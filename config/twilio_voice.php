<?php

return [
    // Prueba paralela: deshabilitada de forma predeterminada.
    'pilot_enabled' => env('TWILIO_PILOT_ENABLED', false),
    'account_sid' => env('TWILIO_ACCOUNT_SID', ''),
    'api_key_sid' => env('TWILIO_API_KEY_SID', ''),
    'api_key_secret' => env('TWILIO_API_KEY_SECRET', ''),
    'auth_token' => env('TWILIO_AUTH_TOKEN', ''),
    'twiml_app_sid' => env('TWILIO_TWIML_APP_SID', ''),
    'caller_id' => env('TWILIO_OUTBOUND_CALLER_ID', ''),
    // La URL pública EXACTA que Twilio utiliza al firmar sus webhooks.
    // Debe ser https://crm.interacom.mx (sin diagonal final) en producción.
    'webhook_base_url' => env('TWILIO_WEBHOOK_BASE_URL', ''),
    // Lista acotada de destinos autorizados para la prueba; separada por comas.
    'pilot_allowlist' => env('TWILIO_PILOT_ALLOWLIST', ''),
];
