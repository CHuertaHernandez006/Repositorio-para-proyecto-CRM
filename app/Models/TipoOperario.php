<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoOperario extends Model
{
    /*
    |--------------------------------------------------------------------------
    | TABLA Y LLAVE PRIMARIA
    |--------------------------------------------------------------------------
    */

    protected $table = 'tipos_operario';

    protected $primaryKey = 'id_tipo_operario';

    public $incrementing = true;

    protected $keyType = 'int';


    /*
    |--------------------------------------------------------------------------
    | TIMESTAMPS
    |--------------------------------------------------------------------------
    |
    | La tabla del nuevo esquema no tiene created_at ni updated_at.
    |
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

        // Compatibilidad temporal con el código anterior
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
            'id_tipo_operario' => 'integer',
            'activo' => 'boolean',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD: ESTADO
    |--------------------------------------------------------------------------
    |
    | Antes:
    |
    | tipo_operarios.estado
    |
    | Ahora:
    |
    | tipos_operario.activo
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
    | OPERARIOS DE ESTE TIPO
    |--------------------------------------------------------------------------
    */

    public function operarios(): HasMany
    {
        return $this->hasMany(
            User::class,
            'id_tipo_operario',
            'id_tipo_operario'
        );
    }
    /** Descripción coherente con el nombre del perfil, sin modificar registros. */
    public function getDescripcionAttribute($value): ?string
    {
        return match (\App\Support\PerfilOperario::clave($this)) {
            'asignacion' => 'Operario que trabaja con clientes asignados por el administrador.',
            'prospeccion' => 'Operario que puede buscar clientes de su empresa y registrar nuevos prospectos.',
            default => $value,
        };
    }
}