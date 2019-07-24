<?php

namespace BoxedCode\Laravel\TwoFactor;

use BoxedCode\Laravel\TwoFactor\BrokerResponse;
use BoxedCode\Laravel\TwoFactor\Contracts\AuthenticationBroker as BrokerContract;
use BoxedCode\Laravel\TwoFactor\Contracts\Challenge;
use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use BoxedCode\Laravel\TwoFactor\Contracts\Enrolment;
use BoxedCode\Laravel\TwoFactor\Methods\MethodManager;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Support\Str;

class AuthenticationBroker implements BrokerContract
{
    /**
     * The method manager instance.
     * 
     * @var \BoxedCode\Laravel\TwoFactor\Methods\MethodManager
     */
    protected $methods;

    /**
     * The configuration array.
     * 
     * @var array
     */
    protected $config;

    /**
     * The event dispatcher instance.
     * 
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * Create a new broker instance.
     * 
     * @param MethodManager $methods
     * @param array         $config
     */
    public function __construct(MethodManager $methods, array $config) 
    {
        $this->methods = $methods;

        $this->config = $config;
    }

    /**
     * Begin enrolment in an authentication method.
     * 
     * @param  Challengeable $user       
     * @param  string        $method_name
     * @param  array         $meta       
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse
     */
    public function beginEnrolment(Challengeable $user, $method_name, array $meta = [])
    {
        // Retrieve a method instance for the requested method name.
        if (!($method = $this->method($method_name))) {
            return $this->respond(static::METHOD_NOT_FOUND);
        }
        
        $user->enrolments()->method($method_name)->delete();

        // Verify that the use can enrol in two factor 
        // authentication for the requested provider.
        if (!$this->canBeginEnrolment($user, $method_name)) {
            return $this->respond(static::USER_CANNOT_ENROL);
        }

        // Create the enrolment for this attempt.
        $enrolment = $user->enrolments()->create([
            'user_id' => $user->getKey(),
            'method' => $method_name,
            'meta' => $meta,
        ]);

        // If the requested method requires setup return 
        // the appropriate response.
        if ($method->requiresEnrolmentSetup()) {
            return $this->respond(
                static::METHOD_REQUIRES_SETUP, 
                ['enrolment' => $enrolment]
            );
        }

        return $this->beginEnrolmentChallengeOrEnrol($enrolment);
    }

    /**
     * Prepare for enrolment setup.
     *
     * This is used to create any data that needs to be provided 
     * to the user before they call the setup method, this could 
     * include things like tokens, QR codes or maybe making a call 
     * to an external service provider.
     * 
     * @param  Challengeable $user       
     * @param  string        $method_name
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse                 
     */
    public function beforeSetup(Challengeable $user, $method_name)
    {
        if (!($enrolment = $this->getEnrolment($user, $method_name))) {
            return $this->respond(static::ENROLMENT_NOT_FOUND);
        }

        // We call the method instances preparation method so that it 
        // can make any calls or generate necessary data before the setup.
        // This data is then returned to the caller for them to process 
        // before they call the setup method.
        $data = $this->method($method_name)->beforeSetup($user);

        return $this->respond(static::BEFORE_SETUP_COMPLETE, ['data' => $data]);
    }

    /**
     * Setup the enrolment.
     * 
     * @param  Challengeable $user        
     * @param  string        $method_name 
     * @param  array         $data        
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse                     
     */
    public function setup(Challengeable $user, $method_name, array $data = [])
    {
        if (!($enrolment = $this->getEnrolment($user, $method_name))) {
            return $this->respond(static::ENROLMENT_NOT_FOUND);
        }

        // The $data from the user is passed to the method instance here 
        // for processing. The method then returns state to be persisted 
        // against the enrolment model for later use.
        $state = $this->method($method_name)->setup(
            $user, $enrolment->state, $data
        );

        $enrolment->fill([
            'setup_at' => now(),
            'state' => array_merge_recursive(
                $enrolment->state, $state
            )
        ])->save();

        return $this->beginEnrolmentChallengeOrEnrol($enrolment);
    }

    /**
     * Enrol the user.
     * 
     * @param  Challengeable $user   
     * @param  string        $method 
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse             
     */
    public function enrol(Challengeable $user, $method_name)
    {
        if (!($enrolment = $user->enrolments()->enrolling($method_name)->first())) {
            return $this->respond(static::ENROLMENT_NOT_FOUND);
        }

        $method = $this->method($method_name);

        // If the enrolment method requires setup, verify that this has occurred.
        if ($method->requiresEnrolmentSetup() && !$enrolment->setup_at) {
            return $this->respond(static::METHOD_REQUIRES_SETUP);
        }

        // If the enrolment method requires a challenge, verify 
        // this has occurred or issue a new challenge.
        $challenge = $user->challenges()->enrolment($method_name)->first();

        if ($method->requiresEnrolmentChallenge() && (!$challenge || !$challenge->verified_at)) {
            return $this->beginEnrolmentChallengeOrEnrol($enrolment);
        }

        $state = $method->enrol($user, $enrolment->state);

        $enrolment->fill([
            'enrolled_at' => now(),
            'state' => array_merge_recursive(
                $enrolment->state, $state
            )
        ])->save();

        $this->event(new Events\Enrolled($enrolment));
        
        return $this->respond(static::USER_ENROLLED, ['enrolment' => $enrolment]);
    }

    /**
     * Disenrol the user from an authentication method.
     * 
     * @param  Challengeable $user        
     * @param  string        $method_name 
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse
     */
    public function disenrol(Challengeable $user, $method_name)
    {
        if (!($enrolment = $user->enrolments()->method($method_name)->first())) {
            return $this->respond(static::ENROLMENT_NOT_FOUND);
        }
        
        $this->method($method_name)->disenrol(
            $user, $enrolment->state
        );

        $user->enrolments()->method($method_name)->delete();

        $user->challenges()->method($method_name)->delete();

        $this->event(new Events\Disenrolled($user, $method_name));

        return $this->respond(static::USER_DISENROLLED, ['enrolment' => $enrolment]);
    }

    /**
     * Dispatch a challenge request to the user.
     * 
     * @param  Challengeable $user        
     * @param  string        $method_name 
     * @param  string        $purpose     
     * @param  array         $data        
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse            
     */
    public function challenge(Challengeable $user, $method_name, $purpose, array $data = [])
    {
        // Retrieve a method instance for the requested method name.
        if (!($method = $this->method($method_name))) {
            return $this->respond(static::METHOD_NOT_FOUND);
        }

        // Flush ALL previous challenges.
        $user->challenges()->method($method_name)->delete();

        // Next, we check that the user is either enrolled or that 
        // this challenge is part of the enrolment process.
        if (!$this->canChallenge($user, $method_name, $purpose)) {
            return $this->respond(static::USER_NOT_ENROLLED);
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

        return $this->respond(static::USER_CHALLENGED, ['challenge' => $challenge]);
    }

    /**
     * Verify the challenge.
     * 
     * @param  Challengeable $user   
     * @param  string        $method 
     * @param  array         $data   
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse       
     */
    public function verify(Challengeable $user, $method, array $data = [])
    {
        // Check that we have a valid challenge for the user and method.
        if (!($challenge = $user->challenges()->pending($method)->first())) {
            return $this->respond(static::CHALLENGE_NOT_FOUND);
        }

        try {
            // Call the method instance to verify the data passed, if 
            // the instance fails it will throw a TwoFactorVerificationException.
            $state = $this->method($method)->verify($user, $challenge->state, $data);

            $challenge->fill([
                'verified_at' => now(),
                'state' => array_merge_recursive(
                    $challenge->state, $state
                )
            ])->save();

            $this->event(new Events\Verified($challenge));

            // If the challenge relates to an enrolment, we enrol the 
            // user and return the response.
            if (Challenge::PURPOSE_ENROLMENT === $challenge->purpose) {
                return $this->enrol($user, $method);
            }

            return $this->respond(static::CHALLENGE_VERIFIED, ['challenge' => $challenge]);
        } catch (Exceptions\TwoFactorVerificationException $ex) { /**/ }

        return $this->respond(static::CHALLENGE_NOT_VERIFIED);
    }

    /**
     * Begin the enrolment challenge or enrol.
     * 
     * @param  Enrolment $enrolment
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse
     */
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

    /**
     * Can the user create a challenge request for the 
     * requested method and purpose.
     * 
     * @param  Challengeable $user    
     * @param  string        $method_name 
     * @param  string        $purpose 
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse         
     */
    public function canChallenge(Challengeable $user, $method_name, $purpose)
    {
        $method = $this->method($method_name);

        $isEnrolling = (Challenge::PURPOSE_ENROLMENT === $purpose);

        $scope = $isEnrolling ? 'enrolling' : 'enrolled';
        $enrolments = $user->enrolments()->{$scope}($method_name)->get();

        $isUserEnrolled = !$isEnrolling ? 1 === $enrolments->count() : false;

        $isSetupStateValid = (
            $method->requiresEnrolmentSetup() ? 
                $enrolments->first() && $enrolments->first()->setup_at : 
                true
        );

        if (($isEnrolling || $isUserEnrolled) && $isSetupStateValid) {
            return true;
        }

        return false;
    }

    /**
     * Can the user begin enrolment in a given authentication method.
     * 
     * @param  Challengeable $user   
     * @param  string        $method 
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse
     */
    public function canBeginEnrolment(Challengeable $user, $method)
    {
        return (
            $user->canEnrolInTwoFactorAuth($method) &&
            0 === $user->enrolments()->enrolled($method)->count()
        );  
    }

    /**
     * Get a list of methods that the user is enrolled in.
     * 
     * @param  Challengeable $user
     * @return \Illuminate\Support\Collection
     */
    public function getEnrolledAuthMethodList(Challengeable $user)
    {
        return $user->enrolments()->enrolled()->get()
            ->keyBy('method')->map(function($enrolment) {
                return $enrolment->label;
            });
    }

    /**
     * Get the first enrolment for the requested user and method.
     * 
     * @param  Challengeable $user        
     * @param  string        $method_name 
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse              
     */
    protected function getEnrolment(Challengeable $user, $method_name)
    {
        return $user->enrolments()
            ->method($method_name)
            ->first();
    }

    /**
     * Create a response instance.
     * 
     * @param  string $outcome 
     * @param  array  $payload 
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse    
     */
    protected function respond(string $outcome, array $payload = [])
    {
        return new BrokerResponse($outcome, $payload);
    }

    /**
     * Generate a UUID for a challenge.
     * 
     * @return string
     */
    protected function generateChallengeUuid()
    {
        return Str::uuid();
    }

    /**
     * Set the event dispatcher.
     * 
     * @param EventDispatcher $events
     */
    public function setEventDispatcher(EventDispatcher $events)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * Get the event dispatcher.
     * 
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getEventDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Try to dispatch an event.
     *
     * @return void
     */
    protected function event()
    {
        if ($this->dispatcher) {
            call_user_func_array(
                [$this->dispatcher, 'dispatch'], 
                func_get_args()
            );
        }
    }

    /**
     * Get the method manager instance.
     * 
     * @return \BoxedCode\Laravel\TwoFactor\Methods\MethodManager
     */
    public function getMethodManager()
    {
        return $this->methods;
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