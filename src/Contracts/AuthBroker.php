<?php

namespace BoxedCode\Laravel\TwoFactor\Contracts;

use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

interface AuthBroker
{
    /**
     * Constant representing a requirement to perform 
     * the authentication method setup routine.
     *
     * @var  string
     */
    const METHOD_REQUIRES_SETUP = 'method_requires_setup';

    /**
     * Constant representing the successful outcome of the 
     * beforeSetup routine.
     *
     * @var  string
     */
    const BEFORE_SETUP_COMPLETE = 'before_setup_complete';

    /**
     * Constant representing the successful verification 
     * of a challenge.
     *
     * @var  string
     */
    const CHALLENGE_VERIFIED = 'challenge_verified';

    /**
     * Constant representing the failure to verify a challenge.
     *
     * @var  string
     */
    const CHALLENGE_NOT_VERIFIED = 'challenge_not_verified';

    /**
     * Constant representing a successfully sent challenge.
     *
     * @var  string
     */
    const USER_CHALLENGED = 'user_challenged';

    /**
     * Constant representing a successful enrolment.
     *
     * @var  string
     */
    const USER_ENROLLED = 'user_enrolled';

    /**
     * Constant representing a successful disenrolment.
     *
     * @var  string
     */
    const USER_DISENROLLED = 'user_disenrolled';

    /**
     * Constant representing the user being in an unenrolled state.
     *
     * @var  string
     */
    const USER_NOT_ENROLLED = 'user_not_enrolled';

    /**
     * Constant representing the user already being enrolled.
     *
     * @var  string
     */
    const USER_ALREADY_ENROLLED = 'user_already_enrolled';

    /**
     * Constant representing the user not being able to enrol.
     *
     * @var  string
     */
    const USER_CANNOT_ENROL = 'user_cannot_enrol';

    /**
     * Constant representing the user having no enrolments in progress.
     *
     * @var string
     */
    const NO_ENROLMENT_IN_PROGRESS = 'no_enrolment_in_progress';

    /**
     * Constant representing the invalid method response.
     *
     * @var  string
     */
    const METHOD_NOT_FOUND = 'method_not_found';

    /**
     * Constant representing the invalid enrolment response.
     *
     * @var  string
     */
    const ENROLMENT_NOT_FOUND = 'enrolment_not_found';

    /**
     * Constant representing the invalid challenge response.
     *
     * @var  string
     */
    const CHALLENGE_NOT_FOUND = 'challenge_not_found';

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
    public function beforeSetup(Challengeable $user, $method_name);

    /**
     * Setup the enrolment.
     * 
     * @param  Challengeable $user        
     * @param  string        $method_name 
     * @param  array         $data        
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse                     
     */
    public function setup(Challengeable $user, $method_name, array $data = []);

    /**
     * Enrol the user.
     * 
     * @param  Challengeable $user   
     * @param  string        $method 
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse             
     */
    public function enrol(Challengeable $user, $method);

    /**
     * Disenrol the user from an authentication method.
     * 
     * @param  Challengeable $user        
     * @param  string        $method_name 
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse
     */
    public function disenrol(Challengeable $user, $method_name);

    /**
     * Dispatch a challenge request to the user.
     * 
     * @param  Challengeable $user        
     * @param  string        $method_name 
     * @param  string        $purpose     
     * @param  array         $data        
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse            
     */
    public function challenge(Challengeable $user, $method_name, $purpose, array $data = []);

    /**
     * Verify the challenge.
     * 
     * @param  Challengeable $user   
     * @param  string        $method 
     * @param  string        $purpose
     * @param  array         $data   
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse       
     */
    public function verify(Challengeable $user, $method, $purpose, array $data = []);

    /**
     * Begin enrolment in an authentication method.
     * 
     * @param  Challengeable $user       
     * @param  string        $method_name
     * @param  array         $state       
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse
     */
    public function beginEnrolment(Challengeable $user, $method_name, array $state = []);

    /**
     * Can the user create a challenge request for the 
     * requested method and purpose.
     * 
     * @param  Challengeable $user    
     * @param  string        $method_name
     * @param  string        $purpose 
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse         
     */
    public function canChallenge(Challengeable $user, $method_name, $purpose);

    /**
     * Can the user begin enrolment in a given authentication method.
     * 
     * @param  Challengeable $user   
     * @param  string        $method 
     * @return \BoxedCode\Laravel\TwoFactor\BrokerResponse
     */
    public function canBeginEnrolment(Challengeable $user, $method);

    /**
     * Get a list of methods that the user is enrolled in.
     * 
     * @param  Challengeable $user
     * @return \Illuminate\Support\Collection
     */
    public function getEnrolledAuthMethodList(Challengeable $user);

    /**
     * Set the event dispatcher.
     * 
     * @param EventDispatcher $events
     */
    public function setEventDispatcher(EventDispatcher $events);

    /**
     * Get the event dispatcher.
     * 
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getEventDispatcher();

    /**
     * Get the method manager instance.
     * 
     * @return \BoxedCode\Laravel\TwoFactor\Methods\MethodManager
     */
    public function getMethodManager();
}