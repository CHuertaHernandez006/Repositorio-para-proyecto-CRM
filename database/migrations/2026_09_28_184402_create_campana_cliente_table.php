<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'campana_cliente',
            function (Blueprint $table) {

                $table->id(
                    'id_campana_cliente'
                );

                $table->foreignId(
                    'id_campana'
                )
                    ->constrained(
                        'campanas',
                        'id_campana'
                    )
                    ->cascadeOnDelete();

                $table->foreignId(
                    'id_cliente'
                )
                    ->constrained(
                        'clientes',
                        'id_cliente'
                    )
                    ->cascadeOnDelete();

                $table->timestamp(
                    'fecha_asignacion'
                )->useCurrent();

                $table->boolean(
                    'estado'
                )->default(true);

                $table->unsignedInteger(
                    'intentos'
                )->default(0);

                $table->timestamps();

                /*
                 * Un cliente no debe aparecer
                 * dos veces en la misma campaña.
                 */
                $table->unique([
                    'id_campana',
                    'id_cliente',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'campana_cliente'
        );
    }
};