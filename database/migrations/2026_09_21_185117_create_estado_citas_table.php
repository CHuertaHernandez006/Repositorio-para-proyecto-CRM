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
        Schema::create('estado_citas', function (Blueprint $table) {

            $table->increments('id_estado_cita');

            $table->string('nombre', 100);

            $table->string('descripcion', 255)->nullable();

            $table->boolean('estado')->default(true);

            $table->date('fecha_fin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estado_citas');
    }
};