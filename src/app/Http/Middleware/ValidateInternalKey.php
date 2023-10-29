<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateInternalKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->header('X-SCDS-INTERNAL-KEY') === config('app.internal_key'), 403);

        return $next($request);
    }
}
