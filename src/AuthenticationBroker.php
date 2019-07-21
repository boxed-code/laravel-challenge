<?php

// @todo Fix enrollment spelling mistake, should be enrolment!
// @todo change DB token for code
// 
namespace BoxedCode\Laravel\TwoFactor;

use BoxedCode\Laravel\TwoFactor\EnrollmentRepository as Enrollment;
use BoxedCode\Laravel\TwoFactor\Providers\ProviderManager as Providers;
use BoxedCode\Laravel\TwoFactor\TokenRepository as Tokens;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Support\Str;

class AuthenticationBroker
{
    const USER_NOT_ENROLLED = 'user_not_enrolled',
          INVALID_PROVIDER = 'invalid_provider',
          INVALID_TOKEN = 'invalid_token',
          INVALID_CODE = 'invalid_code',
          CODE_VERIFIED = 'code_verified',
          USER_CANNOT_ENROL = 'user_cannot_enrol',
          USER_CHALLENGED = 'challenged',
          USER_ENROLED = 'user_enroled',
          PROVIDER_REQUIRES_SETUP = 'provider_requires_setup';

    protected $providers;

    protected $tokens; 

    protected $enrollment;

    protected $config;

    protected $dispatcher;

    public function __construct(Providers $providers, Tokens $tokens, Enrollment $enrollment, array $config)
    {
        $this->providers = $providers;

        $this->tokens = $tokens;

        $this->enrollment = $enrollment;

        $this->config = $config;
    }

    public function defaultProvider(Challengeable $user)
    {
        $default = $this->providers->getDefaultProvider();

        $userProviders = $this->enrolledProviders($user);

        $defaultProviders = array_filter($userProviders, function($item) use ($default) {
            return $default === $item['name'];
        });

        return count($defaultProviders) > 0 ? 
            $defaultProviders['name'] : $default;
    }

    public function enabledProviders()
    {
        return $this->providers->getEnabledProviders();
    }

    public function enrolledProviders(Challengeable $user)
    {
        $enrollments = $this->enrollment->enrollments($user->id);

        $list = [];

        foreach ($enrollments as $enrollment) {
            $label = str_replace(
                ['-', '_'], 
                ' ', 
                Str::title($enrollment['provider'])
            );

            $list[$enrollment['provider']] = $label;
        }

       return $list;
    }

    public function canEnroll(Challengeable $user, $provider_name)
    {
        return (
            $this->validProviderName($provider_name) &&
            !$this->enrolled($user, $provider_name) &&
            $user->canEnrollInTwoFactorAuth($provider_name)
        );
    }

    public function requiresEnrolmentChallenge($provider_name)
    {
        $provider = $this->provider($provider_name);

        return $provider->requiresEnrolmentChallenge();
    }

    public function requiresSetup($provider_name)
    {
        $provider = $this->provider($provider_name);

        return $provider->requiresEnrolmentSetup();
    }

    public function enrolling(Challengeable $user, $provider_name)
    {
        return count($this->tokens->getByChallengeableId($user->id, true)) > 0;
    }

    public function enrolled(Challengeable $user, $provider_name)
    {
        return $this->enrollment->enrolled(
            $user->id, $provider_name
        );
    }

    public function validProviderName($provider_name)
    {
        $providers = array_keys($this->enabledProviders());

        return in_array($provider_name, $providers);
    }

    public function hasMultipleProviders(Challengeable $user)
    {
        return count($this->enrolledProviders($user)) > 1;
    }

    protected function verifyEnrolment(Challengeable $user, $provider_name, $is_enrolment = false)
    {
        if (!$is_enrolment && !$this->enrolled($user, $provider_name)) {
            return static::USER_NOT_ENROLLED;
        }

        $this->tokens->gc(
            $user->id, 
            $this->config['tokens']['lifetime']
        );
    }

    public function challenge(Challengeable $user, $provider_name, $session_id, $is_enrolment = false)
    {
        if ($response = $this->verifyEnrolment($user, $provider_name, $is_enrolment)) {
            return $response;
        }

        $provider = $this->provider($provider_name);

        $token = $this->tokens->create(
            $code = $provider->code(),
            $session_id,
            $user->id,
            $provider_name,
            $is_enrolment
        );

        $provider->challenge($user, $code);
        
        $this->event(new Events\Challenged($user, $token));
    }

    public function verify(Challengeable $user, $provider_name, $code, $session_id, $is_enrolment = false)
    {
        if ($response = $this->verifyEnrolment($user, $provider_name, $is_enrolment)) {
            return $response;
        }

        $provider = $this->provider($provider_name);

        if (!($token = $this->tokens->getBySessionId($session_id, $is_enrolment))) {
            return static::INVALID_TOKEN;
        }

        if ($token['token'] === $code) {

            if ('1' === $token['is_enrollment_token']) {
                return $provider->requiresSetup() ?
                    static::PROVIDER_REQUIRES_SETUP :
                    $this->enrollment($user, $provider_name);
            }

            return static::CODE_VERIFIED;
        }

        return static::INVALID_CODE;
    }

    public function enroll(Challengeable $user, $provider_name, $session_id)
    {
        if (!$this->validProviderName($provider_name)) {
            return static::INVALID_PROVIDER;
        }

        if (!$this->canEnroll($user, $provider_name)) {
            return static::USER_CANNOT_ENROL;
        }

        $this->tokens->flush($user->id);

        if ($this->requiresEnrolmentChallenge($provider_name)) {
            $response = $this->challenge(
                $user, 
                $provider_name, 
                $session_id,
                $is_enrollment = true
            );

            return static::USER_CHALLENGED;
        }

        return $this->broker()->requiresSetup($provider_name) ?
            static::PROVIDER_REQUIRES_SETUP :
            $this->enrollment($user, $provider_name);
    }

    public function enrollment(Challengeable $user, $provider_name)
    {
        return static::USER_ENROLED;
    }

    protected function provider($provider_name)
    {
        return $this->providers->provider($provider_name);
    }

    public function setEventDispatcher(EventDispatcher $events)
    {
        $this->events = $events;

        return $this;
    }

    public function getEventDispatcher()
    {
        return $this->dispatcher;
    }

    protected function event()
    {
        if ($this->dispatcher) {
            return call_user_func_array(
                [$this->dispatcher, 'dispatch'], 
                func_get_args()
            );
        }
    }

    public function providers()
    {
        return $this->providers;
    }
}