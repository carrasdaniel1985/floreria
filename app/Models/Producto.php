<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'tipo',
        'tamanio',
        'tonos',
        'sku',
        'temporada',
        'vida_util_dias',
        'maneja_stock',
        'es_destacado',
        'activo',
        'stock_actual',
        'stock_minimo',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'maneja_stock' => 'boolean',
            'es_destacado' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function fotos()
    {
        return $this->hasMany(ProductoFoto::class)->orderBy('orden');
    }

    public function fotoPrincipal()
    {
        return $this->hasOne(ProductoFoto::class)->where('es_principal', true);
    }

    public function precios()
    {
        return $this->hasMany(ProductoPrecio::class)->orderBy('fecha_desde', 'desc');
    }

    public function precioVigente()
    {
        return $this->hasOne(ProductoPrecio::class)
            ->where('tipo_lista', 'normal')
            ->where('activo', true)
            ->where('fecha_desde', '<=', now()->toDateString())
            ->where(fn($q) => $q->whereNull('fecha_hasta')->orWhere('fecha_hasta', '>=', now()->toDateString()))
            ->latest('fecha_desde');
    }

    public function margen()
    {
        return $this->hasOne(ProductoMargen::class)->latest();
    }

    public function componentes()
    {
        return $this->hasMany(ProductoComponente::class, 'producto_padre_id');
    }

    public function componenteDe()
    {
        return $this->hasMany(ProductoComponente::class, 'producto_componente_id');
    }

    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class);
    }

    public function mermas()
    {
        return $this->hasMany(Merma::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
