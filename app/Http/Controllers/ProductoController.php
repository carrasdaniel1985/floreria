<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductoController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Producto::with(['categoria', 'fotoPrincipal', 'precioVigente'])
            ->when($request->buscar, fn($q, $v) =>
                $q->where('nombre', 'ilike', "%$v%")->orWhere('sku', 'ilike', "%$v%")
            )
            ->when($request->categoria_id, fn($q, $v) => $q->where('categoria_id', $v))
            ->when($request->tipo, fn($q, $v) => $q->where('tipo', $v))
            ->when($request->filled('activo'), fn($q) => $q->where('activo', $request->boolean('activo')))
            ->orderBy('nombre');

        return Inertia::render('Productos/Index', [
            'productos'  => $query->paginate(20)->withQueryString(),
            'categorias' => Categoria::where('activa', true)->orderBy('orden')->get(),
            'filtros'    => $request->only(['buscar', 'categoria_id', 'tipo', 'activo']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Productos/Form', [
            'categorias' => Categoria::where('activa', true)->orderBy('orden')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validar($request);

        $producto = Producto::create(array_merge($validated, ['created_by' => Auth::id()]));

        $this->procesarFotos($request, $producto);

        if ($request->filled('precio_venta')) {
            $producto->precios()->create([
                'tipo_lista'  => 'normal',
                'precio'      => $request->precio_venta,
                'fecha_desde' => now()->toDateString(),
                'activo'      => true,
                'created_by'  => Auth::id(),
            ]);
        }

        AuditoriaService::registrar('crear', 'productos', 'Producto', $producto->id, "Producto {$producto->nombre} creado");

        return redirect()->route('productos.index')->with('success', 'Producto creado.');
    }

    public function show(Producto $producto): Response
    {
        return Inertia::render('Productos/Show', [
            'producto' => $producto->load(['categoria', 'fotos', 'precios.creador', 'margen']),
        ]);
    }

    public function edit(Producto $producto): Response
    {
        return Inertia::render('Productos/Form', [
            'producto'   => $producto->load(['categoria', 'fotos', 'precioVigente', 'margen']),
            'categorias' => Categoria::where('activa', true)->orderBy('orden')->get(),
        ]);
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $this->validar($request, $producto->id);

        $antes = $producto->only(['nombre', 'descripcion', 'tipo', 'tamanio', 'tonos', 'sku', 'temporada', 'vida_util_dias', 'maneja_stock', 'activo', 'categoria_id']);
        $producto->update($validated);
        $this->procesarFotos($request, $producto);

        $cambios = AuditoriaService::diffCambios($antes, $producto->fresh()->only(array_keys($antes)));
        AuditoriaService::registrar('editar', 'productos', 'Producto', $producto->id, "Producto {$producto->nombre} editado", null, $cambios);

        return redirect()->route('productos.show', $producto)->with('success', 'Producto actualizado.');
    }

    public function toggleActivo(Producto $producto): RedirectResponse
    {
        $producto->update(['activo' => !$producto->activo]);
        AuditoriaService::registrar('toggle_activo', 'productos', 'Producto', $producto->id, "Producto {$producto->nombre} " . ($producto->activo ? 'activado' : 'desactivado'));
        return back()->with('success', 'Estado actualizado.');
    }

    private function validar(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nombre'        => ['required', 'string', 'max:200'],
            'descripcion'   => ['nullable', 'string'],
            'categoria_id'  => ['nullable', 'exists:categorias,id'],
            'tipo'          => ['required', 'in:simple,servicio,compuesto'],
            'tamanio'       => ['nullable', 'string', 'max:50'],
            'tonos'         => ['nullable', 'string', 'max:100'],
            'sku'           => ['nullable', 'string', "unique:productos,sku,{$ignoreId}"],
            'temporada'     => ['nullable', 'string', 'max:50'],
            'vida_util_dias' => ['nullable', 'integer', 'min:1'],
            'maneja_stock'  => ['boolean'],
            'es_destacado'  => ['boolean'],
            'activo'        => ['boolean'],
            'stock_minimo'  => ['integer', 'min:0'],
        ]);
    }

    private function procesarFotos(Request $request, Producto $producto): void
    {
        if ($request->hasFile('fotos')) {
            $esPrimero = $producto->fotos()->count() === 0;
            foreach ($request->file('fotos') as $i => $foto) {
                $path = $foto->store('productos', 'public');
                $producto->fotos()->create([
                    'path'        => $path,
                    'es_principal' => $esPrimero && $i === 0,
                    'orden'       => $producto->fotos()->count() + $i,
                ]);
            }
        }
    }
}
