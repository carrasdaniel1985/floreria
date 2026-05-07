<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';
    protected $fillable = [
        'nombre',
        'rut',
        'telefono',
        'email',
        'direccion',
        'tipos_productos',
        'activo',
        'created_by',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
