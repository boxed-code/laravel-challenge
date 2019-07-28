<?php

namespace BoxedCode\Laravel\Auth\Challenge\Http\Traits;

use BoxedCode\Laravel\Auth\Challenge\AuthBroker;
use BoxedCode\Laravel\Auth\Challenge\Contracts\Challenge;
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
        $methodViewName = "challenge::$method.$name";

        if (!empty($method) && view()->exists($methodViewName)) {
            $view = $methodViewName;
        }

        $view = (isset($view) ? $view : "challenge::$name");

        return response()
            ->view($view, $data)
            ->header('Cache-Control', 'no-store');
    }

    /**
     * Get the authentication broker instance.
     * 
     * @return \BoxedCode\Laravel\Auth\Challenge\Contracts\AuthBroker
     */
    protected function broker()
    {
        return app('auth.challenge.broker');
    }

    protected function manager()
    {
        return app('auth.challenge');
    }
}