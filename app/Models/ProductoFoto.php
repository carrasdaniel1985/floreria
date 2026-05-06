<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoFoto extends Model
{
    protected $fillable = ['producto_id', 'path', 'es_principal', 'orden'];

    protected function casts(): array
    {
        return ['es_principal' => 'boolean'];
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
