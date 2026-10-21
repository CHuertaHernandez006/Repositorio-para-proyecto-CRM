<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comi_cita_asignaciones', function (Blueprint $table) {
            $table->bigInteger('id_cita')->primary();
            $table->integer('id_asesor');
            $table->integer('id_gestor');
            $table->string('correo_destino', 254)->nullable();
            $table->timestampTz('correo_enviado_at')->nullable();
            $table->text('correo_error')->nullable();
            $table->timestampsTz();
            $table->foreign('id_cita')->references('id_cita')->on('comi_citas_agenda')->cascadeOnDelete();
            // El asesor pertenece a la empresa propietaria del equipo, no necesariamente a la de origen.
            $table->foreign('id_asesor')->references('id_usuario')->on('usuarios')->restrictOnDelete();
            $table->foreign('id_gestor')->references('id_usuario')->on('usuarios')->restrictOnDelete();
            $table->index('id_asesor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comi_cita_asignaciones');
    }
};
