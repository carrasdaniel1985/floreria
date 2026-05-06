<?php

namespace App\Services;

use App\Models\AuditoriaEvento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditoriaService
{
    public static function registrar(
        string $accion,
        string $modulo,
        ?string $entidadTipo = null,
        ?int $entidadId = null,
        ?string $descripcion = null,
        ?string $motivo = null,
        array $cambios = [],
        ?int $userId = null
    ): AuditoriaEvento {
        $evento = AuditoriaEvento::create([
            'user_id'      => $userId ?? Auth::id(),
            'accion'       => $accion,
            'modulo'       => $modulo,
            'entidad_tipo' => $entidadTipo,
            'entidad_id'   => $entidadId,
            'descripcion'  => $descripcion,
            'motivo'       => $motivo,
            'ip_address'   => Request::ip(),
        ]);

        foreach ($cambios as $campo => $valores) {
            $evento->cambios()->create([
                'campo'          => $campo,
                'valor_anterior' => $valores['antes'] ?? null,
                'valor_nuevo'    => $valores['despues'] ?? null,
            ]);
        }

        return $evento;
    }

    public static function diffCambios(array $antes, array $despues): array
    {
        $cambios = [];
        foreach ($despues as $campo => $valorNuevo) {
            $valorAntes = $antes[$campo] ?? null;
            if ($valorAntes != $valorNuevo) {
                $cambios[$campo] = ['antes' => $valorAntes, 'despues' => $valorNuevo];
            }
        }
        return $cambios;
    }
}
