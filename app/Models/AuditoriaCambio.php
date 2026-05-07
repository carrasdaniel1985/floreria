<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaCambio extends Model
{
    protected $table = 'auditoria_cambios';
    protected $fillable = [
        'auditoria_evento_id',
        'campo',
        'valor_anterior',
        'valor_nuevo',
    ];

    public function evento()
    {
        return $this->belongsTo(AuditoriaEvento::class, 'auditoria_evento_id');
    }
}
