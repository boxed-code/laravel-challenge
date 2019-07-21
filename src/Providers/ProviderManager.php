<?php

namespace BoxedCode\Laravel\TwoFactor\Providers;

use InvalidArgumentException;
use Illuminate\Support\Str;

class ProviderManager
{
   /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $providers = [];

    /**
     * The registered custom driver creators.
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
     * Attempt to get the provider from the local cache.
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function provider($name = null)
    {
        $name = $name ?: $this->getDefaultProvider();

        return $this->resolve($name);
    }

    /**
     * Resolve the given provider.
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
            throw new InvalidArgumentException("Provider [{$name}] is not defined.");
        }

        $providerName = $config['provider'];

        if (isset($this->providers[$name])) {
            return $this->providers[$name];
        } elseif (isset($this->customCreators[$name])) {
            return $this->providers[$name] = $this->callCustomCreator($config);
        } else {
            $providerMethod = 'create'.ucfirst($providerName).'Provider';

            if (method_exists($this, $providerMethod)) {
                return $this->providers[$name] = $this->{$providerMethod}($config);
            } else {
                throw new InvalidArgumentException(
                    "Provider [{$name}] is not supported."
                );
            }
        }
    }

    public function getEnabledProviders()
    {
        $enabledNames = $this->app['config']['two_factor.enabled'];

        $providers = [];

        foreach  ($enabledNames as $name) {
            $providers[$name] = str_replace(['-', '_'], ' ', Str::title($name));
        }

        return $providers;
    }

    protected function createNotificationProvider($config)
    {
        return new NotificationProvider($config);
    }

    /**
     * Get the default provider name.
     *
     * @return string
     */
    public function getDefaultProvider()
    {
        return $this->app['config']['two_factor.default'];
    }

    /**
     * Get the password broker configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["two_factor.providers.{$name}"];
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->broker()->{$method}(...$parameters);
    }
}