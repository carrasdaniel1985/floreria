<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoPrecio extends Model
{
    protected $fillable = [
        'producto_id',
        'tipo_lista',
        'precio',
        'fecha_desde',
        'fecha_hasta',
        'activo',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
        ];
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
