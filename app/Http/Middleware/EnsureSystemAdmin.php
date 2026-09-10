<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            !$request->user() ||
            !$request->user()->hasSystemRole('super-admin')
        ) {
            abort(403);
        }

        return $next($request);
    }
}
