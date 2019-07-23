<?php
// @todo Fix notification method token generator configuration
// @todo allow developer to handle responses for custom purposes
// @todo middleware
// @todo allow custom views verywhere
// @todo flush stale tokens

namespace BoxedCode\Laravel\TwoFactor;

use BoxedCode\Laravel\TwoFactor\Contracts\AuthenticationBroker as BrokerContract;
use BoxedCode\Laravel\TwoFactor\Contracts\Challenge;
use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use BoxedCode\Laravel\TwoFactor\Contracts\Enrolment;
use BoxedCode\Laravel\TwoFactor\Methods\MethodManager;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Support\Str;

class AuthenticationBroker implements BrokerContract
{
    protected $methods;

    protected $config;

    protected $dispatcher;

    public function __construct(MethodManager $methods, array $config) 
    {
        $this->methods = $methods;

        $this->config = $config;
    }

    protected function getEnrolment(Challengeable $user, $method_name)
    {
        return $user->enrolments()
            ->method($method_name)
            ->first();
    }

    protected function beginEnrolmentChallengeOrEnrol(Enrolment $enrolment)
    {
        // If the method requires a pre-enrolment challenge, we run the 
        // challenge routine and return the response.
        if ($this->method($enrolment->method)->requiresEnrolmentChallenge()) {
            return $this->challenge(
                $enrolment->user, 
                $enrolment->method, 
                Challenge::PURPOSE_ENROLMENT
            );
        }

        // Otherwise, if the method requires no setup and no pre-enrolment 
        // challenge, we run the enrolment completion routine.
        return $this->enrol($enrolment->user, $enrolment->method);
    }

    public function begin(Challengeable $user, $method_name, array $meta = [])
    {
        // Retrieve a method instance for the requested method name.
        if (!($method = $this->method($method_name))) {
            return static::INVALID_METHOD;
        }
        
        $user->enrolments()->method($method_name)->delete();

        // Verify that the use can enrol in two factor 
        // authentication for the requested provider.
        if (!$this->canEnrol($user, $method_name)) {
            return static::USER_CANNOT_ENROL;
        }

        // Flush ALL previous enrolments for this method.
        $user->enrolments()->method($method_name)->delete();

        // Create the enrolment for this attempt.
        $enrolment = $user->enrolments()->create([
            'user_id' => $user->getKey(),
            'method' => $method_name,
            'meta' => $meta,
        ]);

        // If the requested method requires setup return 
        // the appropriate response.
        if ($method->requiresEnrolmentSetup()) {
            return static::METHOD_REQUIRES_SETUP;
        }

        return $this->beginEnrolmentChallengeOrEnrol($enrolment);
    }

    public function beforeSetup(Challengeable $user, $method_name)
    {
        if (!($enrolment = $this->getEnrolment($user, $method_name))) {
            return static::INVALID_ENROLMENT;
        }

        // We call the method instances preparation method so that it 
        // can make any calls or generate necessary data before the setup.
        return $this->method($method_name)->beforeSetup($user);
    }

    public function setup(Challengeable $user, $method_name, array $data = [])
    {
        if (!($enrolment = $this->getEnrolment($user, $method_name))) {
            return static::INVALID_ENROLMENT;
        }

        $state = $this->method($method_name)->setup($user, $enrolment->state, $data);

        $enrolment->fill([
            'setup_at' => now(),
            'state' => array_merge_recursive(
                $enrolment->state, $state
            )
        ])->save();

        return $this->beginEnrolmentChallengeOrEnrol($enrolment);
    }

    public function enrol(Challengeable $user, $method)
    {
        if ($enrolment = $user->enrolments()->enrolling($method)->first()) {
            $state = $this->method($method)->enrol(
                $user, $enrolment->state
            );

            $enrolment->fill([
                'enrolled_at' => now(),
                'state' => array_merge_recursive(
                    $enrolment->state, $state
                )
            ])->save();

            $this->event(new Events\Enrolled($enrolment));
            
            return static::USER_ENROLLED;
        }

        return static::INVALID_ENROLMENT;
    }

    public function disenrol(Challengeable $user, $method_name)
    {
        if ($enrolment = $user->enrolments()->enrolled($method_name)->first()) {
            $this->method($method_name)->disenrol($user, $enrolment->state);

            $user->enrolments()->method($method_name)->delete();

            $this->event(new Events\Disenrolled($user, $method_name));

            return static::USER_DISENROLLED;
        }

        return static::INVALID_ENROLMENT;
    }

    public function challenge(Challengeable $user, $method_name, $purpose, array $data = [])
    {
        // Retrieve a method instance for the requested method name.
        if (!($method = $this->method($method_name))) {
            return static::INVALID_METHOD;
        }

        // Flush ALL previous challenges.
        $user->challenges()->method($method_name)->delete();

        // Next, we check that the user is either enrolled or that 
        // this challenge is part of the enrolment process.
        if (!$this->canChallenge($user, $method_name, $purpose)) {
            return static::USER_NOT_ENROLLED;
        }

        $state = $method->challenge($user, $data);

        // Create the challenge, call the method 
        // instance and fire the challenged event.
        $challenge = $user->challenges()->create([
            'id' => $this->generateChallengeUuid(),
            'method' => $method_name,
            'purpose' => $purpose,
            'challenged_at' => now(),
            'state' => $state,
        ]);
        
        $this->event(new Events\Challenged($challenge));

        return static::USER_CHALLENGED;
    }

    public function verify(Challengeable $user, $method, array $data = [])
    {
        // Check that we have a valid challenge for the user and method.
        if (!($challenge = $user->challenges()->pending($method)->first())) {
            return static::INVALID_CHALLENGE;
        }

        try {
            $state = $this->method($method)->verify(
                $user, $challenge->state, $data
            );

            $challenge->fill([
                'verified_at' => now(),
                'state' => array_merge_recursive(
                    $challenge->state, $state
                )
            ])->save();

            $this->event(new Events\Verified($challenge));

            if (Challenge::PURPOSE_ENROLMENT === $challenge->purpose) {
                return $this->enrol($user, $method);
            }

            return static::CODE_VERIFIED;
        } catch (Exceptions\TwoFactorVerificationException $ex) { /**/ }

        return static::INVALID_CODE;
    }

    public function canChallenge(Challengeable $user, $method, $purpose)
    {
        $isEnrolling = (Challenge::PURPOSE_ENROLMENT === $purpose);
        $isUserEnrolled = (1 === $user->enrolments()->enrolled($method)->count());

        if ($isEnrolling || $isUserEnrolled) {
            return true;
        }

        return false;
    }

    public function canEnrol(Challengeable $user, $method)
    {
        return (
            $user->canEnrolInTwoFactorAuth($method) &&
            0 === $user->enrolments()->enrolled($method)->count()
        );  
    }

    public function getEnrolledAuthDriverList(Challengeable $user)
    {
        return $user->enrolments()->enrolled()->get()
            ->keyBy('method')->map(function($enrolment) {
                return $enrolment->label;
            });
    }

    protected function generateChallengeUuid()
    {
        return Str::uuid();
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

    /**
     * Dynamically call the default method instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->methods->$method(...$parameters);
    }
}