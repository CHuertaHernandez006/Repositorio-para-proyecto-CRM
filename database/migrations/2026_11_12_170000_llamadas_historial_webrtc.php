<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // La columna actual es INTEGER y tiene FK compuesta: conservar su tipo.
        DB::statement('ALTER TABLE public.llamadas ALTER COLUMN id_cliente DROP NOT NULL');
        Schema::table('llamadas', function (Blueprint $table) {
            $table->uuid('sesion_web')->nullable()->unique();
            $table->string('origen_registro', 20)->nullable();
            $table->timestampTz('fecha_contestacion')->nullable();
            $table->timestampTz('ultimo_evento_web')->nullable()->index();
        });

        // Estado específico: sin confirmación real del cierre (no falsear fecha_fin).
        DB::table('estados_llamada')->updateOrInsert(
            ['nombre' => 'Sin confirmar'],
            ['descripcion' => 'WebRTC perdió la conexión; cierre no confirmado por Asterisk.', 'activo' => true]
        );
    }

    public function down(): void
    {
        // No forzar NOT NULL si se registraron llamadas internas sin cliente.
        Schema::table('llamadas', function (Blueprint $table) {
            $table->dropUnique(['sesion_web']);
            $table->dropIndex(['ultimo_evento_web']);
            $table->dropColumn(['sesion_web', 'origen_registro', 'fecha_contestacion', 'ultimo_evento_web']);
        });
    }
};
