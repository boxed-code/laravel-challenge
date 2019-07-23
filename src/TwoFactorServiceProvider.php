<?php

namespace BoxedCode\Laravel\TwoFactor;

use Illuminate\Support\ServiceProvider;

class TwoFactorServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

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
        $this->loadViewsFrom(
            $this->packagePath('views'), 
            'two_factor'
        );

        $this->publishes(
            [$this->packagePath('config/two_factor.php') => config_path('two_factor.php')], 
            'config'
        );

        $this->publishes(
            [$this->packagePath('migrations') => database_path('migrations')], 
            'migrations'
        );
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

    public function provides()
    {
        return ['auth.tfa'];
    }
}