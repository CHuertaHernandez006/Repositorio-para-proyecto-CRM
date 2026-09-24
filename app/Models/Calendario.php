<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendario extends Model
{
    // Apunta a la misma tabla de citas de la base de datos
    protected $table = 'citas';

    protected $primaryKey = 'id_cita';

    public $timestamps = false;

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

    protected $casts = [
        'fecha_hora_inicio' => 'datetime',
        'fecha_hora_fin' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(
            Cliente::class,
            'id_cliente',
            'id_cliente'
        );
    }

    public function usuario()
    {
        return $this->belongsTo(
            User::class,
            'id_usuario',
            'id'
        );
    }

    public function estadoCita()
    {
        return $this->belongsTo(
            EstadoCita::class,
            'id_estado_cita',
            'id_estado_cita'
        );
    }

    public function llamada()
    {
        return $this->belongsTo(
            Llamada::class,
            'id_llamada',
            'id_llamada'
        );
    }
}