<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_campanas', function (Blueprint $table) {

            $table->id('id_estado_campana');

            $table->string('nombre', 100);

            $table->string(
                'descripcion',
                255
            )->nullable();

            $table->boolean('estado')
                ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'estado_campanas'
        );
    }
};