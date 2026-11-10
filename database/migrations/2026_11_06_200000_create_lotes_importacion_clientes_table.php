<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lotes_importacion_clientes', function (Blueprint $table) {
            $table->uuid('id_lote')->primary();
            $table->integer('id_empresa');
            $table->integer('id_usuario');
            $table->string('nombre_archivo', 180);
            $table->unsignedInteger('filas_leidas');
            $table->unsignedInteger('importados');
            $table->unsignedInteger('duplicados');
            $table->unsignedInteger('errores');
            $table->timestampsTz();
            $table->unique(['id_empresa', 'id_lote'], 'uq_lote_empresa');
            $table->index(['id_empresa', 'created_at'], 'idx_lotes_empresa_fecha');
            $table->foreign('id_empresa')->references('id_empresa')->on('empresas')->restrictOnDelete();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->restrictOnDelete();
        });
        Schema::create('lote_importacion_clientes', function (Blueprint $table) {
            $table->uuid('id_lote');
            $table->integer('id_empresa');
            $table->integer('id_cliente');
            $table->unsignedInteger('fila_archivo');
            $table->boolean('excepcion')->default(false);
            $table->timestampsTz();
            $table->primary(['id_lote', 'id_cliente'], 'pk_lote_cliente');
            $table->unique(['id_empresa', 'id_cliente'], 'uq_origen_cliente');
            $table->foreign(['id_empresa', 'id_lote'], 'fk_miembro_lote_empresa')
                ->references(['id_empresa', 'id_lote'])->on('lotes_importacion_clientes')->cascadeOnDelete();
            $table->foreign(['id_empresa', 'id_cliente'], 'fk_miembro_cliente_empresa')
                ->references(['id_empresa', 'id_cliente'])->on('clientes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_importacion_clientes');
        Schema::dropIfExists('lotes_importacion_clientes');
    }
};
