<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoComponente extends Model
{
    protected $fillable = ['producto_padre_id', 'producto_componente_id', 'cantidad', 'unidad'];

    public function productoPadre()
    {
        return $this->belongsTo(Producto::class, 'producto_padre_id');
    }

    public function componente()
    {
        return $this->belongsTo(Producto::class, 'producto_componente_id');
    }
}
