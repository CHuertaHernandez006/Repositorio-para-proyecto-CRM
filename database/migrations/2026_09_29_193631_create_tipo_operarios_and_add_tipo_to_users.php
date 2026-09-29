<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | CATÁLOGO DE TIPOS DE OPERARIO
        |--------------------------------------------------------------------------
        */

        Schema::create('tipo_operarios', function (Blueprint $table) {

            $table->id('id_tipo_operario');

            $table->string(
                'nombre',
                100
            );

            $table->string(
                'descripcion',
                255
            )->nullable();

            $table->boolean(
                'estado'
            )->default(true);

            $table->timestamps();
        });


        /*
        |--------------------------------------------------------------------------
        | RELACIÓN CON USERS
        |--------------------------------------------------------------------------
        |
        | Lo dejamos nullable temporalmente porque ya existen operarios
        | registrados antes de implementar esta clasificación.
        |--------------------------------------------------------------------------
        */

        Schema::table('users', function (Blueprint $table) {

            $table
                ->foreignId(
                    'id_tipo_operario'
                )
                ->nullable()
                ->constrained(
                    'tipo_operarios',
                    'id_tipo_operario'
                )
                ->restrictOnDelete();
        });


        /*
        |--------------------------------------------------------------------------
        | TIPOS INICIALES
        |--------------------------------------------------------------------------
        |
        | Estos nombres pueden cambiar después si tu jefe utiliza
        | otra terminología. Los IDs permanecen iguales.
        |--------------------------------------------------------------------------
        */

        DB::table('tipo_operarios')->insert([
            [
                'nombre' =>
                    'Prospección',

                'descripcion' =>
                    'Operario que puede buscar prospectos o clientes para gestionar.',

                'estado' =>
                    true,

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ],

            [
                'nombre' =>
                    'Asignación',

                'descripcion' =>
                    'Operario que trabaja con clientes asignados por el administrador.',

                'estado' =>
                    true,

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ],
        ]);
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | QUITAR RELACIÓN DE USERS
        |--------------------------------------------------------------------------
        */

        Schema::table('users', function (Blueprint $table) {

            $table->dropForeign([
                'id_tipo_operario',
            ]);

            $table->dropColumn(
                'id_tipo_operario'
            );
        });


        /*
        |--------------------------------------------------------------------------
        | ELIMINAR CATÁLOGO
        |--------------------------------------------------------------------------
        */

        Schema::dropIfExists(
            'tipo_operarios'
        );
    }
};