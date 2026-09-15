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
        Schema::create('roles', function (Blueprint $table) {
            // SERIAL PRIMARY KEY
            $table->id('id_rol'); 
            
            // nombre VARCHAR(50) NOT NULL
            $table->string('nombre', 50);
            
            // descripcion VARCHAR(255) (Al no ser NOT NULL en el SQL, le ponemos nullable)
            $table->string('descripcion', 255)->nullable();
            
            // estado BOOLEAN NOT NULL DEFAULT TRUE
            $table->boolean('estado')->default(true);
            
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
