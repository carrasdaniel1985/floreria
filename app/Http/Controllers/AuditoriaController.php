<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaEvento;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditoriaController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AuditoriaEvento::with('usuario')
            ->when($request->modulo, fn($q, $v) => $q->where('modulo', $v))
            ->when($request->accion, fn($q, $v) => $q->where('accion', $v))
            ->when($request->user_id, fn($q, $v) => $q->where('user_id', $v))
            ->when($request->fecha_desde, fn($q, $v) => $q->where('created_at', '>=', $v))
            ->when($request->fecha_hasta, fn($q, $v) => $q->where('created_at', '<=', "$v 23:59:59"))
            ->orderBy('created_at', 'desc');

        return Inertia::render('Auditoria/Index', [
            'eventos'  => $query->paginate(30)->withQueryString(),
            'usuarios' => User::orderBy('nombre')->get(['id', 'nombre', 'apellido']),
            'modulos'  => AuditoriaEvento::distinct()->pluck('modulo')->sort()->values(),
            'filtros'  => $request->only(['modulo', 'accion', 'user_id', 'fecha_desde', 'fecha_hasta']),
        ]);
    }

    public function show(AuditoriaEvento $evento): Response
    {
        return Inertia::render('Auditoria/Show', [
            'evento' => $evento->load(['usuario', 'cambios']),
        ]);
    }
}
