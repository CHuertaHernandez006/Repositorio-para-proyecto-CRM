<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('importaciones_clientes', function (Blueprint $table) {
            $table->uuid('id_importacion')->primary();
            $table->integer('id_empresa');
            $table->integer('id_usuario');
            $table->string('estado', 20)->default('pendiente');
            $table->string('nombre_archivo', 180);
            $table->jsonb('opciones');
            $table->jsonb('datos')->nullable();
            $table->jsonb('resultado');
            $table->jsonb('resumen');
            $table->timestampTz('vence_at');
            $table->timestampsTz();
            $table->foreign('id_empresa')->references('id_empresa')->on('empresas')->restrictOnDelete();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->restrictOnDelete();
            $table->index(['id_empresa', 'id_usuario', 'created_at']);
            $table->index('vence_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importaciones_clientes');
    }
};
