<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole {

    public function handle(Request $request, Closure $next, ...$roles): Response {
        
        if (!$request->user()) {

            return redirect('/login');

        }


        $allowedRoles = [];

        foreach ($roles as $role) {

            $allowedRoles = array_merge($allowedRoles, explode(',', $role));

        }


        $allowedRoles = array_filter(array_unique($allowedRoles));


        if (in_array($request->user()->role, $allowedRoles)) {

            return $next($request);

        }

        abort(403, 'Akses ditolak. Role yang diperlukan: ' . implode(', ', $allowedRoles) . '. Role Anda: ' . $request->user()->role);

    }

}
