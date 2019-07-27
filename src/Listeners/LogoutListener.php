<?php

namespace BoxedCode\Laravel\TwoFactor\Listeners;

use BoxedCode\Laravel\TwoFactor\Contracts\AuthManager;
use Illuminate\Auth\Events\Logout;

class LogoutListener
{
    /**
     * The auth manager instance.
     * 
     * @var \BoxedCode\Laravel\TwoFactor\Contracts\AuthManager
     */
    protected $manager;

    /**
     * Create a new logout listener instance.
     * 
     * @param \BoxedCode\Laravel\TwoFactor\Contracts\AuthManager $manager
     */
    public function __construct(AuthManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Handle the logout event.
     * 
     * @param  \Illuminate\Auth\Events\Logout $event
     * @return void
     */
    public function handle(Logout $event)
    {
        $this->manager->revokeAuthenticationRequest();

        if ($event->user) {
            $this->manager->flushChallenges($event->user);
        }
    }
}