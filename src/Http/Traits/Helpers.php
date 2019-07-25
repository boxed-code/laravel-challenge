<?php

namespace BoxedCode\Laravel\TwoFactor\Http\Traits;

use BoxedCode\Laravel\TwoFactor\AuthBroker;
use BoxedCode\Laravel\TwoFactor\Contracts\Challenge;
use Illuminate\Http\Request;

trait Helpers
{
    /**
     * Get the current authentication purpose.
     * 
     * @param  Request $request
     * @return void
     */
    protected function getAuthenticationPurpose(Request $request)
    {
        return $request->session()->get(
            '_tfa_purpose', Challenge::PURPOSE_AUTH
        );
    }

    /**
     * Set the current authentication purpose.
     * 
     * @param Request $request
     * @param void
     */
    protected function setAuthenticationPurpose(Request $request, $purpose)
    {
        $request->session()->put(
            '_tfa_purpose', $purpose
        );
    }

    /**
     * Flush the current authentication purpose.
     * 
     * @param  Request $request
     * @return void
     */
    protected function flushAuthenticationPurpose(Request $request)
    {
        $request->session()->forget('_tfa_purpose');
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
     * @return \BoxedCode\Laravel\TwoFactor\Contracts\AuthBroker
     */
    protected function broker()
    {
        return app('auth.tfa.broker');
    }
}