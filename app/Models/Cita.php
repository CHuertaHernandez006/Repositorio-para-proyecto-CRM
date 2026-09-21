<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
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

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function estadoCita()
    {
        return $this->belongsTo(EstadoCita::class, 'id_estado_cita', 'id_estado_cita');
    }
}