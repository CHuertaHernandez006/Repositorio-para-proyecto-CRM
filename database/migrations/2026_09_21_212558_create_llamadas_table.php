<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crear tabla de llamadas.
     */
    public function up(): void
    {
        Schema::create('llamadas', function (Blueprint $table) {

            $table->id('id_llamada');

            // Cliente al que se realiza la llamada
            $table->unsignedBigInteger('id_cliente');

            // Operario que realiza la llamada
            $table->unsignedBigInteger('id_usuario');

            // Inicio y finalización real de la llamada
            $table->dateTime('fecha_hora_inicio');

            $table->dateTime('fecha_hora_fin')->nullable();

            // Resultado de la llamada
            $table->string('resultado', 100)->nullable();

            // Observaciones de la llamada
            $table->text('observaciones')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            $table->foreign('id_cliente')
                ->references('id_cliente')
                ->on('clientes')
                ->onDelete('cascade');

            $table->foreign('id_usuario')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Eliminar tabla de llamadas.
     */
    public function down(): void
    {
        Schema::dropIfExists('llamadas');
    }
};