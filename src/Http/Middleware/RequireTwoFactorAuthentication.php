<?php

namespace BoxedCode\Laravel\TwoFactor\Http\Middleware;

use BoxedCode\Laravel\TwoFactor\Contracts\AuthManager;
use Closure;

class RequireTwoFactorAuthentication
{
    /**
     * The session manager instance.
     * 
     * @var AuthManager
     */
    protected $manager;

    /**
     * Create a new middleware instance.
     * 
     * @param AuthManager $manager
     */
    public function __construct(AuthManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * The paths that should be excluded from 
     * two factor authentication.
     *
     * @var array
     */
    protected $except = [
        '/tfa',
        '/tfa/error',
        '/tfa/challenge',
        '/tfa/*/verify',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $methods = null)
    {
        if ($this->shouldAuthenticate($request, $methods)) {
            return redirect()->route('tfa');
        }

        return $next($request);
    }

    /**
     * Ascertain whether we should redirect for authentication.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    protected function shouldAuthenticate($request, $methods)
    {
        return !$this->inExceptArray($request) &&
            !$this->manager->isAuthenticated($methods);
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
