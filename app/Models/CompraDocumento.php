<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraDocumento extends Model
{
    protected $table = 'compra_documentos';
    protected $fillable = [
        'compra_id',
        'nombre_archivo',
        'path',
        'tipo_mime',
        'tamanio_bytes',
        'subido_por',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}
