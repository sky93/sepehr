<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
        //We won't check CSRF token for these routes

        if ($request->is('downloads')  || $request->is('tools/status'))
        {
            return $next($request);
        }
        else
        {
            return parent::handle($request, $next);
        }

	}

}
