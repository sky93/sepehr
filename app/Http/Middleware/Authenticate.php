<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Auth;

class Authenticate {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($this->auth->guest()){
			if ($request->ajax()){
				return response('Unauthorized.', 401);
			}
			else{
				return redirect()->guest('login');
			}
		}else{
            $role = 'global';
            $actions = $request->route()->getAction();
            if(array_key_exists('role', $actions)) {
                $role = $actions['role'];
                if ($role !== 'global' &&  Auth::user()->role != $role){
                    if ($request->ajax()){
                        return response('Not Found.', 404);
                    }
                    else{
                        return abort(404);
                    }

                }
            }
        }
		return $next($request);
	}

}
