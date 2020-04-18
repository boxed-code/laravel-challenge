<?php

namespace Tests\Integration;

use BoxedCode\Laravel\Auth\Challenge\Contracts\Challenge;
use Illuminate\Encryption\Encrypter;

class EnforcingMiddlewareTestCase extends TestCase
{
    protected $mfa;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mfa = app('auth.challenge.broker');
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Set the default enforcing mode to all.
        $app['config']->set('challenge.enforce', 'all');

        // Add & enable the test method.
        $app['config']->set('challenge.methods.test', [
            'label' => 'Test MFA Method',
            'method' => 'test',
        ]);

        $app['config']->set('challenge.enabled', ['test', 'email']);

        // Add the custom creator.
        $app['auth.challenge.methods']->extend('test', function($name, $config) {
            return new Support\TestMethod($name, $config);
        });

        // Set the application encryption key.
        $app['config']->set('app.key', 'base64:'.base64_encode(
            Encrypter::generateKey($app['config']['app.cipher'])
        ));
    }

    public function testDefaultFlow()
    {
        // Enrol the user into the test & e-mail verification methods.
        $this->mfa->withoutDispatchingChallenges()->beginEnrolment($this->testUser, 'test');
        $this->mfa->verify($this->testUser, 'test', []);

        $this->mfa->withoutDispatchingChallenges()->beginEnrolment($this->testUser, 'email');
        $this->mfa->enrol($this->testUser, 'email');
        $this->mfa->verify($this->testUser, 'email', []);

        app('auth.challenge')->flushChallenges($this->testUser);

        // Trigger auth request as an unverified user.
        $response = $this->actingAs($this->testUser)->get('/');
        $response->assertRedirect('http://localhost/tfa');

        // Check we're sent to the MFA method selection page.
        $response = $this->actingAs($this->testUser)->get('/tfa');
        $response->assertSeeTextInOrder([
            'E-mail',
            'Test MFA Method'
        ]);

        // POST the selection page response.
        $response = $this->actingAs($this->testUser)->post('/tfa/dispatch', [
            'method' => 'test'
        ]);
        $response->assertRedirect('http://localhost/tfa/test/verify');

        // Check the verification page.
        $response = $this->actingAs($this->testUser)->get('/tfa/test/verify');
        $response->assertSeeText('Enter the code you have recieved via Test MFA Method below');

        // POST the verification response.
        $response = $this->actingAs($this->testUser)->post('/tfa/test/verify', [
            'code' => $this->testUser->challenges()->first()->state['code']
        ]);
        $response->assertRedirect('http://localhost');

        // Check we can see the homepage.
        $this->testUser->unsetRelation('challenges');
        $response = $this->actingAs($this->testUser)->get('/');
        $response->assertStatus(200);
        $response->assertSeeText('Hello Test User!');
    }

    public function testDefaultPurposeDenied()
    {
        $response = $this->actingAs($this->testUser)->get('/');

        $response->assertRedirect('http://localhost/tfa');
    }

    public function testDefaultPurposeAuthorized()
    {
        // Enrol the user, create a MFA challenge request and verify it.
        $this->mfa->beginEnrolment($this->testUser, 'test');
        $response = $this->mfa->challenge($this->testUser, 'test', Challenge::PURPOSE_AUTH);
        $this->mfa->verify($this->testUser, 'test', ['code' => $response->challenge->state['code']]);

        $response = $this->actingAs($this->testUser)->get('/');

        $response->assertStatus(200);
        $response->assertSee('Hello Test User!');
    }
}