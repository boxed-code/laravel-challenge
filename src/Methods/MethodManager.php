<?php

namespace BoxedCode\Laravel\TwoFactor\Methods;

use InvalidArgumentException;
use Illuminate\Support\Str;

class MethodManager
{
   /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The array of created "methods".
     *
     * @var array
     */
    protected $methods = [];

    /**
     * The registered custom method creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new PasswordBroker manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Attempt to get the method from the local cache.
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function method($name = null)
    {
        $name = $name ?: $this->getDefaultMethod();

        return $this->resolve($name);
    }

    /**
     * Resolve the given method.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Cache\Repository
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Method [{$name}] is not defined.");
        }

        if (!$this->validateMethodName($name)) {
            throw new InvalidArgumentException("Method [{$name}] is not enabled.");
        }

        $methodName = $config['method'];

        if (isset($this->methods[$name])) {
            return $this->methods[$name];
        } elseif (isset($this->customCreators[$name])) {
            return $this->methods[$name] = $this->callCustomCreator($name, $config);
        } else {
            $methodMethod = 'create'.Str::studly($methodName).'Method';

            if (method_exists($this, $methodMethod)) {
                return $this->methods[$name] = $this->{$methodMethod}($name, $config);
            } else {
                throw new InvalidArgumentException(
                    "Method [{$name}] is not supported."
                );
            }
        }
    }

    /**
     * Get the array of enabled authentication methods.
     * 
     * @return array
     */
    public function getEnabledMethods()
    {
        return $this->app['config']['two_factor.enabled'];
    }

    /**
     * Get a formatted list of the enabled methods.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function enabledMethodList()
    {
        $methodNames = $this->getEnabledMethods();

        return collect($methodNames)->mapWithKeys(function($value) {
            return [$value => MethodNameFormatter::toLabel($value)];
        });
    }

    /**
     * Validate the supplied method name actually exists.
     * 
     * @param  string $method
     * @return bool
     */
    public function validateMethodName($method)
    {
        return $this->enabledMethodList()->has($method);
    }

    /**
     * Create a notification method instance.
     * 
     * @param  array $config
     * @return \BoxedCode\Laravel\TwoFactor\Contracts\Method
     */
    protected function createNotificationMethod($name, $config)
    {
        return new NotificationMethod($name, $config);
    }

    protected function createGoogleAuthenticatorMethod($name, $config)
    {
        return new GoogleAuthenticatorMethod($name, $config);
    }

    /**
     * Get the default method name.
     *
     * @return string
     */
    public function getDefaultMethod()
    {
        $enabled = $this->getEnabledMethods();

        return array_shift($enabled);
    }

    /**
     * Get the password broker configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["two_factor.methods.{$name}"];
    }
}