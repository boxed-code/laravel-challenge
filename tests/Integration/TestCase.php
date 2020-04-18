<?php

namespace Tests\Integration;

use Tests\Integration\Support\User;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    public $testUser;

    /**
     * Get package providers.  At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \BoxedCode\Laravel\Auth\Challenge\ChallengeServiceProvider::class,
        ];
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Run the migrations.
        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->artisan('migrate', ['--database' => 'testing']);

        // Create a test user.
        $this->testUser = User::create([
            'name'     => 'Test User',
            'email'    => 'test@user.com',
            'password' => 'password',
        ]);

        \Route::get('/', '\Tests\Integration\Support\Controller@home')->middleware([
            'auth',
            \BoxedCode\Laravel\Auth\Challenge\Http\Middleware\RequireAuthentication::class,
        ]);

        \Route::get('/logout', '\Tests\Integration\Support\Controller@logout')->name('logout');
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Set default user model.
        $app['config']->set('auth.providers.users.model', User::class);
        //$app['config']->set()
    }
}
