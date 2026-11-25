<?php

return [
    // Habilitación del proveedor de llamadas externas.
    // Fallback temporal para instalaciones que aún usan TWILIO_PILOT_ENABLED.
    'enabled' => (bool) env('TWILIO_VOICE_ENABLED', env('TWILIO_PILOT_ENABLED', false)),

    'account_sid' => env('TWILIO_ACCOUNT_SID', ''),
    'api_key_sid' => env('TWILIO_API_KEY_SID', ''),
    'api_key_secret' => env('TWILIO_API_KEY_SECRET', ''),
    'auth_token' => env('TWILIO_AUTH_TOKEN', ''),
    'twiml_app_sid' => env('TWILIO_TWIML_APP_SID', ''),
    'caller_id' => env('TWILIO_OUTBOUND_CALLER_ID', ''),
    'webhook_base_url' => env('TWILIO_WEBHOOK_BASE_URL', ''),
];
