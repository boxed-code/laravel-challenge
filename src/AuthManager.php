<?php

namespace BoxedCode\Laravel\TwoFactor;

use BoxedCode\Laravel\TwoFactor\Contracts\AuthManager as ManagerContract;
use BoxedCode\Laravel\TwoFactor\Contracts\Challenge;
use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use BoxedCode\Laravel\TwoFactor\Events\Verified;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

class AuthManager implements ManagerContract
{
    /**
     * Session store instance.
     * 
     * @var \Illuminate\Contracts\Session\Session
     */
    protected $session;

    /**
     * Configuration.
     * 
     * @var array
     */
    protected $config;

    /**
     * Create a new session manager instance.
     * 
     * @param Session $session
     * @param array   $config 
     */
    public function __construct(Session $session, array $config = [])
    {
        $this->session = $session;

        $this->config = $config;
    }

    /**
     * Create a new authentication request.
     * 
     * @param  string $purpose
     * @param  string $using  
     * @return \Illuminate\Http\RedirectResponse
     */
    public function requestAuthentication($purpose = null, $using = null)
    {
        $this->session->put('_tfa_auth_request', [
            'purpose' => $purpose, 
            'method' => $using, 
            'requested_at' => now(),
        ]);

        return redirect()->route('tfa');
    }

    /**
     * Determine whether an authentication request has been made.
     * 
     * @return bool
     */
    public function wantsAuthentication()
    {
        return $this->getAuthRequest() ? true : false;
    }

    /**
     * The 'purpose' of the current authentication request.
     * 
     * @return string|null
     */
    public function wantsAuthenticationFor()
    {
        if ($this->wantsAuthentication()) {
            return $this->getAuthRequest()['purpose'];
        }
    }

    /**
     * The authentication method desired by the current request.
     * 
     * @return string|null
     */
    public function wantedAuthenticationMethod()
    {
        if ($this->wantsAuthentication()) {
            return $this->getAuthRequest()['method'] ?? 'default';
        }
    }

    /**
     * Revoke the current authentication request.
     * 
     * @return void
     */
    public function revokeAuthenticationRequest()
    {
        $this->session->forget('_tfa_auth_request');
    }

    /**
     * Determine whether the user has authenticates themselves.
     *
     * @param  \BoxedCode\Laravel\TwoFactor\Contracts\Challengeable $user
     * @param  array|string  $method
     * @return boolean        
     */
    public function isAuthenticated(Challengeable $user, string $method = null)
    {
        $methods = (array) $method;

        $challenges = $this->getValidChallengesFor($user);

        if ($method) {
            $challenges = $challenges->whereIn(
                'method', $method
            );
        }

        return $challenges->count() >= 1;
    }

    /**
     * Flush all challenges for the current user.
     * 
     * @param  Challengeable $user
     * @return void    
     */
    public function flushChallenges(Challengeable $user)
    {
        $user->challenges()->delete();
    }

    /**
     * Get the length of time before another challenge/verification 
     * sequence is required.
     * 
     * @return integer
     */
    protected function getVerificationLifetime()
    {
        return $this->config['lifetimes']['verification'] ?? 0;
    }

    /**
     * Get the valid challenges from the store.
     *
     * @param  \BoxedCode\Laravel\TwoFactor\Contracts\Challengeable $user
     * @return \Illuminate\Support\Collection
     */
   protected function getValidChallengesFor(Challengeable $user)
    {
        return $user->challenges->filter(function($item) {
            $lifetime = $this->getVerificationLifetime();

            if ($lifetime > 0) {
                return $item->verified_at && $item->verified_at->greaterThan(
                    now()->subSeconds($lifetime)
                );
            }
            return true;
        });
    }

    /**
     * Get the session store instance.
     * 
     * @return \Illuminate\Contracts\Session\Session
     */
    public function getSessionStore()
    {
        return $this->session;
    }

    /**
     * Get the current authentication request state.
     * 
     * @return array|null
     */
    protected function getAuthRequest()
    {
        $key = '_tfa_auth_request';

        if ($request = $this->session->get($key)) {
            return $request;
        }
    }
}