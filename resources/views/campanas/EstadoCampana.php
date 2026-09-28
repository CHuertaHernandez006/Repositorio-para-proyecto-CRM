<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoCampana extends Model
{
    protected $table = 'estado_campanas';

    protected $primaryKey = 'id_estado_campana';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function campanas(): HasMany
    {
        return $this->hasMany(
            Campana::class,
            'id_estado_campana',
            'id_estado_campana'
        );
    }
}