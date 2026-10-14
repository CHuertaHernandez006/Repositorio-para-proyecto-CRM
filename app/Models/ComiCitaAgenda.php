<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComiCitaAgenda extends Model
{
    protected $table = 'comi_citas_agenda';

    protected $primaryKey = 'id_cita';

    protected $fillable = [
        'id_empresa',
        'id_proyecto',
        'id_agenda',
        'id_cliente',
        'id_conversacion',
        'id_solicitud',
        'fecha_hora_inicio',
        'estado',
        'motivo',
        'observaciones',
    ];

    protected $casts = [
        'fecha_hora_inicio' => 'datetime',
    ];

    public function proyecto()
    {
        return $this->belongsTo(
            ComiProyecto::class,
            'id_proyecto',
            'id_proyecto'
        );
    }

    public function agenda()
    {
        return $this->belongsTo(
            ComiAgenda::class,
            'id_agenda',
            'id_agenda'
        );
    }

    public function cliente()
    {
        return $this->belongsTo(
            Cliente::class,
            'id_cliente',
            'id_cliente'
        );
    }

    public function solicitudServicio()
    {
        return $this->belongsTo(
            ComiSolicitudServicio::class,
            'id_solicitud',
            'id_solicitud'
        );
    }
}