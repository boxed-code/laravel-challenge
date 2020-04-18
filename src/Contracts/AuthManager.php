<?php

namespace BoxedCode\Laravel\Auth\Challenge\Contracts;

use BoxedCode\Laravel\Auth\Challenge\Events\Verified;

interface AuthManager
{
    /**
     * Constant representing that no authentication request is in process.
     */
    const NO_AUTH_REQUEST = 'no_auth_request';

    /**
     * Create a new authentication request.
     *
     * @param string $purpose
     * @param string $using
     *
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
     * @param \BoxedCode\Laravel\Auth\Challenge\Contracts\Challengeable $user
     * @param array|string|null                                         $method
     * @param array|string|null                                         $purpose
     * @param int|null                                                  $lifetime
     *
     * @return bool
     */
    public function isAuthenticated(
        Challengeable $user,
        $method = null,
        $purpose = null,
        $lifetime = null
    );

    /**
     * Flush the verified challenges for the current user.
     *
     * @param Challengeable $user
     *
     * @return void
     */
    public function flushChallenges(Challengeable $user);

    /**
     * Determine whether we should enforce two factor authentication for the user.
     *
     * @param Challengeable $user
     *
     * @return bool
     */
    public function shouldEnforceFor(Challengeable $user);

    /**
     * Get the model associate with an authentication guard.
     *
     * @param string|null $guard
     *
     * @return string
     */
    public function getModelForGuard($guard = null);

    /**
     * Get the session store instance.
     *
     * @return \Illuminate\Contracts\Session\Session
     */
    public function getSessionStore();
}
