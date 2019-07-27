<?php

namespace BoxedCode\Laravel\TwoFactor\Http\Traits;

use BoxedCode\Laravel\TwoFactor\AuthBroker;
use BoxedCode\Laravel\TwoFactor\Contracts\Challenge;
use Illuminate\Http\Request;

trait Helpers
{
    /**
     * Find a custom view for the requested method 
     * and view name or return the default.
     * 
     * @param  string $name 
     * @param  string|null $method
     * @param  array $data
     * @return \Illuminate\Contracts\View\View
     */
    protected function view($name, $method = null, array $data = [])
    {
        $methodViewName = "two_factor::$method.$name";

        if (!empty($method) && view()->exists($methodViewName)) {
            $view = $methodViewName;
        }

        $view = (isset($view) ? $view : "two_factor::$name");

        return response()
            ->view($view, $data)
            ->header('Cache-Control', 'no-store');
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

    protected function manager()
    {
        return app('auth.tfa');
    }
}