<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComiProyecto extends Model
{
    protected $table = 'comi_proyectos';

    protected $primaryKey = 'id_proyecto';

    protected $fillable = [
        'id_empresa',
        'slug',
        'nombre',
        'tipo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function agendas()
    {
        return $this->hasMany(
            ComiAgenda::class,
            'id_proyecto',
            'id_proyecto'
        );
    }

    public function citas()
    {
        return $this->hasMany(
            ComiCitaAgenda::class,
            'id_proyecto',
            'id_proyecto'
        );
    }

    public function solicitudesServicio()
    {
        return $this->hasMany(
            ComiSolicitudServicio::class,
            'id_proyecto',
            'id_proyecto'
        );
    }
}