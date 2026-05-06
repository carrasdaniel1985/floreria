<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoMargen extends Model
{
    protected $fillable = ['producto_id', 'margen_minimo_pct', 'created_by'];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
