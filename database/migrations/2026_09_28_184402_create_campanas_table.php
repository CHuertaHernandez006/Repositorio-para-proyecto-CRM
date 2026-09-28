<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campanas', function (Blueprint $table) {

            $table->id('id_campana');

            $table->foreignId(
                'id_estado_campana'
            )
                ->constrained(
                    'estado_campanas',
                    'id_estado_campana'
                )
                ->restrictOnDelete();

            $table->string(
                'nombre',
                150
            );

            $table->text(
                'descripcion'
            )->nullable();

            $table->text(
                'objetivo'
            )->nullable();

            $table->date(
                'fecha_inicio'
            );

            $table->date(
                'fecha_fin'
            );

            $table->timestamps();

            $table->index(
                'id_estado_campana'
            );

            $table->index([
                'fecha_inicio',
                'fecha_fin',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'campanas'
        );
    }
};