<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->string('numero_referencia')->nullable(); // folio boleta/factura del proveedor
            $table->date('fecha_compra');
            // Estado operacional: borrador, confirmada, anulada
            $table->string('estado_operacional')->default('borrador');
            // Estado de pago: pendiente, pagada
            $table->string('estado_pago')->default('pendiente');
            $table->bigInteger('total')->default(0); // calculado desde detalles
            $table->text('observaciones')->nullable();

            // Confirmación
            $table->foreignId('confirmada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmada_at')->nullable();

            // Anulación
            $table->foreignId('anulada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('anulada_at')->nullable();
            $table->text('motivo_anulacion')->nullable();

            // Pago
            $table->date('fecha_pago')->nullable();
            $table->text('observaciones_pago')->nullable();
            $table->foreignId('pago_registrado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('compra_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->string('descripcion_producto')->nullable(); // nombre al momento de compra
            $table->decimal('cantidad', 10, 2);
            $table->bigInteger('costo_unitario'); // CLP
            $table->bigInteger('costo_total'); // CLP
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('compra_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->string('nombre_archivo');
            $table->string('path');
            $table->string('tipo_mime')->nullable();
            $table->bigInteger('tamanio_bytes')->nullable();
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compra_documentos');
        Schema::dropIfExists('compra_detalles');
        Schema::dropIfExists('compras');
    }
};
