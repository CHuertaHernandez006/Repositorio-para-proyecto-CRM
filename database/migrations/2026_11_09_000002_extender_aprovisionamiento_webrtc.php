<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // La tabla base fue creada en 000001. Esta migracion NO la reemplaza.
        Schema::table('webrtc_credenciales', function (Blueprint $table) {
            $table->string('estado', 30)->default('provisionada');
            $table->uuid('solicitud_uuid')->nullable();
            $table->timestamp('provisionada_en')->nullable();
            $table->string('ultimo_error', 255)->nullable();
        });

        Schema::create('webrtc_operaciones', function (Blueprint $table) {
            $table->id();
            $table->uuid('solicitud_uuid')->unique();
            $table->unsignedBigInteger('id_empresa');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->string('extension_asterisk', 20);
            $table->string('tipo', 20); // provisionar / revocar
            $table->string('estado', 20)->default('pendiente');
            $table->unsignedSmallInteger('intentos')->default(0);
            $table->string('ultimo_error', 255)->nullable();
            $table->timestamp('procesada_en')->nullable();
            $table->timestamps();
            $table->index(['extension_asterisk', 'estado', 'id']);
            $table->index(['id_empresa', 'id_usuario']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webrtc_operaciones');
        Schema::table('webrtc_credenciales', function (Blueprint $table) {
            $table->dropColumn(['estado', 'solicitud_uuid', 'provisionada_en', 'ultimo_error']);
        });
    }
};
