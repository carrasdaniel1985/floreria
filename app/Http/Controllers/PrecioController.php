<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoPrecio;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrecioController extends Controller
{
    public function store(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'tipo_lista'   => ['required', 'in:normal,temporada,cliente_frecuente'],
            'precio'       => ['required', 'integer', 'min:0'],
            'fecha_desde'  => ['required', 'date'],
            'fecha_hasta'  => ['nullable', 'date', 'after:fecha_desde'],
        ]);

        // Desactivar precios anteriores del mismo tipo_lista
        $producto->precios()
            ->where('tipo_lista', $validated['tipo_lista'])
            ->where('activo', true)
            ->update(['activo' => false, 'fecha_hasta' => now()->subDay()->toDateString()]);

        $precio = $producto->precios()->create(array_merge($validated, [
            'activo'     => true,
            'created_by' => Auth::id(),
        ]));

        AuditoriaService::registrar('crear_precio', 'productos', 'ProductoPrecio', $precio->id, "Precio {$validated['tipo_lista']} de {$producto->nombre} establecido en {$validated['precio']}");

        return back()->with('success', 'Precio registrado.');
    }

    public function destroy(Producto $producto, ProductoPrecio $precio): RedirectResponse
    {
        $precio->update(['activo' => false]);
        AuditoriaService::registrar('desactivar_precio', 'productos', 'ProductoPrecio', $precio->id, "Precio de {$producto->nombre} desactivado");
        return back()->with('success', 'Precio desactivado.');
    }

    public function updateMargen(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'margen_minimo_pct' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $producto->margen()->updateOrCreate(
            ['producto_id' => $producto->id],
            array_merge($validated, ['created_by' => Auth::id()])
        );

        AuditoriaService::registrar('actualizar_margen', 'productos', 'Producto', $producto->id, "Margen mínimo de {$producto->nombre} actualizado a {$validated['margen_minimo_pct']}%");

        return back()->with('success', 'Margen actualizado.');
    }
}
