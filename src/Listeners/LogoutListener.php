<?php

namespace BoxedCode\Laravel\Auth\Challenge\Listeners;

use BoxedCode\Laravel\Auth\Challenge\Contracts\AuthManager;
use Illuminate\Auth\Events\Logout;

class LogoutListener
{
    /**
     * The auth manager instance.
     * 
     * @var \BoxedCode\Laravel\Auth\Challenge\Contracts\AuthManager
     */
    protected $manager;

    /**
     * Create a new logout listener instance.
     * 
     * @param \BoxedCode\Laravel\Auth\Challenge\Contracts\AuthManager $manager
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