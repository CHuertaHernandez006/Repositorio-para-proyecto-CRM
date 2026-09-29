<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoOperario extends Model
{
    protected $table = 'tipo_operarios';

    protected $primaryKey = 'id_tipo_operario';

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
    | OPERARIOS DE ESTE TIPO
    |--------------------------------------------------------------------------
    */

    public function operarios(): HasMany
    {
        return $this->hasMany(
            User::class,
            'id_tipo_operario',
            'id_tipo_operario'
        );
    }
}