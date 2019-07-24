<?php

namespace BoxedCode\Laravel\TwoFactor\Http\Traits;

use BoxedCode\Laravel\TwoFactor\AuthenticationBroker;
use BoxedCode\Laravel\TwoFactor\BrokerResponse;
use BoxedCode\Laravel\TwoFactor\Contracts\Challenge;
use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use BoxedCode\Laravel\TwoFactor\Contracts\Enrolment;
use BoxedCode\Laravel\TwoFactor\Exceptions\TwoFactorLogicException;
use Illuminate\Http\Request;
use LogicException;

trait EnrolsUsers
{
    /**
     * Begin the two factor enrolment process.
     * 
     * @param  Request $request
     * @param  string  $method  
     * @return \Illuminate\Http\Response
     */
    public function begin(Request $request, $method)
    {
        $response = $this->broker()->beginEnrolment(
            $request->user(), 
            $method
        );

        $request->session()->flash(
            '_tfa_purpose', Challenge::PURPOSE_ENROLMENT
        );

        return $this->routeResponse($response, $method);
    }

    /**
     * The enrolment method requires additional setup, the user 
     * should be redirected to the methods setup form.
     * 
     * @param  Challengeable $user      
     * @param  Enrolment     $enrolment
     * @return \Illuminate\Http\Response                 
     */
    protected function requiresSetup(Challengeable $user, Enrolment $enrolment)
    {
        //
    }

    /**
     * Show the enrolment method setup form.
     * 
     * @param  Request $request
     * @param  string  $method
     * @return \Illuminate\Http\Response
     */
    public function showSetupForm(Request $request, $method)
    {
        $response = $this->broker()->beforeSetup(
            $request->user(),
            $method
        );

        // If the beforeSetup routine was not successful, we route the 
        // brokers response via the response handler, this determines the next action.
        if (AuthenticationBroker::BEFORE_SETUP_COMPLETE !== (string) $response) {
            return $this->routeResponse($response);
        }

        return $this->findView($method, 'enrol')
            ->withSetupPath(route('tfa.enrolment.setup', [$method]))
            ->withSetupData($response->data);
    }

    /**
     * Handle the setup form submission.
     * 
     * @param  Request $request
     * @param  string  $method
     * @return \Illuminate\Http\Response
     */
    public function setup(Request $request, $method)
    {
        $response = $this->broker()->setup(
            $request->user(),
            $method,
            $request->all()
        );

        $this->reflashSessionPurpose($request);

        return $this->routeResponse($response, $method);
    }

    /**
     * The user has been successfully enrolled into the requested 
     * authentication method and should be shown the enrolment success view. 
     *      
     * @param  Challengeable $user      
     * @param  Enrolment     $enrolment 
     * @return \Illuminate\Http\Request          
     */
    protected function enrolled(Challengeable $user, Enrolment $enrolment)
    {
        //
    }

    /**
     * Show the enrolment success page.
     * 
     * @param  Request $request
     * @param  string  $method  
     * @return \Illuminate\Http\Response
     */
    public function showEnrolled(Request $request, $method)
    {
        return $this->findView($method, 'enrolled')
            ->withMethod($method);
    }

    /**
     * Handle a request to disenroll the user 
     * from the requested authentication method.
     * 
     * @param  Request $request
     * @param  string  $method 
     * @return \Illuminate\Http\Response
     */
    public function disenrol(Request $request, $method)
    {
        $response = $this->broker()->disenrol(
            $request->user(),
            $method
        );

        return $this->routeResponse($response, $method);
    }
    
    /**
     * The user has been disenrolled for the requested authentication 
     * method an should be shown the disenrolment success view.
     * 
     * @param  Challengeable $user      
     * @param  Enrolment     $enrolment 
     * @return \Illuminate\Http\Request  
     */
    protected function disenrolled(Challengeable $user, Enrolment $enrolment)
    {
        //
    }

    /**
     * Show the 'successfully disenrolled' page.
     * 
     * @param  Request $request
     * @param  string  $method
     * @return \Illuminate\Http\Response
     */
    public function showDisenrolled(Request $request, $method)
    {
        return view('two_factor::disenrolled')
            ->withMethod($method);
    }
}