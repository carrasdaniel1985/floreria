<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaEvento extends Model
{
    protected $table = 'auditoria_eventos';
    protected $fillable = [
        'user_id',
        'accion',
        'modulo',
        'entidad_tipo',
        'entidad_id',
        'descripcion',
        'motivo',
        'ip_address',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cambios()
    {
        return $this->hasMany(AuditoriaCambio::class);
    }
}
