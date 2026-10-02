<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoLlamada extends Model
{
    protected $table = 'estados_llamada';
    protected $primaryKey = 'id_estado_llamada';

    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',

        // Compatibilidad temporal con código viejo.
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'id_estado_llamada' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function getEstadoAttribute(): bool
    {
        return (bool) $this->activo;
    }

    public function setEstadoAttribute($value): void
    {
        $this->attributes['activo'] = (bool) $value;
    }

    public function llamadas(): HasMany
    {
        return $this->hasMany(
            Llamada::class,
            'id_estado_llamada',
            'id_estado_llamada'
        );
    }
}
