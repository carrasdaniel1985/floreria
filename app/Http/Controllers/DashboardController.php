<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [];

        if (auth()->user()->hasRole('administrador')) {
            $stats = [
                'usuarios_activos'    => User::where('activo', true)->count(),
                'productos_activos'   => Producto::where('activo', true)->count(),
                'proveedores_activos' => Proveedor::where('activo', true)->count(),
                'compras_pendientes'  => Compra::where('estado_operacional', 'confirmada')->where('estado_pago', 'pendiente')->count(),
                'compras_sin_doc'     => Compra::where('estado_operacional', 'confirmada')->doesntHave('documentos')->count(),
                'productos_bajo_stock' => Producto::where('activo', true)->where('maneja_stock', true)->whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
            ];
        }

        return Inertia::render('Dashboard', compact('stats'));
    }
}
