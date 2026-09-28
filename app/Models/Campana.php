<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Campana extends Model
{
    protected $table = 'campanas';

    protected $primaryKey = 'id_campana';

    protected $fillable = [
        'id_estado_campana',
        'nombre',
        'descripcion',
        'objetivo',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];


    /*
    |--------------------------------------------------------------------------
    | ESTADO
    |--------------------------------------------------------------------------
    */

    public function estadoCampana(): BelongsTo
    {
        return $this->belongsTo(
            EstadoCampana::class,
            'id_estado_campana',
            'id_estado_campana'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CLIENTES ACTIVOS DE LA CAMPAÑA
    |--------------------------------------------------------------------------
    */

    public function clientes(): BelongsToMany
    {
        return $this->belongsToMany(
            Cliente::class,
            'campana_cliente',
            'id_campana',
            'id_cliente',
            'id_campana',
            'id_cliente'
        )
        ->withPivot([
            'id_campana_cliente',
            'fecha_asignacion',
            'estado',
            'intentos',
        ])
        ->wherePivot('estado', true)
        ->withTimestamps();
    }


    /*
    |--------------------------------------------------------------------------
    | TODAS LAS RELACIONES
    |--------------------------------------------------------------------------
    |
    | Incluye relaciones activas e inactivas.
    | La usamos para reactivar clientes que estuvieron antes en la campaña.
    |
    */

    public function todosLosClientes(): BelongsToMany
    {
        return $this->belongsToMany(
            Cliente::class,
            'campana_cliente',
            'id_campana',
            'id_cliente',
            'id_campana',
            'id_cliente'
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