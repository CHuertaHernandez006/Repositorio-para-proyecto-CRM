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
        Schema::create('clientes', function (Blueprint $table) {
        $table->id('id_cliente');
        
        // Llaves foráneas (las conectaremos con los catálogos después)
        $table->integer('id_tipo_cliente');
        $table->integer('id_estado_lead');
        
        $table->string('nombre', 100);
        $table->string('apellido_paterno', 100);
        $table->string('apellido_materno', 100)->nullable();
        $table->string('empresa', 150)->nullable();
        
        $table->string('telefono_principal', 20);
        $table->string('telefono_secundario', 20)->nullable();
        $table->string('correo', 150)->nullable();
        
        $table->string('pais', 100)->nullable();
        $table->string('estado', 100)->nullable(); // Se refiere al Estado de la República
        $table->string('ciudad', 100)->nullable();
        $table->string('fuente', 100)->nullable();
        
        $table->date('fecha_registro')->useCurrent();
        $table->date('fecha_actualizacion')->useCurrent();
        
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
