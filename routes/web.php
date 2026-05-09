<?php

use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PrecioController;
use App\Http\Controllers\ComunaController;
use App\Http\Controllers\TarifaDespachoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\MermaController;
use App\Http\Controllers\AuditoriaController;
use Illuminate\Support\Facades\Route;

// Raíz redirige a login o dashboard
Route::get('/', fn() => redirect()->route('login'));

// Cambio de contraseña obligatorio (autenticado pero sin restricción de must_change_password)
Route::middleware('auth')->group(function () {
    Route::get('/password/change', [PasswordChangeController::class, 'create'])->name('password.change');
    Route::post('/password/change', [PasswordChangeController::class, 'store'])->name('password.change.store');
});

// Rutas protegidas: autenticado + usuario activo + contraseña cambiada
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Usuarios (solo administrador)
    Route::middleware('role:administrador')->group(function () {
        Route::resource('usuarios', UsuarioController::class);
        Route::patch('/usuarios/{usuario}/toggle-activo', [UsuarioController::class, 'toggleActivo'])->name('usuarios.toggle-activo');
        Route::post('/usuarios/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])->name('usuarios.reset-password');
    });

    // Categorías (administrador)
    Route::middleware('role:administrador')->group(function () {
        Route::resource('categorias', CategoriaController::class);
        Route::patch('/categorias/{categoria}/toggle-activa', [CategoriaController::class, 'toggleActiva'])->name('categorias.toggle-activa');
    });

    // Productos (administrador para CRUD, todos para ver)
    Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
    // Rutas sin parámetro van ANTES de {producto} para evitar conflictos de routing
    Route::middleware('role:administrador')->group(function () {
        Route::get('/productos/create', [ProductoController::class, 'create'])->name('productos.create');
        Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
    });
    Route::get('/productos/{producto}', [ProductoController::class, 'show'])->name('productos.show');
    Route::middleware('role:administrador')->group(function () {
        Route::get('/productos/{producto}/edit', [ProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
        Route::patch('/productos/{producto}/toggle-activo', [ProductoController::class, 'toggleActivo'])->name('productos.toggle-activo');
        Route::post('/productos/{producto}/precios', [PrecioController::class, 'store'])->name('productos.precios.store');
        Route::delete('/productos/{producto}/precios/{precio}', [PrecioController::class, 'destroy'])->name('productos.precios.destroy');
        Route::put('/productos/{producto}/margen', [PrecioController::class, 'updateMargen'])->name('productos.margen.update');
    });

    // Comunas y tarifas de despacho (administrador)
    Route::middleware('role:administrador')->group(function () {
        Route::resource('comunas', ComunaController::class);
        Route::patch('/comunas/{comuna}/toggle-activa', [ComunaController::class, 'toggleActiva'])->name('comunas.toggle-activa');
        Route::resource('tarifas-despacho', TarifaDespachoController::class);
    });

    // Proveedores (administrador y comprador)
    Route::middleware('role:administrador|comprador')->group(function () {
        Route::resource('proveedores', ProveedorController::class);
        Route::patch('/proveedores/{proveedor}/toggle-activo', [ProveedorController::class, 'toggleActivo'])->name('proveedores.toggle-activo');
    });

    // Compras (administrador y comprador)
    Route::middleware('role:administrador|comprador')->group(function () {
        Route::resource('compras', CompraController::class);
        Route::post('/compras/{compra}/confirmar', [CompraController::class, 'confirmar'])->name('compras.confirmar');
        Route::post('/compras/{compra}/documentos', [CompraController::class, 'subirDocumento'])->name('compras.documentos.store');
        Route::delete('/compras/{compra}/documentos/{documento}', [CompraController::class, 'eliminarDocumento'])->name('compras.documentos.destroy');
        Route::get('/compras/{compra}/documentos/{documento}/download', [CompraController::class, 'descargarDocumento'])->name('compras.documentos.download');
        Route::patch('/compras/{compra}/pago', [CompraController::class, 'actualizarPago'])->name('compras.pago');
        Route::resource('mermas', MermaController::class)->only(['index', 'create', 'store', 'show']);
    });

    // Anulación de compras (solo administrador)
    Route::middleware('role:administrador')->group(function () {
        Route::post('/compras/{compra}/anular', [CompraController::class, 'anular'])->name('compras.anular');
    });

    // Auditoría (solo administrador)
    Route::middleware('role:administrador')->group(function () {
        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
        Route::get('/auditoria/{evento}', [AuditoriaController::class, 'show'])->name('auditoria.show');
    });
});

require __DIR__.'/auth.php';
