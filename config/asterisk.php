<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Asterisk Manager Interface (AMI)
    |--------------------------------------------------------------------------
    |
    | Laravel usa AMI para solicitar llamadas salientes.
    | No pongas credenciales reales directamente en este archivo.
    | Configúralas en .env.
    |
    */

    'ami' => [
        'host' => env('ASTERISK_AMI_HOST'),
        'port' => (int) env('ASTERISK_AMI_PORT', 5038),
        'username' => env('ASTERISK_AMI_USERNAME'),
        'password' => env('ASTERISK_AMI_PASSWORD'),
        'connect_timeout' => (float) env('ASTERISK_AMI_CONNECT_TIMEOUT', 5),
        'read_timeout' => (int) env('ASTERISK_AMI_READ_TIMEOUT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Canal del operario
    |--------------------------------------------------------------------------
    |
    | Para endpoints PJSIP típicos:
    | PJSIP/1004
    |
    */

    'agent_tech' => env('ASTERISK_AGENT_TECH', 'PJSIP'),

    /*
    |--------------------------------------------------------------------------
    | Contexto del dialplan para llamadas originadas desde CRM
    |--------------------------------------------------------------------------
    |
    | Raziel debe crear/configurar este contexto en extensions.conf.
    |
    */

    'outbound_context' => env('ASTERISK_OUTBOUND_CONTEXT', 'from-crm'),

    /*
    |--------------------------------------------------------------------------
    | Timeout de Originate
    |--------------------------------------------------------------------------
    */

    'originate_timeout' => (int) env('ASTERISK_ORIGINATE_TIMEOUT', 30000),
'webhook_token' => env('ASTERISK_WEBHOOK_TOKEN')

    ];
