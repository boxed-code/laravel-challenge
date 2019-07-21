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

        $this->app->bind(TokenRepository::class, function($app) {
            return new DatabaseTokenRepository(
                $app['db']->connection(),
                config('two_factor.tokens.table_name')
            );
        });

        $this->app->bind(EnrollmentRepository::class, function($app) {
            return new EloquentEnrollmentRepository(
                new Models\Enrollment
            );
        });

        $this->app->bind(AuthenticationBroker::class, function($app) {
            $providerManager = new Providers\ProviderManager($app);

            $tokens = $app->make(TokenRepository::class);

            $enrollment = $app->make(EnrollmentRepository::class);

            $config = $app['config']->get('two_factor');

            return (new AuthenticationBroker(
                $providerManager,
                $tokens,
                $enrollment,
                $config
            ))->setEventDispatcher($app['events']);
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