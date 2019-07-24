<?php

namespace BoxedCode\Laravel\TwoFactor\Http\Traits;

use BoxedCode\Laravel\TwoFactor\AuthenticationBroker;
use BoxedCode\Laravel\TwoFactor\Contracts\Challenge;
use Illuminate\Http\Request;

trait Helpers
{
    /**
     * Refresh the challenge purpose within session storage.
     * 
     * @param  Request $request
     * @return void
     */
    protected function reflashSessionPurpose(Request $request)
    {
        $request->session()->reflash('_tfa_purpose');
    }

    /**
     * Find a custom view for the requested method 
     * and view name or return the default.
     * 
     * @param  string $method
     * @param  string $name 
     * @return \Illuminate\Contracts\View\View
     */
    protected function findView($method, $name)
    {
        $methodViewName = "two_factor::$method.$name";

        if (view()->exists($methodViewName)) {
            $view = view($methodViewName);
        }

        return (isset($view) ? $view : view("two_factor::$name"));
    }

    /**
     * Get the authentication broker instance.
     * 
     * @return \BoxedCode\Laravel\TwoFactor\Contracts\AuthenticationBroker
     */
    protected function broker()
    {
        return app('auth.tfa');
    }
}