<?php

return [

    'ami' => [
        'host' =>
            env('ASTERISK_AMI_HOST'),

        'port' =>
            (int) env(
                'ASTERISK_AMI_PORT',
                5038
            ),

        'username' =>
            env('ASTERISK_AMI_USERNAME'),

        'password' =>
            env('ASTERISK_AMI_PASSWORD'),

        'connect_timeout' =>
            (float) env(
                'ASTERISK_AMI_CONNECT_TIMEOUT',
                5
            ),

        'read_timeout' =>
            (int) env(
                'ASTERISK_AMI_READ_TIMEOUT',
                5
            ),
    ],

    'agent_tech' =>
        env(
            'ASTERISK_AGENT_TECH',
            'PJSIP'
        ),

    'outbound_context' =>
        env(
            'ASTERISK_OUTBOUND_CONTEXT',
            'from-crm'
        ),

    'originate_timeout' =>
        (int) env(
            'ASTERISK_ORIGINATE_TIMEOUT',
            30000
        ),

    /*
    |--------------------------------------------------------------------------
    | Webhook Asterisk -> Laravel
    |--------------------------------------------------------------------------
    */

    'webhook_token' =>
        env(
            'ASTERISK_WEBHOOK_TOKEN'
        ),

    /*
    |--------------------------------------------------------------------------
    | Grabaciones de Asterisk
    |--------------------------------------------------------------------------
    */

    'recordings_path' =>
        env(
            'ASTERISK_RECORDINGS_PATH',
            '/var/spool/asterisk/monitor'
        ),

];
