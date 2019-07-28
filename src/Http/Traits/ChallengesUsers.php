<?php

namespace BoxedCode\Laravel\Auth\Challenge\Http\Traits;

use BoxedCode\Laravel\Auth\Challenge\Contracts\AuthBroker;
use BoxedCode\Laravel\Auth\Challenge\Contracts\AuthManager;
use BoxedCode\Laravel\Auth\Challenge\Contracts\Challenge;
use BoxedCode\Laravel\Auth\Challenge\Contracts\Challengeable;
use BoxedCode\Laravel\Auth\Challenge\Exceptions\ChallengeLogicException;
use Illuminate\Http\Request;

trait ChallengesUsers
{
    /**
     * Show the authentication method selection view.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function showMethodSelectionForm(Request $request)
    {
        // Authentications must be requested via the manager.
        if (!$this->manager()->wantsAuthentication()) {
            return $this->routeResponse(
                $request, AuthManager::NO_AUTH_REQUEST
            );
        }

        $enrolmentCount = $request->user()->enrolments()->enrolled()->count();

        // Send an error if there are no enrolments for the current user.
        if (0 === $enrolmentCount) { 
            return $this->routeResponse(
                $request, AuthBroker::USER_NOT_ENROLLED
            ); 
        }

        // If the user only has one enrolled authentication method, 
        // we direct them straight to the verification process.
        if (1 === $enrolmentCount) {
            return $this->challengeAndRedirect(
                $request,
                $request->user(),
                $request->user()->getDefaultAuthMethod(),
                $request->all()
            );
        }

        $methods = $this->broker()->getEnrolledAuthMethodList(
            $request->user()
        );

        // Otherwise, we show them the method selection screen.
        return $this->view('method', null, [
            'methods' => $methods, 
            'form_action_url' => route('challenge.dispatch')
        ]);
    }

    /**
     * Handle the authentication method form request.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function challenge(Request $request)
    {
        $request->validate([
            'method' => 'required|string'
        ]);

        return $this->challengeAndRedirect(
            $request,
            $request->user(),
            $request->get('method'),
            $request->all()
        );
    }

    /**
     * The user has been challenged via the chosen 
     * authentication method and needs to verify the token.
     * 
     * @param  \Illuminate\Http\Request $request   
     * @param  \BoxedCode\Laravel\Auth\Challenge\Contracts\Challenge $challenge 
     * @return \Illuminate\Http\Response            
     */
    public function challenged(Request $request, Challenge $challenge)
    {
        //
    }

    /**
     * Show the authentication methods verification form.
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  string  $method
     * @return \Illuminate\Contracts\View\View
     */
    public function showVerificationForm(Request $request, $method)
    {
        // Authentications must be requested via the manager.
        if (!$this->manager()->wantsAuthentication()) {
            return $this->routeResponse(
                $request, AuthManager::NO_AUTH_REQUEST, $method
            );
        }

        // First, we check that the requested authentication method
        // is valid and that the user is enrolled into it.
        $purpose = $this->manager()->wantsAuthenticationFor();

        $canChallenge = $this->broker()->canChallenge(
            $request->user(), 
            $method,
            $purpose
        );
        
        if ($canChallenge) { 
            return $this->view('verify', $method, [
                'form_action_url' => route('challenge.verify.form', [$method])
            ]);
        }

        $response = AuthBroker::USER_NOT_ENROLLED;

        return $this->routeResponse($request, $response, $method);
    }

    /**
     * Handle the response from the verification form.
     * 
     * @param  \Illuminate\Http\Request $request 
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

        return $this->routeResponse($request, $response, $method);
    }

    /**
     * The code was verified by the method instance, the user 
     * should be redirected to the intended destination.
     * 
     * @param  \Illuminate\Http\Request $requst 
     * @param  Challenge     $challenge 
     * @return \Illuminate\Http\Response             
     */
    protected function verified(Request $requst, Challenge $challenge)
    {
        //
    }

    /**
     * Dispatch a challenge request to the user and redirect 
     * the user to the verification path.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \BoxedCode\Laravel\Auth\Challenge\Contracts\Challengeable $user    
     * @param  string        $method  
     * @param  array         $data    
     * @return \Illuminate\Http\Response         
     */
    protected function challengeAndRedirect(Request $request,
                                            Challengeable $user, 
                                            $method, 
                                            array $data = []
    ) {
        $purpose = $this->manager()->wantsAuthenticationFor();
        
        $response = $this->broker()->challenge(
            $user, 
            $method, 
            $purpose,
            $data
        );

        return $this->routeResponse($request, $response, $method);
    }
}