<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objetivos_operarios', function (Blueprint $table) {
            $table->id('id_objetivo');

            $table->foreignId('id_usuario')
                ->constrained('users', 'id')
                ->cascadeOnDelete();

            $table->unsignedInteger('objetivo_llamadas');

            $table->enum('periodo', [
                'semanal',
                'mensual',
                'personalizado'
            ])->default('semanal');

            $table->date('fecha_inicio');
            $table->date('fecha_fin');

            $table->enum('estado', [
                'pendiente',
                'en_progreso',
                'cumplido',
                'vencido'
            ])->default('pendiente');

            $table->timestamps();

            $table->index([
                'id_usuario',
                'estado'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objetivos_operarios');
    }
};