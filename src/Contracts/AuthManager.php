<?php

namespace BoxedCode\Laravel\TwoFactor\Contracts;

use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
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
     * Determine whether the user has authenticates themselves.
     *
     * @param  \BoxedCode\Laravel\TwoFactor\Contracts\Challengeable $user
     * @param  array|string  $method
     * @return boolean        
     */
    public function isAuthenticated(Challengeable $user, $method = null);

    /**
     * Flush the verified challenges for the current user.
     * 
     * @param  Challengeable $user
     * @return void    
     */
    public function flushChallenges(Challengeable $user);

    /**
     * Get the session store instance.
     * 
     * @return \Illuminate\Contracts\Session\Session
     */
    public function getSessionStore();
}