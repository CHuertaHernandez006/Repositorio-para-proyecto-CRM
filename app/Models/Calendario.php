<?php

namespace App\Models;

/*
|--------------------------------------------------------------------------
| CALENDARIO
|--------------------------------------------------------------------------
|
| El calendario utiliza exactamente la misma tabla, campos y relaciones
| que el modelo Cita.
|
| Al heredar de Cita evitamos mantener dos modelos duplicados y que uno
| quede desactualizado cuando cambie la estructura de la tabla citas.
|
*/

class Calendario extends Cita
{
    //
}