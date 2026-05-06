<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // Validar que usuario esté activo
        if (!$user->activo) {
            Auth::logout();
            return back()->withErrors(['email' => 'Tu cuenta está desactivada. Contacta al administrador.']);
        }

        // Registrar último acceso
        $user->update(['last_login_at' => now()]);

        AuditoriaService::registrar('login', 'autenticacion', 'User', $user->id, "Inicio de sesión exitoso de {$user->nombre_completo}");

        // Si debe cambiar contraseña, redirigir a formulario
        if ($user->must_change_password) {
            return redirect()->route('password.change');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            AuditoriaService::registrar('logout', 'autenticacion', 'User', $user->id, "Cierre de sesión de {$user->nombre_completo}");
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
