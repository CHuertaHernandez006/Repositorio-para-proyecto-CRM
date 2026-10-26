<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comercial_fichas_prospecto', function (Blueprint $table) {
            $table->bigInteger('id_prospecto')->primary();
            $table->integer('id_editor');
            $table->string('giro', 150)->nullable();
            $table->string('numero_colaboradores', 100)->nullable();
            $table->string('canal_principal', 100)->nullable();
            $table->string('volumen_estimado', 100)->nullable();
            $table->string('presupuesto_aproximado', 100)->nullable();
            $table->text('objetivo_principal')->nullable();
            $table->timestampsTz();
            $table->foreign('id_prospecto')->references('id')->on('comercial_prospectos')->cascadeOnDelete();
            $table->foreign('id_editor')->references('id_usuario')->on('usuarios')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comercial_fichas_prospecto');
    }
};
