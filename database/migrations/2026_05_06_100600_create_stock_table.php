<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Movimientos de stock (entrada por compra, salida por merma/ajuste)
        Schema::create('movimientos_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->string('tipo'); // entrada, salida, merma, ajuste
            $table->decimal('cantidad', 10, 2);
            $table->integer('stock_antes');
            $table->integer('stock_despues');
            $table->string('referencia_tipo')->nullable(); // compra, merma, ajuste_manual
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Mermas y pérdidas
        Schema::create('mermas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->decimal('cantidad', 10, 2);
            $table->string('motivo'); // merma, vencido, dañado, descartado, otro
            $table->text('observaciones')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mermas');
        Schema::dropIfExists('movimientos_stock');
    }
};
