<?php

namespace BoxedCode\Laravel\Auth\Challenge;

use BoxedCode\Laravel\Auth\Challenge\Console\Commands\DisenrolUser;
use BoxedCode\Laravel\Auth\Challenge\Console\Commands\EnrolUser;
use BoxedCode\Laravel\Auth\Challenge\Contracts\AuthBroker as BrokerContract;
use BoxedCode\Laravel\Auth\Challenge\Contracts\AuthManager as ManagerContract;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class ChallengeServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            $this->packagePath('config/challenge.php'), 
            'challenge'
        );

        $this->registerMethodManager();

        $this->registerAuthBroker();

        $this->registerAuthManager();
    }

    /**
     * Register the method manager.
     * 
     * @return void
     */
    protected function registerMethodManager()
    {
        $this->app->singleton(Methods\MethodManager::class, function($app) {
            return new Methods\MethodManager($app);
        });

        $this->app->alias(Methods\MethodManager::class, 'auth.challenge.methods');
    }

    /**
     * Register the authentication broker instance.
     * 
     * @return void
     */
    protected function registerAuthBroker()
    {
        $this->app->bind(BrokerContract::class, function($app) {
            $config = config('challenge', []);

            return (new AuthBroker($app['auth.challenge.methods'], $config))
                ->setEventDispatcher($app['events']);
        });

        $this->app->alias(BrokerContract::class, 'auth.challenge.broker');
    }

    protected function registerAuthManager()
    {
        $this->app->singleton(ManagerContract::class, function($app) {
            $config = config('challenge', []);

            return new AuthManager(
                $app['session']->driver(), 
                $config
            );
        });

        $this->app->alias(ManagerContract::class, 'auth.challenge');
    }

    /**
     * Application is booting.
     *
     * @return void
     */
    public function boot()
    {
        // Register the commands.
        if ($this->app->runningInConsole()) {
            $this->commands([
                EnrolUser::class,
                DisenrolUser::class,
            ]);
        }

        // Setup the packages routing.
        $this->setupRouting();

        // Register the event listeners.
        $this->app['events']->listen(
            \Illuminate\Auth\Events\Logout::class, 
            \BoxedCode\Laravel\Auth\Challenge\Listeners\LogoutListener::class
        );

        // Register the package views.
        $this->loadViewsFrom($this->packagePath('views'), 'challenge');

        // Register the package configuration to publish.
        $this->publishes(
            [$this->packagePath('config/challenge.php') => config_path('challenge.php')], 
            'config'
        );

        // Register the migrations to publish.
        $this->loadMigrationsFrom($this->packagePath('migrations'));
    }

    /**
     * Setup the packages routing.
     * 
     * @return void
     */
    protected function setupRouting()
    {
        $registerRoutes = function() { 
            $this->loadRoutesFrom(
                $this->packagePath('src/Http/routes.php')
            ); 
        };

        Router::macro('challenge', function() use ($registerRoutes) {
            $registerRoutes();
        });


        // Register the routes automatically if required.
        if ($this->app['config']->get('challenge.routing.register') === true) {
            $registerRoutes();
        }
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