<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $primaryKey = 'id_cliente';

    /*
    |--------------------------------------------------------------------------
    | FECHAS
    |--------------------------------------------------------------------------
    */

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualizacion';

    public $timestamps = true;

    protected $casts = [
        'fecha_registro' => 'date',
        'fecha_actualizacion' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | CAMPOS ASIGNABLES
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'empresa',
        'telefono_principal',
        'telefono_secundario',
        'correo',
        'pais',
        'estado',
        'ciudad',
        'fuente',
        'id_tipo_cliente',
        'id_estado_lead',
        'id_empresa',
    ];

    /*
    |--------------------------------------------------------------------------
    | NOMBRE COMPLETO
    |--------------------------------------------------------------------------
    */

    public function getNombreCompletoAttribute()
    {
        return trim(
            "{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CAMPAÑAS
    |--------------------------------------------------------------------------
    |
    | Un cliente puede pertenecer a una o varias campañas.
    |
    */

    public function campanas(): BelongsToMany
    {
        return $this->belongsToMany(
            Campana::class,
            'campana_cliente',
            'id_cliente',
            'id_campana',
            'id_cliente',
            'id_campana'
        )
        ->withPivot([
            'id_campana_cliente',
            'fecha_asignacion',
            'estado',
            'intentos',
        ])
        ->withTimestamps();
    }
}