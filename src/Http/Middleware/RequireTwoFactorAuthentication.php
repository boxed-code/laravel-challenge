<?php

namespace BoxedCode\Laravel\TwoFactor\Http\Middleware;

use BoxedCode\Laravel\TwoFactor\Contracts\AuthManager;
use BoxedCode\Laravel\TwoFactor\Contracts\Challenge;
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
     * @param  string|null  $purpose
     * @return mixed
     */
    public function handle($request, Closure $next, $purpose = null, $lifetime = null)
    {
        $purposes = $this->expandPurposeString($purpose);

        if ($this->shouldAuthenticate($request, $purposes, $lifetime)) {
            session()->put('url.intended', $request->fullUrl());

            if ($request->expectsJson()) {
                throw new AuthenticationException;
            }

            return $this->manager->requestAuthentication(
                !empty($purposes) ? $purposes[0] : Challenge::PURPOSE_AUTH
            );
        }

        return $next($request);
    }

    /**
     * Expand a '|' delimited purpose string to an array.
     * 
     * @param  string|null $purpose
     * @return array|null
     */
    protected function expandPurposeString($purpose)
    {
        if (is_string($purpose)) {
            return explode('|', $purpose);
        }
    }

    /**
     * Ascertain whether we should redirect for authentication.
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  string|array|null $purposes
     * @param  integer|null
     * @return bool
     */
    protected function shouldAuthenticate($request, $purposes, $lifetime)
    {
        return 
            !$this->inExceptArray($request) &&
            $this->manager->shouldEnforceFor($request->user()) &&
            !$this->manager->isAuthenticated($request->user(), null, $purposes, $lifetime);
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
