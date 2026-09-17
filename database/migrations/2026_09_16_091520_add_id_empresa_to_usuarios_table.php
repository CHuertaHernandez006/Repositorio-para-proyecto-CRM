<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Agregamos la columna permitiendo nulos
            $table->unsignedBigInteger('id_empresa')->nullable()->after('id_rol');
        
            // Creamos la relación (llave foránea)
            $table->foreign('id_empresa')
                ->references('id_empresa')
                ->on('empresas')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_empresa']);
            $table->dropColumn('id_empresa');
        });
    }
};
