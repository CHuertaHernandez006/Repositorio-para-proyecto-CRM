<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';
    protected $primaryKey = 'id_empresa';

    protected $fillable = [
        'nombre',
        'slug',
        'estado'
    ];

    // Una empresa tiene muchos usuarios (Admin Cliente y Agentes)
    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_empresa', 'id_empresa');
    }
}
