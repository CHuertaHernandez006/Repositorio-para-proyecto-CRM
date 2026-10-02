<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Llamada extends Model
{
    protected $table = 'llamadas';
    protected $primaryKey = 'id_llamada';

    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    public const TIPO_ENTRANTE = 'entrante';
    public const TIPO_SALIENTE = 'saliente';

    protected $fillable = [
        'id_empresa',
        'id_cliente',
        'id_usuario',
        'id_campana',
        'id_resultado',
        'id_estado_llamada',
        'tipo_llamada',
        'fecha_inicio',
        'fecha_fin',
        'duracion',
        'numero_origen',
        'numero_destino',
        'identificador_asterisk',
        'grabacion_url',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'id_llamada' => 'integer',
            'id_empresa' => 'integer',
            'id_cliente' => 'integer',
            'id_usuario' => 'integer',
            'id_campana' => 'integer',
            'id_resultado' => 'integer',
            'id_estado_llamada' => 'integer',
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'duracion' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function campana(): BelongsTo
    {
        return $this->belongsTo(Campana::class, 'id_campana', 'id_campana');
    }

    public function estadoLlamada(): BelongsTo
    {
        return $this->belongsTo(
            EstadoLlamada::class,
            'id_estado_llamada',
            'id_estado_llamada'
        );
    }

    public function resultado(): BelongsTo
    {
        return $this->belongsTo(
            ResultadoLlamada::class,
            'id_resultado',
            'id_resultado'
        );
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'id_llamada', 'id_llamada');
    }
}
