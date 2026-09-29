<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;


    /*
    |--------------------------------------------------------------------------
    | CAMPOS ASIGNABLES
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'name',
        'email',
        'password',

        'id_empresa',
        'id_rol',

        /*
        |--------------------------------------------------------------------------
        | TIPO DE OPERARIO
        |--------------------------------------------------------------------------
        */

        'id_tipo_operario',

        /*
        |--------------------------------------------------------------------------
        | ESTADO GENERAL
        |--------------------------------------------------------------------------
        */

        'estado',

        /*
        |--------------------------------------------------------------------------
        | APROBACIÓN DE OPERARIOS
        |--------------------------------------------------------------------------
        */

        'estado_aprobacion',
        'solicitado_por',
        'fecha_solicitud',
        'revisado_por',
        'fecha_revision',
        'motivo_rechazo',
    ];


    /*
    |--------------------------------------------------------------------------
    | CAMPOS OCULTOS
    |--------------------------------------------------------------------------
    */

    protected $hidden = [
        'password',
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
            'email_verified_at' => 'datetime',

            'password' => 'hashed',

            'estado' => 'boolean',

            'id_empresa' => 'integer',
            'id_rol' => 'integer',
            'id_tipo_operario' => 'integer',

            'fecha_solicitud' => 'datetime',
            'fecha_revision' => 'datetime',
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
    | TIPO DE OPERARIO
    |--------------------------------------------------------------------------
    |
    | Permite utilizar:
    |
    | $usuario->tipoOperario
    | $usuario->tipoOperario->nombre
    |
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
    | OBJETIVOS DEL OPERARIO
    |--------------------------------------------------------------------------
    */

    public function objetivos(): HasMany
    {
        return $this->hasMany(
            ObjetivoOperario::class,
            'id_usuario',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | USUARIO QUE SOLICITÓ EL ALTA DEL OPERARIO
    |--------------------------------------------------------------------------
    */

    public function solicitadoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'solicitado_por',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN QUE REVISÓ LA SOLICITUD
    |--------------------------------------------------------------------------
    */

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'revisado_por',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | COMPROBAR SI ES OPERARIO
    |--------------------------------------------------------------------------
    */

    public function esOperario(): bool
    {
        return (int) $this->id_rol === 3;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPROBAR SI ESTÁ PENDIENTE
    |--------------------------------------------------------------------------
    */

    public function aprobacionPendiente(): bool
    {
        return
            $this->esOperario() &&
            $this->estado_aprobacion === 'pendiente';
    }


    /*
    |--------------------------------------------------------------------------
    | COMPROBAR SI ESTÁ APROBADO
    |--------------------------------------------------------------------------
    */

    public function estaAprobado(): bool
    {
        /*
        |--------------------------------------------------------------------------
        | NULL sigue aceptándose temporalmente para los operarios antiguos.
        |--------------------------------------------------------------------------
        */

        return
            $this->esOperario() &&
            (
                $this->estado_aprobacion === 'aprobado' ||
                $this->estado_aprobacion === null
            );
    }


    /*
    |--------------------------------------------------------------------------
    | COMPROBAR SI FUE RECHAZADO
    |--------------------------------------------------------------------------
    */

    public function fueRechazado(): bool
    {
        return
            $this->esOperario() &&
            $this->estado_aprobacion === 'rechazado';
    }


    /*
    |--------------------------------------------------------------------------
    | COMPROBAR SI PUEDE OPERAR
    |--------------------------------------------------------------------------
    */

    public function puedeOperar(): bool
    {
        return
            $this->estaAprobado() &&
            $this->estado === true;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPROBAR SI TIENE TIPO DE OPERARIO ASIGNADO
    |--------------------------------------------------------------------------
    */

    public function tieneTipoOperario(): bool
    {
        return
            $this->id_tipo_operario !== null;
    }
}