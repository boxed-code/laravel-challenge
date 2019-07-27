<?php

namespace BoxedCode\Laravel\TwoFactor\Contracts;

use BoxedCode\Laravel\TwoFactor\Events\Verified;
use Illuminate\Auth\Events\Logout;

interface AuthManager
{
    /**
     * Constant representing that no authentication request is in process.
     */
    const NO_AUTH_REQUEST = 'no_auth_request';

    /**
     * Create a new authentication request.
     * 
     * @param  string $purpose
     * @param  string $using  
     * @return \Illuminate\Http\RedirectResponse
     */
    public function requestAuthentication($purpose = null, $using = null);

    /**
     * Determine whether an authentication request has been made.
     * 
     * @return bool
     */
    public function wantsAuthentication();

    /**
     * The 'purpose' of the current authentication request.
     * 
     * @return string|null
     */
    public function wantsAuthenticationFor();

    /**
     * The authentication method desired by the current request.
     * 
     * @return string|null
     */
    public function wantedAuthenticationMethod();

    /**
     * Revoke the current authentication request.
     * 
     * @return void
     */
    public function revokeAuthenticationRequest();

    /**
     * Get the session lifetime.
     * 
     * @return integer
     */
    public function getVerificationLifetime();

    /**
     * Set the challenges within the store.
     * 
     * @param \Illuminate\Support\Collection|array $challenges
     */
    public function setChallenges($challenges);

    /**
     * Get the challenges from the store.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getChallenges();

    /**
     * Flush all challenges in the store.
     * 
     * @return void
     */
    public function flushChallenges();

    /**
     * Log a challenge.
     * 
     * @param  Challenge $challenge
     * @return
     */
    public function logChallenge(Challenge $challenge);

    /**
     * Get th valid challenges from the store.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getValidChallenges();

    /**
     * Determine whether the user has authenticates themselves.
     * 
     * @param  array|string  $method
     * @return boolean        
     */
    public function isAuthenticated(string $method = null);

    /**
     * Handle the 'Verified' authentication event.
     * 
     * @param  \BoxedCode\Laravel\TwoFactor\Events\Verified $event
     * @return void
     */
    public function handleVerifiedEvent(Verified $event);

    /**
     * Handle the 'Logout' authentication event.
     * 
     * @param  \Illuminate\Auth\Events\Logout $event 
     * @return void
     */
    public function handleLogoutEvent(Logout $event);

    /**
     * Get the session store instance.
     * 
     * @return \Illuminate\Contracts\Session\Session
     */
    public function getSessionStore();
}