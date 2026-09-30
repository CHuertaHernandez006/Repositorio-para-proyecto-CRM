<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Cliente extends Model
{
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | TABLA Y LLAVE PRIMARIA
    |--------------------------------------------------------------------------
    */

    protected $table = 'clientes';

    protected $primaryKey = 'id_cliente';

    public $incrementing = true;

    protected $keyType = 'int';


    /*
    |--------------------------------------------------------------------------
    | TIMESTAMPS
    |--------------------------------------------------------------------------
    |
    | La nueva base vuelve a utilizar los nombres estándar de Laravel:
    |
    | created_at
    | updated_at
    | deleted_at
    |
    */

    public $timestamps = true;


    /*
    |--------------------------------------------------------------------------
    | CAMPOS ASIGNABLES
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'id_empresa',
        'id_tipo_cliente',
        'id_estado_lead',
        'id_fuente',

        'nombre',
        'apellido_paterno',
        'apellido_materno',

        'organizacion',

        'telefono_principal',
        'telefono_secundario',

        'correo',

        'pais',
        'estado_region',
        'ciudad',

        /*
        |--------------------------------------------------------------------------
        | COMPATIBILIDAD CON EL CÓDIGO ANTERIOR
        |--------------------------------------------------------------------------
        |
        | Estos NO existen físicamente en PostgreSQL.
        | Los accessors/mutators de abajo los traducen.
        |
        */

        'empresa',
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
            'id_cliente' => 'integer',
            'id_empresa' => 'integer',
            'id_tipo_cliente' => 'integer',
            'id_estado_lead' => 'integer',
            'id_fuente' => 'integer',

            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | NOMBRE COMPLETO
    |--------------------------------------------------------------------------
    */

    public function getNombreCompletoAttribute(): string
    {
        return trim(
            collect([
                $this->nombre,
                $this->apellido_paterno,
                $this->apellido_materno,
            ])
                ->filter(
                    fn ($valor) =>
                    $valor !== null &&
                    $valor !== ''
                )
                ->implode(' ')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD: EMPRESA
    |--------------------------------------------------------------------------
    |
    | Antes:
    |
    | clientes.empresa
    |
    | Ahora:
    |
    | clientes.organizacion
    |
    */

    public function getEmpresaAttribute(): ?string
    {
        return $this->organizacion;
    }

    public function setEmpresaAttribute($value): void
    {
        $this->attributes['organizacion'] = $value;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD: ESTADO
    |--------------------------------------------------------------------------
    |
    | Antes:
    |
    | clientes.estado
    |
    | Ahora:
    |
    | clientes.estado_region
    |
    */

    public function getEstadoAttribute(): ?string
    {
        return $this->estado_region;
    }

    public function setEstadoAttribute($value): void
    {
        $this->attributes['estado_region'] = $value;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD: FECHA DE REGISTRO
    |--------------------------------------------------------------------------
    |
    | Antes:
    |
    | fecha_registro
    |
    | Ahora:
    |
    | created_at
    |
    */

    public function getFechaRegistroAttribute()
    {
        return $this->created_at;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD: FECHA DE ACTUALIZACIÓN
    |--------------------------------------------------------------------------
    |
    | Antes:
    |
    | fecha_actualizacion
    |
    | Ahora:
    |
    | updated_at
    |
    */

    public function getFechaActualizacionAttribute()
    {
        return $this->updated_at;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD: FUENTE
    |--------------------------------------------------------------------------
    |
    | Antes clientes guardaba directamente un texto:
    |
    | fuente = "Comi"
    |
    | Ahora guarda:
    |
    | id_fuente
    |
    | y el nombre vive en fuentes_lead.
    |
    | Esto permite que las vistas que todavía usen:
    |
    | $cliente->fuente
    |
    | sigan mostrando el nombre.
    |
    */

    public function getFuenteAttribute(): ?string
    {
        if (!$this->id_fuente) {
            return null;
        }

        return DB::table('fuentes_lead')
            ->where(
                'id_fuente',
                $this->id_fuente
            )
            ->value('nombre');
    }


    /*
    |--------------------------------------------------------------------------
    | EMPRESA / TENANT
    |--------------------------------------------------------------------------
    |
    | OJO:
    |
    | $cliente->empresa
    |
    | continúa significando la organización textual del prospecto.
    |
    | Para acceder al tenant real del CRM usamos:
    |
    | $cliente->empresaCrm
    |
    */

    public function empresaCrm(): BelongsTo
    {
        return $this->belongsTo(
            Empresa::class,
            'id_empresa',
            'id_empresa'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CAMPAÑAS
    |--------------------------------------------------------------------------
    */

    public function campanas(): BelongsToMany
    {
        return $this->belongsToMany(
            Campana::class,
            'campana_cliente',
            'id_cliente',
            'id_campana',
            'id_cliente',
            'id_campana'
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