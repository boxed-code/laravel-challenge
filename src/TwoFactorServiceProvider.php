<?php

namespace BoxedCode\Laravel\TwoFactor;

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

        $this->registerAuthenticationBroker();
    }

    /**
     * Register the authentication broker instance.
     * 
     * @return void
     */
    protected function registerAuthenticationBroker()
    {
        $this->app->bind(AuthenticationBroker::class, function($app) {
            $manager = new Methods\MethodManager($app);

            $config = config('two_factor', []);

            return (new AuthenticationBroker($manager, $config))
                ->setEventDispatcher($app['events']);
        });

        $this->app->alias(AuthenticationBroker::class, 'auth.tfa');
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