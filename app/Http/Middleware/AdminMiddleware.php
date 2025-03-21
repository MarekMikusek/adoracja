<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property-read AuthFactory $auth
 */
class AdminMiddleware
{
    /**
     * The authentication factory instance.
     *
     * @var AuthFactory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  AuthFactory  $auth
     * @return void
     */
    public function __construct(AuthFactory $auth)
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
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->is_admin) {
            return $next($request);
        }

        return redirect('/')->with('error', 'You do not have admin access.');
    }
}
