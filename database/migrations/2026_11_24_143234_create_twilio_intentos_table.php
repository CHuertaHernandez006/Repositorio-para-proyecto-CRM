<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('twilio_intentos', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->integer('id_empresa');
            $table->integer('id_usuario');
            $table->integer('id_cliente');

            $table->string('destino_e164', 16);
            $table->string('estado', 30)->default('preparado');

            $table->timestampTz('expira_en');
            $table->timestampTz('fecha_contestacion')->nullable();
            $table->timestampTz('fecha_fin')->nullable();

            $table->string('twilio_parent_sid', 34)->nullable();
            $table->string('twilio_child_sid', 34)->nullable();

            $table->bigInteger('id_llamada')->nullable();

            $table->timestampsTz();

            // El usuario debe pertenecer a la misma empresa.
            $table->foreign(
                ['id_empresa', 'id_usuario'],
                'twilio_intentos_empresa_usuario_fk'
            )->references(
                ['id_empresa', 'id_usuario']
            )->on('usuarios')->restrictOnDelete();

            // El cliente debe pertenecer a la misma empresa.
            $table->foreign(
                ['id_empresa', 'id_cliente'],
                'twilio_intentos_empresa_cliente_fk'
            )->references(
                ['id_empresa', 'id_cliente']
            )->on('clientes')->restrictOnDelete();

            // Relación opcional con el historial de llamadas del CRM.
            $table->foreign(
                'id_llamada',
                'twilio_intentos_llamada_fk'
            )->references(
                'id_llamada'
            )->on('llamadas')->nullOnDelete();

            $table->unique(
                'twilio_parent_sid',
                'twilio_intentos_parent_sid_uq'
            );

            $table->unique(
                'twilio_child_sid',
                'twilio_intentos_child_sid_uq'
            );

            $table->index(
                ['id_empresa', 'id_usuario', 'created_at'],
                'twilio_intentos_usuario_fecha_idx'
            );

            $table->index(
                ['estado', 'expira_en'],
                'twilio_intentos_estado_expira_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('twilio_intentos');
    }
};
