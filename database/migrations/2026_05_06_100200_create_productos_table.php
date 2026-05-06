<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('tipo')->default('simple'); // simple, servicio, compuesto
            $table->string('tamanio')->nullable();
            $table->string('tonos')->nullable();
            $table->string('sku')->unique()->nullable();
            $table->string('temporada')->nullable();
            $table->integer('vida_util_dias')->nullable();
            $table->boolean('maneja_stock')->default(true);
            $table->boolean('es_destacado')->default(false);
            $table->boolean('activo')->default(true);
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Fotos adicionales del producto
        Schema::create('producto_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('path');
            $table->boolean('es_principal')->default(false);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        // Estructura para productos compuestos (fase 2 - solo estructura)
        Schema::create('producto_componentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_padre_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('producto_componente_id')->constrained('productos')->cascadeOnDelete();
            $table->decimal('cantidad', 10, 2)->default(1);
            $table->string('unidad')->nullable();
            $table->timestamps();
            $table->unique(['producto_padre_id', 'producto_componente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_componentes');
        Schema::dropIfExists('producto_fotos');
        Schema::dropIfExists('productos');
    }
};
