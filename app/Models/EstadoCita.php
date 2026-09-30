<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoCita extends Model
{
    protected $table = 'estados_cita';

    protected $primaryKey = 'id_estado_cita';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',

        // Compatibilidad temporal con vistas/controladores antiguos.
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'id_estado_cita' => 'integer',
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

    public function citas(): HasMany
    {
        return $this->hasMany(
            Cita::class,
            'id_estado_cita',
            'id_estado_cita'
        );
    }
}
