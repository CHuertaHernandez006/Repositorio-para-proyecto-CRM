<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoCita extends Model
{
    protected $table = 'estado_citas';

    protected $primaryKey = 'id_estado_cita';

    /*
    |--------------------------------------------------------------------------
    | La tabla no utiliza created_at / updated_at
    |--------------------------------------------------------------------------
    */
    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Campos que pueden asignarse masivamente
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'fecha_fin',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de tipos
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'estado' => 'boolean',
        'fecha_fin' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Citas que utilizan este estado
    |--------------------------------------------------------------------------
    */
    public function citas()
    {
        return $this->hasMany(
            Cita::class,
            'id_estado_cita',
            'id_estado_cita'
        );
    }
}