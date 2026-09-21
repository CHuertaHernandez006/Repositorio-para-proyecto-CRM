<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoCita extends Model
{
    protected $table = 'estado_citas';
    protected $primaryKey = 'id_estado_cita';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'fecha_fin',
    ];
}