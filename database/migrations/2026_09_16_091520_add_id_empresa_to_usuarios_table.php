<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'id_empresa')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('id_empresa')
                    ->nullable()
                    ->after('id_rol');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'id_empresa')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('id_empresa');
            });
        }
    }
};