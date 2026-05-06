<?php

namespace App\Http\Controllers;

use App\Models\Merma;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Services\AuditoriaService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MermaController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(Request $request): Response
    {
        $query = Merma::with(['producto', 'sucursal', 'creador'])
            ->when($request->producto_id, fn($q, $v) => $q->where('producto_id', $v))
            ->when($request->motivo, fn($q, $v) => $q->where('motivo', $v))
            ->when($request->fecha_desde, fn($q, $v) => $q->where('created_at', '>=', $v))
            ->when($request->fecha_hasta, fn($q, $v) => $q->where('created_at', '<=', "$v 23:59:59"))
            ->orderBy('created_at', 'desc');

        return Inertia::render('Mermas/Index', [
            'mermas'   => $query->paginate(20)->withQueryString(),
            'filtros'  => $request->only(['producto_id', 'motivo', 'fecha_desde', 'fecha_hasta']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Mermas/Form', [
            'productos'  => Producto::where('activo', true)->where('maneja_stock', true)->orderBy('nombre')->get(),
            'sucursales' => Sucursal::where('activa', true)->get(),
            'motivos'    => ['merma', 'vencido', 'dañado', 'descartado', 'otro'],
            'sucursal_default_id' => Sucursal::where('nombre', 'Isla de Maipo')->first()?->id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'producto_id'  => ['required', 'exists:productos,id'],
            'sucursal_id'  => ['required', 'exists:sucursales,id'],
            'cantidad'     => ['required', 'numeric', 'min:0.01'],
            'motivo'       => ['required', 'in:merma,vencido,dañado,descartado,otro'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $merma = DB::transaction(function () use ($validated) {
            $merma = Merma::create(array_merge($validated, ['created_by' => Auth::id()]));
            $this->stockService->registrarMerma($merma->load(['producto', 'sucursal']));
            return $merma;
        });

        AuditoriaService::registrar('registrar_merma', 'mermas', 'Merma', $merma->id,
            "Merma registrada: {$validated['cantidad']} unidades de producto #{$validated['producto_id']} — motivo: {$validated['motivo']}");

        return redirect()->route('mermas.index')->with('success', 'Merma registrada. Stock descontado.');
    }

    public function show(Merma $merma): Response
    {
        return Inertia::render('Mermas/Show', [
            'merma' => $merma->load(['producto', 'sucursal', 'creador']),
        ]);
    }
}
