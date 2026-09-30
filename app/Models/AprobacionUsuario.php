<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AprobacionUsuario extends Model
{
    /*
    |--------------------------------------------------------------------------
    | TABLA
    |--------------------------------------------------------------------------
    */

    protected $table = 'aprobaciones_usuario';

    protected $primaryKey = 'id_aprobacion';

    public $incrementing = true;

    protected $keyType = 'int';

    /*
    |--------------------------------------------------------------------------
    | LA TABLA NO TIENE created_at / updated_at
    |--------------------------------------------------------------------------
    */

    public $timestamps = false;


    /*
    |--------------------------------------------------------------------------
    | CAMPOS ASIGNABLES
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'id_usuario',
        'estado',

        'solicitado_por',
        'fecha_solicitud',

        'revisado_por',
        'fecha_revision',

        'motivo_rechazo',
    ];


    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'id_aprobacion' => 'integer',
            'id_usuario' => 'integer',

            'solicitado_por' => 'integer',
            'revisado_por' => 'integer',

            'fecha_solicitud' => 'datetime',
            'fecha_revision' => 'datetime',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | OPERARIO
    |--------------------------------------------------------------------------
    */

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_usuario',
            'id_usuario'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN QUE SOLICITÓ
    |--------------------------------------------------------------------------
    */

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'solicitado_por',
            'id_usuario'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN QUE REVISÓ
    |--------------------------------------------------------------------------
    */

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'revisado_por',
            'id_usuario'
        );
    }
}