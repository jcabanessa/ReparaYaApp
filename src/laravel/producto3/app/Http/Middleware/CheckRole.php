<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Verificar si el usuario está autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Comprobar si el rol del usuario está incluido en los permitidos para la ruta
        if (in_array($user->rol, $roles)) {
            return $next($request);
        }

        // Si no tiene permisos, redirigir o abortar
        abort(403, 'Acceso no autorizado.');
    }
}
