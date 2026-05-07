<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merma extends Model
{
    protected $table = 'mermas';
    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'cantidad',
        'motivo',
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
