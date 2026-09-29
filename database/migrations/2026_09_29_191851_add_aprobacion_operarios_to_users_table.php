<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | ESTADO DE APROBACIÓN
            |--------------------------------------------------------------------------
            |
            | Valores utilizados:
            |
            | pendiente
            | aprobado
            | rechazado
            |
            | Lo dejamos nullable para que este sistema no afecte
            | a usuarios que no sean operarios.
            |
            */

            $table
                ->string(
                    'estado_aprobacion',
                    20
                )
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | USUARIO QUE REALIZÓ LA SOLICITUD
            |--------------------------------------------------------------------------
            */

            $table
                ->foreignId(
                    'solicitado_por'
                )
                ->nullable()
                ->constrained(
                    'users'
                )
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | FECHA DE SOLICITUD
            |--------------------------------------------------------------------------
            */

            $table
                ->timestamp(
                    'fecha_solicitud'
                )
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | SUPER ADMIN QUE REVISÓ LA SOLICITUD
            |--------------------------------------------------------------------------
            */

            $table
                ->foreignId(
                    'revisado_por'
                )
                ->nullable()
                ->constrained(
                    'users'
                )
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | FECHA DE REVISIÓN
            |--------------------------------------------------------------------------
            */

            $table
                ->timestamp(
                    'fecha_revision'
                )
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | MOTIVO DE RECHAZO
            |--------------------------------------------------------------------------
            */

            $table
                ->text(
                    'motivo_rechazo'
                )
                ->nullable();
        });


        /*
        |--------------------------------------------------------------------------
        | OPERARIOS QUE YA EXISTÍAN
        |--------------------------------------------------------------------------
        |
        | Los operarios actuales fueron creados antes de implementar
        | el sistema de solicitudes.
        |
        | Los marcamos como aprobados para que sigan funcionando
        | normalmente y no aparezcan como pendientes.
        |
        */

        DB::table('users')
            ->where(
                'id_rol',
                3
            )
            ->whereNull(
                'estado_aprobacion'
            )
            ->update([
                'estado_aprobacion' =>
                    'aprobado',
            ]);
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | PRIMERO ELIMINAMOS LAS FOREIGN KEYS
            |--------------------------------------------------------------------------
            */

            $table->dropForeign([
                'solicitado_por',
            ]);

            $table->dropForeign([
                'revisado_por',
            ]);


            /*
            |--------------------------------------------------------------------------
            | DESPUÉS LAS COLUMNAS
            |--------------------------------------------------------------------------
            */

            $table->dropColumn([
                'estado_aprobacion',
                'solicitado_por',
                'fecha_solicitud',
                'revisado_por',
                'fecha_revision',
                'motivo_rechazo',
            ]);
        });
    }
};