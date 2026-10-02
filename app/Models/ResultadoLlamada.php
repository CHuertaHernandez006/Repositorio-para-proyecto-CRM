<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResultadoLlamada extends Model
{
    protected $table = 'resultados_llamada';
    protected $primaryKey = 'id_resultado';

    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    public const CATEGORIA_CONTACTO = 'Contacto';
    public const CATEGORIA_COMERCIAL = 'Comercial';
    public const CATEGORIA_CONVERSION = 'Conversión';

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria',
        'activo',

        // Compatibilidad temporal con código viejo.
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'id_resultado' => 'integer',
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
            'id_resultado',
            'id_resultado'
        );
    }
}
