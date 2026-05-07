<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifaDespacho extends Model
{
    protected $table = 'tarifas_despacho';
    protected $fillable = [
        'sucursal_id',
        'comuna_id',
        'precio',
        'fecha_desde',
        'fecha_hasta',
        'activa',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
        ];
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function comuna()
    {
        return $this->belongsTo(Comuna::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
