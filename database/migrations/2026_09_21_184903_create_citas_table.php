<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {

            $table->increments('id_cita');

            $table->unsignedInteger('id_cliente');

            $table->unsignedBigInteger('id_usuario');

            $table->unsignedInteger('id_llamada')->nullable();

            $table->unsignedInteger('id_estado_cita');

            $table->dateTime('fecha_hora_inicio');

            $table->dateTime('fecha_hora_fin')->nullable();

            $table->string('motivo', 255)->nullable();

            $table->text('observaciones')->nullable();


            // Relación con clientes
            $table->foreign('id_cliente')
                ->references('id_cliente')
                ->on('clientes')
                ->onDelete('cascade');


            // Relación con usuarios / operarios
            $table->foreign('id_usuario')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');


            // Relación con llamadas
            $table->foreign('id_llamada')
                ->references('id_llamada')
                ->on('llamadas')
                ->onDelete('set null');


            // Relación con estados de cita
            $table->foreign('id_estado_cita')
                ->references('id_estado_cita')
                ->on('estado_citas')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};