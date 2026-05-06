<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sucursales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('direccion')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::create('usuario_sucursales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'sucursal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_sucursales');
        Schema::dropIfExists('sucursales');
    }
};
