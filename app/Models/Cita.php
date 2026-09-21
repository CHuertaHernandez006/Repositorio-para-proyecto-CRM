<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'citas';

    protected $primaryKey = 'id_cita';

    /*
    |--------------------------------------------------------------------------
    | La tabla CITAS del proyecto no maneja created_at / updated_at
    |--------------------------------------------------------------------------
    */
    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Campos que pueden asignarse masivamente
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'id_cliente',
        'id_usuario',
        'id_llamada',
        'id_estado_cita',
        'fecha_hora_inicio',
        'fecha_hora_fin',
        'motivo',
        'observaciones',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de tipos
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'fecha_hora_inicio' => 'datetime',
        'fecha_hora_fin' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones Eloquent
    |--------------------------------------------------------------------------
    */

    /**
     * Cliente relacionado con la cita.
     */
    public function cliente()
    {
        return $this->belongsTo(
            Cliente::class,
            'id_cliente',
            'id_cliente'
        );
    }

    /**
     * Usuario / Operario responsable de la cita.
     * Ajusta la clase (User::class o Usuario::class) y la clave primaria ('id' o 'id_usuario')
     * según la estructura exacta de tu proyecto.
     */
    public function usuario()
    {
        return $this->belongsTo(
            User::class,
            'id_usuario',
            'id'
        );
    }

    /**
     * Estado de la cita.
     */
    public function estadoCita()
    {
        return $this->belongsTo(
            EstadoCita::class,
            'id_estado_cita',
            'id_estado_cita'
        );
    }

    /**
     * Llamada relacionada con la cita.
     */
    public function llamada()
    {
        return $this->belongsTo(
            Llamada::class,
            'id_llamada',
            'id_llamada'
        );
    }
}