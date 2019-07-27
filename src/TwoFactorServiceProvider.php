<?php

namespace BoxedCode\Laravel\TwoFactor;

use BoxedCode\Laravel\TwoFactor\Contracts\AuthBroker as BrokerContract;
use BoxedCode\Laravel\TwoFactor\Contracts\AuthManager as ManagerContract;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class TwoFactorServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            $this->packagePath('config/two_factor.php'), 
            'two_factor'
        );

        $this->registerAuthBroker();

        $this->registerAuthManager();
    }

    /**
     * Register the authentication broker instance.
     * 
     * @return void
     */
    protected function registerAuthBroker()
    {
        $this->app->bind(BrokerContract::class, function($app) {
            $manager = new Methods\MethodManager($app);

            $config = config('two_factor', []);

            return (new AuthBroker($manager, $config))
                ->setEventDispatcher($app['events']);
        });

        $this->app->alias(BrokerContract::class, 'auth.tfa.broker');
    }

    protected function registerAuthManager()
    {
        $this->app->singleton(ManagerContract::class, function($app) {
            $config = config('two_factor', []);

            return new AuthManager(
                $app['session']->driver(), 
                $config
            );
        });

        $this->app->alias(ManagerContract::class, 'auth.tfa');
    }

    /**
     * Application is booting.
     *
     * @return void
     */
    public function boot()
    {
        // Register the packages route macros.
        $this->registerRouteMacro();

        // Register the event listeners.
        $this->app['events']->listen(
            \Illuminate\Auth\Events\Logout::class, 
            \BoxedCode\Laravel\TwoFactor\Listeners\LogoutListener::class
        );

        // Register the package views.
        $this->loadViewsFrom($this->packagePath('views'), 'two_factor');

        // Register the package configuration to publish.
        $this->publishes(
            [$this->packagePath('config/two_factor.php') => config_path('two_factor.php')], 
            'config'
        );

        // Register the migrations to publish.
        $this->publishes(
            [$this->packagePath('migrations') => database_path('migrations')], 
            'migrations'
        );
    }

    /**
     * Register the router macro.
     * 
     * @return void
     */
    protected function registerRouteMacro()
    {
        $registerRoutes = function() { 
            $this->loadRoutesFrom(
                $this->packagePath('src/Http/routes.php')
            ); 
        };

        Router::macro('tfa', function() use ($registerRoutes) {
            $registerRoutes();
        });
    }

    /**
     * Loads a path relative to the package base directory.
     *
     * @param string $path
     * @return string
     */
    protected function packagePath($path = '')
    {
        return sprintf('%s/../%s', __DIR__, $path);
    }
}