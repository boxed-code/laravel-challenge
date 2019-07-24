<?php

namespace BoxedCode\Laravel\TwoFactor\Http\Traits;

use BoxedCode\Laravel\TwoFactor\Contracts\AuthenticationBroker;
use BoxedCode\Laravel\TwoFactor\Contracts\Challenge;
use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use BoxedCode\Laravel\TwoFactor\Exceptions\TwoFactorLogicException;
use Illuminate\Http\Request;

trait ChallengesUsers
{
    /**
     * Show the authentication method selection view.
     * 
     * @param  Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function showMethodSelectionForm(Request $request)
    {
        $enrolmentCount = $request->user()->enrolments()->enrolled()->count();

        // Send an error if there are no enrolments for the current user.
        if (0 === $enrolmentCount) { 
            return $this->routeResponse(AuthenticationBroker::ENROLMENT_NOT_FOUND); 
        }

        // If the user only has one enrolled authentication method, 
        // we direct them straight to the verification process.
        if (1 === $enrolmentCount) {
            return $this->challengeAndRedirect(
                $request->user(),
                $request->user()->getDefaultTwoFactorAuthMethod(),
                $request->session()->get('_tfa_purpose', Challenge::PURPOSE_AUTH),
                $request->all()
            );
        }

        $methods = $this->broker()->getEnrolledAuthMethodList(
            $request->user()
        );

        // Otherwise, we show them the method selection screen.
        return view('two_factor::method')
            ->with('methods', $methods)
            ->with('challenge_path', route('tfa.challenge'));
    }

    /**
     * Handle the authentication method form request.
     * 
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function challenge(Request $request)
    {
        $purpose = $request->session()->get(
            '_tfa_purpose', Challenge::PURPOSE_AUTH
        );

        $request->validate([
            'method' => 'required|string'
        ]);

        return $this->challengeAndRedirect(
            $request->user(),
            $request->get('method'),
            $purpose,
            $request->all()
        );
    }

    /**
     * The user has been challenged via the chosen 
     * authentication method and needs to verify the token.
     * 
     * @param  Challengeable $user      
     * @param  Challenge     $challenge 
     * @return \Illuminate\Http\Response            
     */
    public function challenged(Challengeable $user, Challenge $challenge)
    {
        //
    }

    /**
     * Show the authentication methods verification form.
     * 
     * @param  Request $request
     * @param  string  $method
     * @return \Illuminate\Contracts\View\View
     */
    public function showVerificationForm(Request $request, $method)
    {
        // First, we check that the requested authentication method
        // is valid and that the user is enrolled into it.
        $purpose = $request->session()->get(
            '_tfa_purpose', Challenge::PURPOSE_AUTH
        );

        $this->reflashSessionPurpose($request);

        $canChallenge = $this->broker()->canChallenge(
            $request->user(), $method, $purpose
        );
        
        if ($canChallenge) { 
            return $this->findView($method, 'verify')
                ->withVerificationPath(route('tfa.verify.form', [$method]));
        }

        return $this->routeResponse(AuthenticationBroker::USER_NOT_ENROLLED, $method);
    }

    /**
     * Handle the response from the verification form.
     * 
     * @param  Request $request 
     * @param  string $method  
     * @return \Illuminate\Http\Response     
     */
    public function verify(Request $request, $method)
    {
        $response = $this->broker()->verify(
            $request->user(), 
            $method, 
            $request->all()
        );

        $this->reflashSessionPurpose($request);

        return $this->routeResponse($response, $method);
    }

    /**
     * The code was verified by the method instance, the user 
     * should be redirected to the intended destination.
     * 
     * @param  Challengeable $user 
     * @param  Challenge     $challenge 
     * @return \Illuminate\Http\Response             
     */
    protected function verified(Challengeable $user, Challenge $challenge)
    {
        //
    }

    /**
     * Dispatch a challenge request to the user and redirect 
     * the user to the verification path.
     * 
     * @param  Challengeable $user    
     * @param  string        $method  
     * @param  sting         $purpose 
     * @param  array         $data    
     * @return \Illuminate\Http\Response         
     */
    protected function challengeAndRedirect(Challengeable $user, 
                                            $method, 
                                            $purpose, 
                                            array $data = []
    ) {
        $response = $this->broker()->challenge(
            $user, 
            $method, 
            $purpose,
            $data
        );

        return $this->routeResponse($response, $method);
    }
}