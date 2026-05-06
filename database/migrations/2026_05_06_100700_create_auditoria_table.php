<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion'); // crear, editar, desactivar, eliminar, confirmar, anular, etc.
            $table->string('modulo'); // usuarios, productos, compras, etc.
            $table->string('entidad_tipo')->nullable(); // nombre del modelo
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('motivo')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('auditoria_cambios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auditoria_evento_id')->constrained('auditoria_eventos')->cascadeOnDelete();
            $table->string('campo');
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_cambios');
        Schema::dropIfExists('auditoria_eventos');
    }
};
