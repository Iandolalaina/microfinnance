<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Verifie que l'utilisateur connecter a l'un des roles autorises
     * pour accder a la route demande.
     *
     * Utilisation dans les routes : ->middleware('role:admin')
     * ou  plusieurs roles :    ->middleware('role:admin,agent')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, "Vous n'avez pas accès à cette page.");
        }

        return $next($request);
    }
}
