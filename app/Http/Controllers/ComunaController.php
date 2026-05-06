<?php

namespace App\Http\Controllers;

use App\Models\Comuna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComunaController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Comuna::withCount('tarifas')
            ->when($request->buscar, fn($q, $v) => $q->where('nombre', 'ilike', "%$v%"))
            ->when($request->filled('activa'), fn($q) => $q->where('activa', $request->boolean('activa')))
            ->orderBy('nombre');

        return Inertia::render('Comunas/Index', [
            'comunas' => $query->paginate(30)->withQueryString(),
            'filtros' => $request->only(['buscar', 'activa']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Comunas/Form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:comunas'],
            'region' => ['nullable', 'string', 'max:100'],
            'activa' => ['boolean'],
        ]);

        Comuna::create($validated);
        return redirect()->route('comunas.index')->with('success', 'Comuna creada.');
    }

    public function edit(Comuna $comuna): Response
    {
        return Inertia::render('Comunas/Form', compact('comuna'));
    }

    public function update(Request $request, Comuna $comuna): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', "unique:comunas,nombre,{$comuna->id}"],
            'region' => ['nullable', 'string', 'max:100'],
            'activa' => ['boolean'],
        ]);

        $comuna->update($validated);
        return redirect()->route('comunas.index')->with('success', 'Comuna actualizada.');
    }

    public function toggleActiva(Comuna $comuna): RedirectResponse
    {
        $comuna->update(['activa' => !$comuna->activa]);
        return back()->with('success', 'Estado actualizado.');
    }

    public function show(Comuna $comuna): Response
    {
        return Inertia::render('Comunas/Show', ['comuna' => $comuna->load('tarifas.sucursal')]);
    }

    public function destroy(Comuna $comuna): RedirectResponse
    {
        $comuna->update(['activa' => false]);
        return redirect()->route('comunas.index')->with('success', 'Comuna desactivada.');
    }
}
