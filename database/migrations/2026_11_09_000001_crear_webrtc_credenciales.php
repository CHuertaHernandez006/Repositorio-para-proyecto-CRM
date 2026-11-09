<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('webrtc_credenciales', function (Blueprint $table) {
            $table->id();
            // id_usuario es la PK real de usuarios, no el campo 'id' de Laravel.
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('extension_asterisk', 20)->unique();
            $table->text('secret_cifrado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webrtc_credenciales');
    }
};
