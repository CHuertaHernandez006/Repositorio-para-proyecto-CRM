<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cita extends Model
{
    protected $table = 'citas';

    protected $primaryKey = 'id_cita';

    public $incrementing = true;

    protected $keyType = 'int';

    /*
    |--------------------------------------------------------------------------
    | TIMESTAMPS
    |--------------------------------------------------------------------------
    |
    | En la nueva base PostgreSQL la tabla citas SÍ utiliza:
    | created_at y updated_at.
    |
    */

    public $timestamps = true;

    protected $fillable = [
        'id_empresa',
        'id_cliente',
        'id_usuario',
        'id_llamada',
        'id_estado_cita',
        'fecha_hora_inicio',
        'fecha_hora_fin',
        'motivo',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'id_cita' => 'integer',
            'id_empresa' => 'integer',
            'id_cliente' => 'integer',
            'id_usuario' => 'integer',
            'id_llamada' => 'integer',
            'id_estado_cita' => 'integer',

            'fecha_hora_inicio' => 'datetime',
            'fecha_hora_fin' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(
            Empresa::class,
            'id_empresa',
            'id_empresa'
        );
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(
            Cliente::class,
            'id_cliente',
            'id_cliente'
        );
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_usuario',
            'id_usuario'
        );
    }

    public function estadoCita(): BelongsTo
    {
        return $this->belongsTo(
            EstadoCita::class,
            'id_estado_cita',
            'id_estado_cita'
        );
    }

    public function llamada(): BelongsTo
    {
        return $this->belongsTo(
            Llamada::class,
            'id_llamada',
            'id_llamada'
        );
    }
}
