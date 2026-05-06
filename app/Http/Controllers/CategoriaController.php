<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CategoriaController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Categoria::withCount('productos')
            ->when($request->buscar, fn($q, $v) => $q->where('nombre', 'ilike', "%$v%"))
            ->when($request->filled('activa'), fn($q) => $q->where('activa', $request->boolean('activa')))
            ->orderBy('orden')->orderBy('nombre');

        return Inertia::render('Categorias/Index', [
            'categorias' => $query->paginate(20)->withQueryString(),
            'filtros'    => $request->only(['buscar', 'activa']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Categorias/Form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:categorias'],
            'orden'  => ['integer', 'min:0'],
            'activa' => ['boolean'],
            'imagen' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen')->store('categorias', 'public');
        }

        $categoria = Categoria::create($validated);
        AuditoriaService::registrar('crear', 'categorias', 'Categoria', $categoria->id, "Categoría {$categoria->nombre} creada");

        return redirect()->route('categorias.index')->with('success', 'Categoría creada.');
    }

    public function edit(Categoria $categoria): Response
    {
        return Inertia::render('Categorias/Form', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', "unique:categorias,nombre,{$categoria->id}"],
            'orden'  => ['integer', 'min:0'],
            'activa' => ['boolean'],
            'imagen' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('imagen')) {
            if ($categoria->imagen) Storage::disk('public')->delete($categoria->imagen);
            $validated['imagen'] = $request->file('imagen')->store('categorias', 'public');
        }

        $antes = $categoria->only(['nombre', 'orden', 'activa']);
        $categoria->update($validated);
        $cambios = AuditoriaService::diffCambios($antes, $categoria->fresh()->only(array_keys($antes)));
        AuditoriaService::registrar('editar', 'categorias', 'Categoria', $categoria->id, "Categoría {$categoria->nombre} editada", null, $cambios);

        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada.');
    }

    public function toggleActiva(Categoria $categoria): RedirectResponse
    {
        $categoria->update(['activa' => !$categoria->activa]);
        AuditoriaService::registrar('toggle_activa', 'categorias', 'Categoria', $categoria->id, "Categoría {$categoria->nombre} " . ($categoria->activa ? 'activada' : 'desactivada'));
        return back()->with('success', 'Estado actualizado.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        $categoria->update(['activa' => false]);
        return redirect()->route('categorias.index')->with('success', 'Categoría desactivada.');
    }

    public function show(Categoria $categoria): Response
    {
        return Inertia::render('Categorias/Show', ['categoria' => $categoria->load('productos')]);
    }
}
