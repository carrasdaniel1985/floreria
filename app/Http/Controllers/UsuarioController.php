<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\User;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::with(['roles', 'sucursales'])
            ->when($request->buscar, fn($q, $v) =>
                $q->where('nombre', 'ilike', "%$v%")
                  ->orWhere('apellido', 'ilike', "%$v%")
                  ->orWhere('email', 'ilike', "%$v%")
            )
            ->when($request->rol, fn($q, $v) => $q->whereHas('roles', fn($r) => $r->where('name', $v)))
            ->when($request->filled('activo'), fn($q) => $q->where('activo', $request->boolean('activo')))
            ->orderBy('nombre');

        return Inertia::render('Usuarios/Index', [
            'usuarios'  => $query->paginate(20)->withQueryString(),
            'roles'     => Role::all(),
            'sucursales' => Sucursal::where('activa', true)->get(),
            'filtros'   => $request->only(['buscar', 'rol', 'activo']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Usuarios/Form', [
            'roles'     => Role::all(),
            'sucursales' => Sucursal::where('activa', true)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'      => ['required', 'string', 'max:100'],
            'apellido'    => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'unique:users'],
            'telefono'    => ['nullable', 'string', 'max:20'],
            'roles'       => ['required', 'array', 'min:1'],
            'roles.*'     => ['exists:roles,name'],
            'sucursales'  => ['required', 'array', 'min:1'],
            'sucursales.*' => ['exists:sucursales,id'],
            'activo'      => ['boolean'],
            'password'    => ['required', Password::min(6)],
        ]);

        $user = User::create([
            'nombre'               => $validated['nombre'],
            'apellido'             => $validated['apellido'],
            'email'                => $validated['email'],
            'telefono'             => $validated['telefono'] ?? null,
            'password'             => Hash::make($validated['password']),
            'must_change_password' => true,
            'activo'               => $validated['activo'] ?? true,
        ]);

        $user->syncRoles($validated['roles']);
        $user->sucursales()->sync($validated['sucursales']);

        AuditoriaService::registrar('crear', 'usuarios', 'User', $user->id, "Usuario {$user->nombre_completo} creado");

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): Response
    {
        return Inertia::render('Usuarios/Form', [
            'usuario'    => $usuario->load(['roles', 'sucursales']),
            'roles'      => Role::all(),
            'sucursales' => Sucursal::where('activa', true)->get(),
        ]);
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'      => ['required', 'string', 'max:100'],
            'apellido'    => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', "unique:users,email,{$usuario->id}"],
            'telefono'    => ['nullable', 'string', 'max:20'],
            'roles'       => ['required', 'array', 'min:1'],
            'roles.*'     => ['exists:roles,name'],
            'sucursales'  => ['required', 'array', 'min:1'],
            'sucursales.*' => ['exists:sucursales,id'],
            'activo'      => ['boolean'],
        ]);

        $antes = $usuario->only(['nombre', 'apellido', 'email', 'telefono', 'activo']);
        $usuario->update($validated);
        $usuario->syncRoles($validated['roles']);
        $usuario->sucursales()->sync($validated['sucursales']);

        $cambios = AuditoriaService::diffCambios($antes, $usuario->fresh()->only(array_keys($antes)));
        AuditoriaService::registrar('editar', 'usuarios', 'User', $usuario->id, "Usuario {$usuario->nombre_completo} editado", null, $cambios);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleActivo(User $usuario): RedirectResponse
    {
        $nuevoEstado = !$usuario->activo;
        $usuario->update(['activo' => $nuevoEstado]);

        AuditoriaService::registrar(
            $nuevoEstado ? 'activar' : 'desactivar',
            'usuarios', 'User', $usuario->id,
            "Usuario {$usuario->nombre_completo} " . ($nuevoEstado ? 'activado' : 'desactivado')
        );

        return back()->with('success', 'Estado actualizado.');
    }

    public function resetPassword(User $usuario): RedirectResponse
    {
        $nuevaPassword = Str::random(10);
        $usuario->update([
            'password'             => Hash::make($nuevaPassword),
            'must_change_password' => true,
        ]);

        AuditoriaService::registrar('reset_password', 'usuarios', 'User', $usuario->id, "Contraseña reseteada para {$usuario->nombre_completo}");

        return back()->with('success', "Contraseña temporal: {$nuevaPassword}");
    }

    public function show(User $usuario): Response
    {
        return Inertia::render('Usuarios/Show', [
            'usuario' => $usuario->load(['roles', 'sucursales']),
        ]);
    }

    public function destroy(User $usuario): RedirectResponse
    {
        // Solo desactivar, nunca eliminar físicamente
        $usuario->update(['activo' => false]);
        AuditoriaService::registrar('desactivar', 'usuarios', 'User', $usuario->id, "Usuario {$usuario->nombre_completo} desactivado vía destroy");
        return redirect()->route('usuarios.index')->with('success', 'Usuario desactivado.');
    }
}
