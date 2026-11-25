<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class CheckRole {
    public function handle(Request $request, Closure $next, string ...$roles): Response {
        abort_unless(auth()->check(), 401, 'Inicia sesión.');
        abort_unless(in_array(auth()->user()->rol, $roles, true), 403, 'No tienes permiso para acceder.');
        return $next($request);
    }
}
