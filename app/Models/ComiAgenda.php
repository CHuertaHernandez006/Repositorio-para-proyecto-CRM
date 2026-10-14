<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComiAgenda extends Model
{
    protected $table = 'comi_agendas';

    protected $primaryKey = 'id_agenda';

    protected $fillable = [
        'id_empresa',
        'id_proyecto',
        'slug',
        'nombre',
        'zona_horaria',
        'duracion_minutos',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'duracion_minutos' => 'integer',
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
            'id_agenda',
            'id_agenda'
        );
    }
}