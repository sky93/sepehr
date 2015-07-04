<?php

namespace App\Http\Middleware;

use Closure;

class Role
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {

        if ($request->user()->role != $role) {
            return abort(404);
        }

        return $next($request);
    }

}