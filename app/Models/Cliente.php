<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';

    // Nombres de las columnas de fechas en tu base de datos.
    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    public $timestamps = true;

    protected $casts = [
        'fecha_registro' => 'date',
        'fecha_actualizacion' => 'date',
    ];
}