<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccessLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $level = 0): Response
    {
        $user = Auth::user();

        if($level>0){
            if(!$user||$user->level < $level) {
                // Informamos que el recurso no existe como medida de seguridad
                abort(404, "Recurso no encontrado");
            }
        }

        return $next($request);
    }
}
