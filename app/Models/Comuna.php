<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comuna extends Model
{
    protected $table = 'comunas';
    protected $fillable = ['nombre', 'region', 'activa'];

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    public function tarifas()
    {
        return $this->hasMany(TarifaDespacho::class);
    }
}
