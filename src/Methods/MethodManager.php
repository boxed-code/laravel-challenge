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

        $methodName = $config['method'];

        if (isset($this->methods[$name])) {
            return $this->methods[$name];
        } elseif (isset($this->customCreators[$name])) {
            return $this->methods[$name] = $this->callCustomCreator($config);
        } else {
            $methodMethod = 'create'.ucfirst($methodName).'Method';

            if (method_exists($this, $methodMethod)) {
                return $this->methods[$name] = $this->{$methodMethod}($config);
            } else {
                throw new InvalidArgumentException(
                    "Method [{$name}] is not supported."
                );
            }
        }
    }

    public function getEnabledMethods()
    {
        return $this->app['config']['two_factor.enabled'];
    }

    public function formatMethodLabel($provider_name)
    {
        $search = ['-', '_'];

        $titleCase = Str::title($provider_name);

        return str_replace($search, ' ', $titleCase);
    }

    public function enabledMethodList()
    {
        $methodNames = $this->methods->getEnabledMethods();

        return collect($methodNames)->mapWithKeys(function($value) {
            return [$value => $this->formatMethodLabel($value)];
        });
    }

    public function validateMethodName($provider_name)
    {
        return $this->enabledMethodList()->exists($provider_name);
    }

    protected function createNotificationMethod($config)
    {
        return new NotificationMethod($config);
    }

    /**
     * Get the default method name.
     *
     * @return string
     */
    public function getDefaultMethod()
    {
        return array_shift($this->app['config']['two_factor.enabled']);
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