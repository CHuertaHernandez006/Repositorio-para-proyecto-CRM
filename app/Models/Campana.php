<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campana extends Model
{
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | TABLA Y LLAVE PRIMARIA
    |--------------------------------------------------------------------------
    */

    protected $table = 'campanas';

    protected $primaryKey = 'id_campana';

    public $incrementing = true;

    protected $keyType = 'int';


    /*
    |--------------------------------------------------------------------------
    | CAMPOS ASIGNABLES
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'id_empresa',
        'id_estado_campana',
        'nombre',
        'descripcion',
        'objetivo',
        'fecha_inicio',
        'fecha_fin',
    ];


    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'id_campana' => 'integer',
            'id_empresa' => 'integer',
            'id_estado_campana' => 'integer',

            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',

            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | EMPRESA
    |--------------------------------------------------------------------------
    */

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(
            Empresa::class,
            'id_empresa',
            'id_empresa'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ESTADO DE CAMPAÑA
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
    | CLIENTES ACTIVOS
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
                'id_empresa',
                'activo',
                'intentos',
            ])
            ->wherePivot(
                'activo',
                true
            )
            ->withTimestamps();
    }


    /*
    |--------------------------------------------------------------------------
    | TODOS LOS CLIENTES
    |--------------------------------------------------------------------------
    |
    | Incluye asignaciones activas e inactivas.
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
                'id_empresa',
                'activo',
                'intentos',
            ])
            ->withTimestamps();
    }
}