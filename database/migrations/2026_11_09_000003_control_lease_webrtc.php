<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('webrtc_operaciones', function (Blueprint $table) {
            $table->uuid('lease_token')->nullable();
            $table->timestamp('iniciada_en')->nullable();
        });

        // Una credencial creada manualmente antes del helper no equivale a
        // un cambio confirmado por Asterisk. Requiere renovar desde el panel.
        DB::table('webrtc_credenciales')
            ->whereNull('solicitud_uuid')
            ->update(['estado' => 'pendiente', 'provisionada_en' => null]);
    }

    public function down(): void
    {
        Schema::table('webrtc_operaciones', function (Blueprint $table) {
            $table->dropColumn(['lease_token', 'iniciada_en']);
        });
        // No revertir 'estado': implicaría inventar una confirmación de Asterisk.
    }
};
