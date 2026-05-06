<?php

namespace App\Http\Controllers;

use App\Models\Comuna;
use App\Models\Sucursal;
use App\Models\TarifaDespacho;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TarifaDespachoController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TarifaDespacho::with(['sucursal', 'comuna'])
            ->when($request->sucursal_id, fn($q, $v) => $q->where('sucursal_id', $v))
            ->when($request->comuna_id, fn($q, $v) => $q->where('comuna_id', $v))
            ->when($request->filled('activa'), fn($q) => $q->where('activa', $request->boolean('activa')))
            ->orderBy('created_at', 'desc');

        return Inertia::render('TarifasDespacho/Index', [
            'tarifas'    => $query->paginate(20)->withQueryString(),
            'sucursales' => Sucursal::where('activa', true)->get(),
            'comunas'    => Comuna::where('activa', true)->orderBy('nombre')->get(),
            'filtros'    => $request->only(['sucursal_id', 'comuna_id', 'activa']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('TarifasDespacho/Form', [
            'sucursales' => Sucursal::where('activa', true)->get(),
            'comunas'    => Comuna::where('activa', true)->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'comuna_id'   => ['required', 'exists:comunas,id'],
            'precio'      => ['required', 'integer', 'min:0'],
            'fecha_desde' => ['required', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after:fecha_desde'],
            'activa'      => ['boolean'],
        ]);

        // Desactivar tarifa anterior para misma sucursal+comuna
        TarifaDespacho::where('sucursal_id', $validated['sucursal_id'])
            ->where('comuna_id', $validated['comuna_id'])
            ->where('activa', true)
            ->update(['activa' => false]);

        $tarifa = TarifaDespacho::create(array_merge($validated, ['created_by' => Auth::id(), 'activa' => true]));
        AuditoriaService::registrar('crear', 'tarifas_despacho', 'TarifaDespacho', $tarifa->id, "Tarifa despacho creada");

        return redirect()->route('tarifas-despacho.index')->with('success', 'Tarifa registrada.');
    }

    public function edit(TarifaDespacho $tarifasDespacho): Response
    {
        return Inertia::render('TarifasDespacho/Form', [
            'tarifa'     => $tarifasDespacho->load(['sucursal', 'comuna']),
            'sucursales' => Sucursal::where('activa', true)->get(),
            'comunas'    => Comuna::where('activa', true)->orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, TarifaDespacho $tarifasDespacho): RedirectResponse
    {
        $validated = $request->validate([
            'precio'      => ['required', 'integer', 'min:0'],
            'fecha_desde' => ['required', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after:fecha_desde'],
            'activa'      => ['boolean'],
        ]);

        $tarifasDespacho->update($validated);
        AuditoriaService::registrar('editar', 'tarifas_despacho', 'TarifaDespacho', $tarifasDespacho->id, "Tarifa despacho editada");

        return redirect()->route('tarifas-despacho.index')->with('success', 'Tarifa actualizada.');
    }

    public function show(TarifaDespacho $tarifasDespacho): Response
    {
        return Inertia::render('TarifasDespacho/Show', ['tarifa' => $tarifasDespacho->load(['sucursal', 'comuna'])]);
    }

    public function destroy(TarifaDespacho $tarifasDespacho): RedirectResponse
    {
        $tarifasDespacho->update(['activa' => false]);
        return redirect()->route('tarifas-despacho.index')->with('success', 'Tarifa desactivada.');
    }
}
