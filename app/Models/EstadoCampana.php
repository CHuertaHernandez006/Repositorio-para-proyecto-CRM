<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoCampana extends Model
{
    /*
    |--------------------------------------------------------------------------
    | TABLA Y LLAVE PRIMARIA
    |--------------------------------------------------------------------------
    */

    protected $table = 'estados_campana';

    protected $primaryKey = 'id_estado_campana';

    public $incrementing = true;

    protected $keyType = 'int';


    /*
    |--------------------------------------------------------------------------
    | TIMESTAMPS
    |--------------------------------------------------------------------------
    */

    public $timestamps = false;


    /*
    |--------------------------------------------------------------------------
    | CAMPOS ASIGNABLES
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',

        // Compatibilidad temporal con código anterior
        'estado',
    ];


    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'id_estado_campana' => 'integer',
            'activo' => 'boolean',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD: ESTADO
    |--------------------------------------------------------------------------
    |
    | Antes:
    | estado_campanas.estado
    |
    | Ahora:
    | estados_campana.activo
    |
    */

    public function getEstadoAttribute(): bool
    {
        return (bool) $this->activo;
    }

    public function setEstadoAttribute($value): void
    {
        $this->attributes['activo'] = (bool) $value;
    }


    /*
    |--------------------------------------------------------------------------
    | CAMPAÑAS
    |--------------------------------------------------------------------------
    */

    public function campanas(): HasMany
    {
        return $this->hasMany(
            Campana::class,
            'id_estado_campana',
            'id_estado_campana'
        );
    }
}