<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraDocumento;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Services\AuditoriaService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompraController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(Request $request): Response
    {
        $query = Compra::with(['proveedor', 'sucursal', 'creador'])
            ->withCount('documentos')
            ->when($request->buscar, fn($q, $v) => $q->whereHas('proveedor', fn($p) => $p->where('nombre', 'ilike', "%$v%")))
            ->when($request->estado_operacional, fn($q, $v) => $q->where('estado_operacional', $v))
            ->when($request->estado_pago, fn($q, $v) => $q->where('estado_pago', $v))
            ->when($request->proveedor_id, fn($q, $v) => $q->where('proveedor_id', $v))
            ->when($request->sin_documento, fn($q) => $q->doesntHave('documentos'))
            ->when($request->fecha_desde, fn($q, $v) => $q->where('fecha_compra', '>=', $v))
            ->when($request->fecha_hasta, fn($q, $v) => $q->where('fecha_compra', '<=', $v))
            ->orderBy('fecha_compra', 'desc');

        return Inertia::render('Compras/Index', [
            'compras'      => $query->paginate(20)->withQueryString(),
            'proveedores'  => Proveedor::where('activo', true)->orderBy('nombre')->get(),
            'filtros'      => $request->only(['buscar', 'estado_operacional', 'estado_pago', 'proveedor_id', 'sin_documento', 'fecha_desde', 'fecha_hasta']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Compras/Form', [
            'proveedores' => Proveedor::where('activo', true)->orderBy('nombre')->get(),
            'sucursales'  => Sucursal::where('activa', true)->get(),
            'productos'   => Producto::where('activo', true)->with('precioVigente')->orderBy('nombre')->get(),
            'sucursal_default_id' => Sucursal::where('nombre', 'Isla de Maipo')->first()?->id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validarCompra($request);
        $compra = DB::transaction(function () use ($validated, $request) {
            $compra = Compra::create(array_merge($validated['compra'], ['created_by' => Auth::id()]));
            $this->guardarDetalles($compra, $validated['detalles']);
            $compra->recalcularTotal();
            return $compra;
        });

        AuditoriaService::registrar('crear', 'compras', 'Compra', $compra->id, "Compra #{$compra->id} creada en estado borrador");
        return redirect()->route('compras.show', $compra)->with('success', 'Compra guardada como borrador.');
    }

    public function show(Compra $compra): Response
    {
        return Inertia::render('Compras/Show', [
            'compra' => $compra->load([
                'proveedor', 'sucursal', 'detalles.producto',
                'documentos.subidoPor', 'confirmadaPor', 'anuladaPor', 'creador'
            ]),
        ]);
    }

    public function edit(Compra $compra): Response
    {
        abort_if($compra->esAnulada(), 403, 'No se puede editar una compra anulada.');

        return Inertia::render('Compras/Form', [
            'compra'      => $compra->load(['proveedor', 'sucursal', 'detalles.producto']),
            'proveedores' => Proveedor::where('activo', true)->orderBy('nombre')->get(),
            'sucursales'  => Sucursal::where('activa', true)->get(),
            'productos'   => Producto::where('activo', true)->with('precioVigente')->orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Compra $compra): RedirectResponse
    {
        abort_if($compra->esAnulada(), 403, 'No se puede editar una compra anulada.');

        $validated = $this->validarCompra($request);
        $motivo = $request->validate(['motivo_modificacion' => ['required_if:estado_operacional,confirmada', 'nullable', 'string']])['motivo_modificacion'] ?? null;

        DB::transaction(function () use ($compra, $validated, $motivo) {
            $antes = $compra->only(['proveedor_id', 'fecha_compra', 'observaciones', 'numero_referencia']);
            $detallesAntesQty = $compra->detalles()->pluck('cantidad', 'producto_id')->toArray();

            $compra->update($validated['compra']);
            $compra->detalles()->delete();
            $this->guardarDetalles($compra, $validated['detalles']);
            $compra->recalcularTotal();

            // Si estaba confirmada, ajustar stock
            if ($compra->esConfirmada()) {
                $this->ajustarStockPorModificacion($compra, $detallesAntesQty);
            }

            $cambios = AuditoriaService::diffCambios($antes, $compra->fresh()->only(array_keys($antes)));
            AuditoriaService::registrar('editar', 'compras', 'Compra', $compra->id, "Compra #{$compra->id} modificada", $motivo, $cambios);
        });

        return redirect()->route('compras.show', $compra)->with('success', 'Compra actualizada.');
    }

    public function confirmar(Request $request, Compra $compra): RedirectResponse
    {
        abort_if(!$compra->esBorrador(), 403, 'Solo se pueden confirmar compras en borrador.');

        DB::transaction(function () use ($compra) {
            $compra->update([
                'estado_operacional' => 'confirmada',
                'confirmada_por'     => Auth::id(),
                'confirmada_at'      => now(),
            ]);
            $this->stockService->registrarEntradaPorCompra($compra->load('detalles.producto'));
        });

        AuditoriaService::registrar('confirmar', 'compras', 'Compra', $compra->id, "Compra #{$compra->id} confirmada — stock actualizado");
        return redirect()->route('compras.show', $compra)->with('success', 'Compra confirmada. Stock actualizado.');
    }

    public function anular(Request $request, Compra $compra): RedirectResponse
    {
        abort_if(!$compra->esConfirmada(), 403, 'Solo se pueden anular compras confirmadas.');

        $request->validate(['motivo_anulacion' => ['required', 'string', 'min:10']]);

        $advertencias = [];
        DB::transaction(function () use ($compra, $request, &$advertencias) {
            $advertencias = $this->stockService->revertirCompra($compra->load('detalles.producto'));
            $compra->update([
                'estado_operacional' => 'anulada',
                'anulada_por'        => Auth::id(),
                'anulada_at'         => now(),
                'motivo_anulacion'   => $request->motivo_anulacion,
            ]);
        });

        AuditoriaService::registrar('anular', 'compras', 'Compra', $compra->id, "Compra #{$compra->id} anulada", $request->motivo_anulacion);

        $msg = 'Compra anulada.';
        if ($advertencias) {
            $msg .= ' Advertencias: ' . implode('; ', $advertencias);
        }

        return redirect()->route('compras.show', $compra)->with('success', $msg);
    }

    public function subirDocumento(Request $request, Compra $compra): RedirectResponse
    {
        $request->validate([
            'documento' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $file = $request->file('documento');
        $path = $file->store("compras/{$compra->id}/documentos", 'private');

        $compra->documentos()->create([
            'nombre_archivo' => $file->getClientOriginalName(),
            'path'           => $path,
            'tipo_mime'      => $file->getClientMimeType(),
            'tamanio_bytes'  => $file->getSize(),
            'subido_por'     => Auth::id(),
        ]);

        AuditoriaService::registrar('adjuntar_documento', 'compras', 'Compra', $compra->id, "Documento adjuntado a compra #{$compra->id}");
        return back()->with('success', 'Documento adjuntado.');
    }

    public function eliminarDocumento(Compra $compra, CompraDocumento $documento): RedirectResponse
    {
        Storage::disk('private')->delete($documento->path);
        $documento->delete();
        return back()->with('success', 'Documento eliminado.');
    }

    public function descargarDocumento(Compra $compra, CompraDocumento $documento)
    {
        return Storage::disk('private')->download($documento->path, $documento->nombre_archivo);
    }

    public function actualizarPago(Request $request, Compra $compra): RedirectResponse
    {
        $validated = $request->validate([
            'estado_pago'        => ['required', 'in:pendiente,pagada'],
            'fecha_pago'         => ['nullable', 'date'],
            'observaciones_pago' => ['nullable', 'string'],
        ]);

        $antes = ['estado_pago' => $compra->estado_pago];
        $compra->update(array_merge($validated, ['pago_registrado_por' => Auth::id()]));

        AuditoriaService::registrar('actualizar_pago', 'compras', 'Compra', $compra->id, "Estado de pago de compra #{$compra->id} actualizado a {$validated['estado_pago']}", null,
            AuditoriaService::diffCambios($antes, ['estado_pago' => $validated['estado_pago']]));

        return back()->with('success', 'Estado de pago actualizado.');
    }

    public function destroy(Compra $compra): RedirectResponse
    {
        abort_if(!$compra->esBorrador(), 403, 'Solo se pueden descartar compras en borrador.');
        $compra->detalles()->delete();
        $compra->delete();
        return redirect()->route('compras.index')->with('success', 'Borrador descartado.');
    }

    private function validarCompra(Request $request): array
    {
        $data = $request->validate([
            'proveedor_id'      => ['nullable', 'exists:proveedores,id'],
            'sucursal_id'       => ['required', 'exists:sucursales,id'],
            'numero_referencia' => ['nullable', 'string', 'max:50'],
            'fecha_compra'      => ['required', 'date'],
            'observaciones'     => ['nullable', 'string'],
            'detalles'          => ['required', 'array', 'min:1'],
            'detalles.*.producto_id'         => ['nullable', 'exists:productos,id'],
            'detalles.*.descripcion_producto' => ['nullable', 'string'],
            'detalles.*.cantidad'             => ['required', 'numeric', 'min:0.01'],
            'detalles.*.costo_unitario'       => ['required', 'integer', 'min:0'],
            'detalles.*.observaciones'        => ['nullable', 'string'],
        ]);

        return [
            'compra'   => collect($data)->except('detalles')->toArray(),
            'detalles' => $data['detalles'],
        ];
    }

    private function guardarDetalles(Compra $compra, array $detalles): void
    {
        foreach ($detalles as $det) {
            $producto = $det['producto_id'] ? Producto::find($det['producto_id']) : null;
            $compra->detalles()->create([
                'producto_id'         => $det['producto_id'] ?? null,
                'descripcion_producto' => $det['descripcion_producto'] ?? $producto?->nombre,
                'cantidad'            => $det['cantidad'],
                'costo_unitario'      => $det['costo_unitario'],
                'costo_total'         => $det['cantidad'] * $det['costo_unitario'],
                'observaciones'       => $det['observaciones'] ?? null,
            ]);
        }
    }

    private function ajustarStockPorModificacion(Compra $compra, array $detallesAntesQty): void
    {
        // Revertir stock anterior y registrar nuevo
        foreach ($detallesAntesQty as $productoId => $cantidadAntes) {
            $producto = Producto::find($productoId);
            if ($producto && $producto->maneja_stock) {
                $this->stockService->registrarEntradaPorCompra($compra); // simplificado
            }
        }
    }
}
