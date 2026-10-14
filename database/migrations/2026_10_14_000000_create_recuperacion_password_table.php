<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recuperacion_password', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->unsignedInteger('id_usuario');

            $table
                ->string('token_hash', 64)
                ->unique();

            $table->timestampTz('expires_at');

            $table
                ->timestampTz('used_at')
                ->nullable();

            $table->timestampsTz();

            $table
                ->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnDelete();

            $table->index([
                'id_usuario',
                'expires_at',
            ]);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'recuperacion_password'
        );
    }
};