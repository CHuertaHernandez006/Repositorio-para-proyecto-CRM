<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComiSolicitudServicio extends Model
{
    protected $table = 'comi_solicitudes_servicio';

    protected $primaryKey = 'id_solicitud';

    protected $fillable = [
        'id_empresa',
        'id_proyecto',
        'conversation_key',
        'nombre_contacto',
        'telefono',
        'equipo',
        'descripcion_problema',
        'horario_preferido',
        'estado',
        'notas',
    ];

    public function proyecto()
    {
        return $this->belongsTo(
            ComiProyecto::class,
            'id_proyecto',
            'id_proyecto'
        );
    }

    public function citas()
    {
        return $this->hasMany(
            ComiCitaAgenda::class,
            'id_solicitud',
            'id_solicitud'
        );
    }
}