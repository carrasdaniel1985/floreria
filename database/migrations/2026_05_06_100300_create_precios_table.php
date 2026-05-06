<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Historial de precios de venta por producto
        Schema::create('producto_precios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('tipo_lista')->default('normal'); // normal, temporada, cliente_frecuente
            $table->bigInteger('precio'); // CLP, incluye IVA
            $table->date('fecha_desde');
            $table->date('fecha_hasta')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Margen mínimo esperado por producto
        Schema::create('producto_margenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->decimal('margen_minimo_pct', 5, 2)->default(0); // porcentaje
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Comunas para tarifas de despacho
        Schema::create('comunas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('region')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        // Tarifas de despacho por comuna y sucursal (historial)
        Schema::create('tarifas_despacho', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('comuna_id')->constrained('comunas')->cascadeOnDelete();
            $table->bigInteger('precio'); // CLP, incluye IVA
            $table->date('fecha_desde');
            $table->date('fecha_hasta')->nullable();
            $table->boolean('activa')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifas_despacho');
        Schema::dropIfExists('comunas');
        Schema::dropIfExists('producto_margenes');
        Schema::dropIfExists('producto_precios');
    }
};
