<?php

namespace BoxedCode\Laravel\Auth\Challenge;

use BoxedCode\Laravel\Auth\Challenge\Contracts\AuthManager as ManagerContract;
use BoxedCode\Laravel\Auth\Challenge\Contracts\Challenge;
use BoxedCode\Laravel\Auth\Challenge\Contracts\Challengeable;
use BoxedCode\Laravel\Auth\Challenge\Events\Verified;
use Illuminate\Contracts\Session\Session;

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
     * @param string $purpose
     * @param string $using
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function requestAuthentication($purpose = null, $using = null)
    {
        $this->session->put('_challenge_auth_request', [
            'purpose'      => $purpose,
            'method'       => $using,
            'requested_at' => now(),
        ]);

        return redirect()->route('challenge');
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
        $this->session->forget('_challenge_auth_request');
    }

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
    ) {
        $methods = (array) $method;

        $purposes = (array) $purpose;

        $challenges = $this->getVerifiedChallengesFor($user, $lifetime);

        if ($methods) {
            $challenges = $challenges->whereIn(
                'method',
                $methods
            );
        }

        if ($purposes) {
            $challenges = $challenges->whereIn(
                'purpose',
                $purposes
            );
        }

        return $challenges->count() >= 1;
    }

    /**
     * Flush the verified challenges for the current user.
     *
     * @param Challengeable $user
     *
     * @return void
     */
    public function flushChallenges(Challengeable $user)
    {
        $user->challenges()->whereNotNull('verified_at')->delete();
    }

    /**
     * Determine whether we should enforce two factor authentication for the user.
     *
     * @param Challengeable $user
     *
     * @return bool
     */
    public function shouldEnforceFor(Challengeable $user)
    {
        $enforcingStatus = $this->config['enforce'];

        return
            'all' === $enforcingStatus ||
            'enrolled' === $enforcingStatus && $user->enrolments()->enrolled()->count() > 0;
    }

    /**
     * Get the model associate with an authentication guard.
     *
     * @param string|null $guard
     *
     * @return string
     */
    public function getModelForGuard($guard = null)
    {
        if (empty($guard)) {
            $guard = config('auth.defaults.guard');
        }

        return collect(config('auth.guards'))
            ->map(function ($guard) {
                if (!isset($guard['provider'])) {
                    return;
                }

                return config("auth.providers.{$guard['provider']}.model");
            })->get($guard);
    }

    /**
     * Get the length of time before another challenge/verification
     * sequence is required.
     *
     * @return int
     */
    protected function getVerificationLifetime()
    {
        return $this->config['lifetimes']['verification'] ?? 0;
    }

    /**
     * Get the verified challenges from the store.
     *
     * @param  \BoxedCode\Laravel\Auth\Challenge\Contracts\Challengeable $user
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getVerifiedChallengesFor(Challengeable $user, $lifetime = null)
    {
        return $user->challenges->filter(function ($item) use ($lifetime) {
            $lifetime = $lifetime ?: $this->getVerificationLifetime();

            if ($lifetime > 0) {
                return $item->verified_at && $item->verified_at->greaterThan(
                    now()->subSeconds($lifetime)
                );
            }

            return $item->verified_at ? true : false;
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
        $key = '_challenge_auth_request';

        if ($request = $this->session->get($key)) {
            return $request;
        }
    }
}
