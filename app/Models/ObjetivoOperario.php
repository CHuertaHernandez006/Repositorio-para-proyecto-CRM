<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ObjetivoOperario extends Model
{
    protected $table = 'objetivos_operarios';

    protected $fillable = [
        'id_usuario',
        'objetivo_llamadas',
        'periodo',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'objetivo_llamadas' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function operario()
    {
        return $this->belongsTo(
            User::class,
            'id_usuario'
        );
    }
}