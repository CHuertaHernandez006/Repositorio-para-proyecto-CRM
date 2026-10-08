<?php

return [
    // Empresa interna propietaria del equipo y del seguimiento comercial.
    'empresa_id' => env('COMERCIAL_EMPRESA_ID'),

    // Empresas de origen autorizadas para captar prospectos comerciales.
    // Si no se define, conserva el comportamiento de la empresa interna.
    'comi_empresa_ids' => env('COMI_EMPRESA_IDS', env('COMERCIAL_EMPRESA_ID')),

    'rol_nombre' => 'Asesor comercial interno',
];
