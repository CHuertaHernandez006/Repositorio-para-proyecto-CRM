<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | TABLA
    |--------------------------------------------------------------------------
    */

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    public $incrementing = true;

    protected $keyType = 'int';


    /*
    |--------------------------------------------------------------------------
    | CAMPOS ASIGNABLES
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        // Campos reales
        'id_empresa',
        'id_rol',
        'id_tipo_operario',

        'nombre',
        'apellido_paterno',
        'apellido_materno',

        'correo',
        'correo_verificado_at',
        'telefono',

        'password_hash',
        'remember_token',

        'activo',
        'ultimo_acceso',

        // Compatibilidad con código anterior
        'name',
        'email',
        'password',
        'estado',
    ];


    /*
    |--------------------------------------------------------------------------
    | CAMPOS OCULTOS
    |--------------------------------------------------------------------------
    */

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];


    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'id_usuario' => 'integer',
            'id_empresa' => 'integer',
            'id_rol' => 'integer',
            'id_tipo_operario' => 'integer',

            'activo' => 'boolean',

            'correo_verificado_at' => 'datetime',
            'ultimo_acceso' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | AUTENTICACIÓN
    |--------------------------------------------------------------------------
    |
    | Laravel debe usar password_hash en lugar de password.
    |
    */

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD: ID
    |--------------------------------------------------------------------------
    |
    | Permite seguir usando:
    |
    | $usuario->id
    |
    | aunque la PK real sea id_usuario.
    |
    */

    public function getIdAttribute(): ?int
    {
        return $this->id_usuario;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD: NAME
    |--------------------------------------------------------------------------
    */

    public function getNameAttribute(): string
    {
        return trim(
            collect([
                $this->nombre,
                $this->apellido_paterno,
                $this->apellido_materno,
            ])
                ->filter(fn ($valor) => $valor !== null && $valor !== '')
                ->implode(' ')
        );
    }

    public function setNameAttribute($value): void
    {
        $partes = preg_split(
            '/\s+/',
            trim((string) $value),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        if (!$partes) {
            return;
        }

        if (count($partes) === 1) {
            $this->attributes['nombre'] = $partes[0];
            $this->attributes['apellido_paterno'] = '';
            $this->attributes['apellido_materno'] = null;

            return;
        }

        if (count($partes) === 2) {
            $this->attributes['nombre'] = $partes[0];
            $this->attributes['apellido_paterno'] = $partes[1];
            $this->attributes['apellido_materno'] = null;

            return;
        }

        $apellidoMaterno = array_pop($partes);
        $apellidoPaterno = array_pop($partes);

        $this->attributes['nombre'] = implode(' ', $partes);
        $this->attributes['apellido_paterno'] = $apellidoPaterno;
        $this->attributes['apellido_materno'] = $apellidoMaterno;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD: EMAIL
    |--------------------------------------------------------------------------
    */

    public function getEmailAttribute(): ?string
    {
        return $this->correo;
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['correo'] = $value;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD: PASSWORD
    |--------------------------------------------------------------------------
    */

    public function getPasswordAttribute(): ?string
    {
        return $this->password_hash;
    }

    public function setPasswordAttribute($value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->attributes['password_hash'] =
            Hash::needsRehash($value)
                ? Hash::make($value)
                : $value;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD: ESTADO
    |--------------------------------------------------------------------------
    |
    | Antes: users.estado
    | Ahora: usuarios.activo
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
    | TIPO DE OPERARIO
    |--------------------------------------------------------------------------
    */

    public function tipoOperario(): BelongsTo
    {
        return $this->belongsTo(
            TipoOperario::class,
            'id_tipo_operario',
            'id_tipo_operario'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | OBJETIVOS
    |--------------------------------------------------------------------------
    */

    public function objetivos(): HasMany
    {
        return $this->hasMany(
            ObjetivoOperario::class,
            'id_usuario',
            'id_usuario'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | APROBACIONES
    |--------------------------------------------------------------------------
    */

    public function aprobaciones(): HasMany
    {
        return $this->hasMany(
            AprobacionUsuario::class,
            'id_usuario',
            'id_usuario'
        );
    }

    public function aprobacionActual(): HasOne
    {
        return $this->hasOne(
            AprobacionUsuario::class,
            'id_usuario',
            'id_usuario'
        )->ofMany(
            'id_aprobacion',
            'max'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD: ESTADO DE APROBACIÓN
    |--------------------------------------------------------------------------
    */

    public function getEstadoAprobacionAttribute(): ?string
    {
        return $this->obtenerAprobacionActual()?->estado;
    }

    public function getFechaSolicitudAttribute()
    {
        return $this->obtenerAprobacionActual()?->fecha_solicitud;
    }

    public function getFechaRevisionAttribute()
    {
        return $this->obtenerAprobacionActual()?->fecha_revision;
    }

    public function getMotivoRechazoAttribute(): ?string
    {
        return $this->obtenerAprobacionActual()?->motivo_rechazo;
    }

    public function getSolicitadoPorAttribute(): ?int
    {
        return $this->obtenerAprobacionActual()?->solicitado_por;
    }

    public function getRevisadoPorAttribute(): ?int
    {
        return $this->obtenerAprobacionActual()?->revisado_por;
    }


    /*
    |--------------------------------------------------------------------------
    | OBTENER APROBACIÓN ACTUAL
    |--------------------------------------------------------------------------
    */

    private function obtenerAprobacionActual(): ?AprobacionUsuario
    {
        if ($this->relationLoaded('aprobacionActual')) {
            return $this->getRelation('aprobacionActual');
        }

        return $this->aprobacionActual()->first();
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDACIONES DE OPERARIO
    |--------------------------------------------------------------------------
    */

    public function esOperario(): bool
    {
        return (int) $this->id_rol === 3;
    }

    public function aprobacionPendiente(): bool
    {
        return
            $this->esOperario() &&
            $this->estado_aprobacion === 'pendiente';
    }

    public function estaAprobado(): bool
    {
        return
            $this->esOperario() &&
            $this->estado_aprobacion === 'aprobado';
    }

    public function fueRechazado(): bool
    {
        return
            $this->esOperario() &&
            $this->estado_aprobacion === 'rechazado';
    }

    public function puedeOperar(): bool
    {
        return
            $this->estaAprobado() &&
            (bool) $this->activo;
    }

    public function tieneTipoOperario(): bool
    {
        return $this->id_tipo_operario !== null;
    }
}