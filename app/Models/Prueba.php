<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prueba extends Model
{
    // Asegúrate de poner el nombre exacto de tu tabla en Postgres
    protected $table = 'pruebas'; 

    protected $fillable = ['correo', 'password'];
}