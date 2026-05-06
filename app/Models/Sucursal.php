<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';
    protected $fillable = ['nombre', 'direccion', 'telefono', 'activa'];

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_sucursales');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class);
    }

    public function tarifasDespacho()
    {
        return $this->hasMany(TarifaDespacho::class);
    }
}
