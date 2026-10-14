<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->integer('id_usuario_responsable')->nullable();
            $table->index(['id_empresa', 'id_usuario_responsable'], 'idx_clientes_responsable');
            $table->foreign(['id_empresa', 'id_usuario_responsable'], 'fk_clientes_responsable_empresa')
                ->references(['id_empresa', 'id_usuario'])->on('usuarios')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign('fk_clientes_responsable_empresa');
            $table->dropIndex('idx_clientes_responsable');
            $table->dropColumn('id_usuario_responsable');
        });
    }
};
