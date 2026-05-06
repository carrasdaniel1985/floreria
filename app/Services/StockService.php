<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\Merma;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function registrarEntradaPorCompra(Compra $compra): void
    {
        foreach ($compra->detalles as $detalle) {
            if (!$detalle->producto || !$detalle->producto->maneja_stock) {
                continue;
            }
            $this->mover($detalle->producto, $compra->sucursal, 'entrada', $detalle->cantidad, 'compra', $compra->id);
        }
    }

    public function revertirCompra(Compra $compra): array
    {
        $advertencias = [];
        foreach ($compra->detalles as $detalle) {
            if (!$detalle->producto || !$detalle->producto->maneja_stock) {
                continue;
            }
            $producto = $detalle->producto;
            if ($producto->stock_actual < $detalle->cantidad) {
                $advertencias[] = "Stock insuficiente para revertir '{$producto->nombre}': disponible {$producto->stock_actual}, requerido {$detalle->cantidad}";
            }
            $cantidadARevertir = min($detalle->cantidad, $producto->stock_actual);
            if ($cantidadARevertir > 0) {
                $this->mover($producto, $compra->sucursal, 'salida', $cantidadARevertir, 'compra_anulada', $compra->id);
            }
        }
        return $advertencias;
    }

    public function registrarMerma(Merma $merma): void
    {
        $this->mover($merma->producto, $merma->sucursal, 'merma', $merma->cantidad, 'merma', $merma->id);
    }

    private function mover(Producto $producto, Sucursal $sucursal, string $tipo, float $cantidad, string $refTipo, int $refId): void
    {
        DB::transaction(function () use ($producto, $sucursal, $tipo, $cantidad, $refTipo, $refId) {
            $stockAntes = $producto->stock_actual;
            $stockDespues = $tipo === 'entrada'
                ? $stockAntes + $cantidad
                : max(0, $stockAntes - $cantidad);

            MovimientoStock::create([
                'producto_id'    => $producto->id,
                'sucursal_id'    => $sucursal->id,
                'tipo'           => $tipo,
                'cantidad'       => $cantidad,
                'stock_antes'    => $stockAntes,
                'stock_despues'  => $stockDespues,
                'referencia_tipo' => $refTipo,
                'referencia_id'  => $refId,
                'created_by'     => Auth::id(),
            ]);

            $producto->update(['stock_actual' => $stockDespues]);
        });
    }
}
