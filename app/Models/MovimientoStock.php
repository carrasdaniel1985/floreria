<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoStock extends Model
{
    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'tipo',
        'cantidad',
        'stock_antes',
        'stock_despues',
        'referencia_tipo',
        'referencia_id',
        'observaciones',
        'created_by',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
