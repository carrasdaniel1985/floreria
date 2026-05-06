<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProveedorController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Proveedor::withCount('compras')
            ->when($request->buscar, fn($q, $v) =>
                $q->where('nombre', 'ilike', "%$v%")->orWhere('rut', 'ilike', "%$v%")
            )
            ->when($request->filled('activo'), fn($q) => $q->where('activo', $request->boolean('activo')))
            ->orderBy('nombre');

        return Inertia::render('Proveedores/Index', [
            'proveedores' => $query->paginate(20)->withQueryString(),
            'filtros'     => $request->only(['buscar', 'activo']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Proveedores/Form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'          => ['required', 'string', 'max:200'],
            'rut'             => ['required', 'string', 'max:20', 'unique:proveedores'],
            'telefono'        => ['nullable', 'string', 'max:20'],
            'email'           => ['nullable', 'email'],
            'direccion'       => ['nullable', 'string', 'max:300'],
            'tipos_productos' => ['nullable', 'string', 'max:300'],
            'activo'          => ['boolean'],
        ]);

        $proveedor = Proveedor::create(array_merge($validated, ['created_by' => Auth::id()]));
        AuditoriaService::registrar('crear', 'proveedores', 'Proveedor', $proveedor->id, "Proveedor {$proveedor->nombre} creado");

        return redirect()->route('proveedores.index')->with('success', 'Proveedor registrado.');
    }

    public function edit(Proveedor $proveedor): Response
    {
        return Inertia::render('Proveedores/Form', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'          => ['required', 'string', 'max:200'],
            'rut'             => ['required', 'string', 'max:20', "unique:proveedores,rut,{$proveedor->id}"],
            'telefono'        => ['nullable', 'string', 'max:20'],
            'email'           => ['nullable', 'email'],
            'direccion'       => ['nullable', 'string', 'max:300'],
            'tipos_productos' => ['nullable', 'string', 'max:300'],
            'activo'          => ['boolean'],
        ]);

        $antes = $proveedor->only(['nombre', 'rut', 'telefono', 'email', 'direccion', 'tipos_productos', 'activo']);
        $proveedor->update($validated);
        $cambios = AuditoriaService::diffCambios($antes, $proveedor->fresh()->only(array_keys($antes)));
        AuditoriaService::registrar('editar', 'proveedores', 'Proveedor', $proveedor->id, "Proveedor {$proveedor->nombre} editado", null, $cambios);

        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado.');
    }

    public function toggleActivo(Proveedor $proveedor): RedirectResponse
    {
        $proveedor->update(['activo' => !$proveedor->activo]);
        return back()->with('success', 'Estado actualizado.');
    }

    public function show(Proveedor $proveedor): Response
    {
        return Inertia::render('Proveedores/Show', [
            'proveedor' => $proveedor->load(['compras' => fn($q) => $q->latest()->limit(10)]),
        ]);
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        $proveedor->update(['activo' => false]);
        return redirect()->route('proveedores.index')->with('success', 'Proveedor desactivado.');
    }
}
