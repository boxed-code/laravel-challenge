<?php

namespace BoxedCode\Laravel\TwoFactor;

use BoxedCode\Laravel\TwoFactor\Contracts\Challenge;
use BoxedCode\Laravel\TwoFactor\Contracts\AuthManager as ManagerContract;
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
     * Get the session lifetime.
     * 
     * @return integer
     */
    public function getVerificationLifetime()
    {
        return $this->config['lifetimes']['verification'] ?? 0;
    }

    /**
     * Set the challenges within the store.
     * 
     * @param \Illuminate\Support\Collection|array $challenges
     */
    public function setChallenges($challenges)
    {
        $this->session->put('_tfa_session_challenges', $challenges);
    }

    /**
     * Get the challenges from the store.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getChallenges()
    {
        return collect($this->session->get('_tfa_session_challenges', []));
    }

    /**
     * Flush all challenges in the store.
     * 
     * @return void
     */
    public function flushChallenges()
    {
        $this->setChallenges([]);
    }

    /**
     * Log a challenge.
     * 
     * @param  Challenge $challenge
     * @return
     */
    public function logChallenge(Challenge $challenge)
    {
        if (Challenge::PURPOSE_AUTH === $challenge->purpose) {
            $this->setChallenges(
                $this->getChallenges()->push($challenge)
            );
        }
    }

    /**
     * Get th valid challenges from the store.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getValidChallenges()
    {
        return $this->getChallenges()->filter(function($item) {
            $lifetime = $this->getVerificationLifetime();

            if ($lifetime > 0) {
                return $item->verified_at->greaterThan(
                    now()->subSeconds($lifetime)
                );
            }
            return true;
        });
    }

    /**
     * Determine whether the user has authenticates themselves.
     * 
     * @param  array|string  $method
     * @return boolean        
     */
    public function isAuthenticated(string $method = null)
    {
        $methods = (array) $method;

        $challenges = $this->getValidChallenges();

        if ($method) {
            $challenges = $challenges->whereIn(
                'method', $method
            );
        }

        return $challenges->count() >= 1;
    }

    /**
     * Handle the 'Verified' authentication event.
     * 
     * @param  \BoxedCode\Laravel\TwoFactor\Events\Verified $event
     * @return void
     */
    public function handleVerifiedEvent(Verified $event)
    {
        $this->logChallenge($event->challenge);
    }

    /**
     * Handle the 'Logout' authentication event.
     * 
     * @param  \Illuminate\Auth\Events\Logout $event 
     * @return void
     */
    public function handleLogoutEvent(Logout $event)
    {
        $this->flushChallenges();
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