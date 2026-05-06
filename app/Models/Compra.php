<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'proveedor_id',
        'sucursal_id',
        'numero_referencia',
        'fecha_compra',
        'estado_operacional',
        'estado_pago',
        'total',
        'observaciones',
        'confirmada_por',
        'confirmada_at',
        'anulada_por',
        'anulada_at',
        'motivo_anulacion',
        'fecha_pago',
        'observaciones_pago',
        'pago_registrado_por',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fecha_compra' => 'date',
            'confirmada_at' => 'datetime',
            'anulada_at' => 'datetime',
            'fecha_pago' => 'date',
        ];
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }

    public function documentos()
    {
        return $this->hasMany(CompraDocumento::class);
    }

    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class, 'referencia_id')
            ->where('referencia_tipo', 'compra');
    }

    public function confirmadaPor()
    {
        return $this->belongsTo(User::class, 'confirmada_por');
    }

    public function anuladaPor()
    {
        return $this->belongsTo(User::class, 'anulada_por');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function esBorrador(): bool
    {
        return $this->estado_operacional === 'borrador';
    }

    public function esConfirmada(): bool
    {
        return $this->estado_operacional === 'confirmada';
    }

    public function esAnulada(): bool
    {
        return $this->estado_operacional === 'anulada';
    }

    public function recalcularTotal(): void
    {
        $this->total = $this->detalles()->sum('costo_total');
        $this->save();
    }
}
