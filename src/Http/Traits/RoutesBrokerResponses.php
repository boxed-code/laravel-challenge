<?php

namespace BoxedCode\Laravel\TwoFactor\Http\Traits;

use BoxedCode\Laravel\TwoFactor\BrokerResponse;
use BoxedCode\Laravel\TwoFactor\Contracts\AuthBroker;
use BoxedCode\Laravel\TwoFactor\Exceptions\TwoFactorLogicException;

trait RoutesBrokerResponses
{
    /**
     * Get the path to redirect the user to after verification.
     * 
     * @return string
     */
    public function verificationRedirectPath()
    {
        return '/';
    }

    /**
     * Show the TFA error view.
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function showError()
    {
        return view('two_factor::error');
    }
    
    /**
     * Redirect the user to the TFA error view with 
     * an associated error message.
     * 
     * @param sting $message
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendErrorResponse($message)
    {
        return redirect()->route('tfa.error')
            ->withErrors([
                $message
            ]);
    }

    /**
     * Route a broker response to the correct handler.
     * 
     * @param  BrokerResponse|string $response
     * @param  string|null    $method   
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     * @throws TwoFactorLogicException
     */
    protected function routeResponse($response, $method = null)
    {
        switch ($response) 
        {
            // The enrolment method requires additional setup, the user 
            // should be redirected to the methods setup form.
            case AuthBroker::METHOD_REQUIRES_SETUP:
                return $this->requiresSetup($response->enrolment->user, $response->enrolment) ?:
                    redirect()->route('tfa.enrolment.setup', [$method])
                        ->withEnrolment($response->enrolment);

            // The user has been successfully enrolled into the requested 
            // authentication method and should be shown the enrolment success view.           
            case AuthBroker::USER_ENROLLED:
                return $this->enrolled($response->enrolment->user, $response->enrolment) ?:
                    redirect()->route('tfa.enrolled', [$method])
                        ->withEnrolment($response->enrolment);

            // The user has been disenrolled for the requested authentication 
            // method an should be shown the disenrolment success view.
            case AuthBroker::USER_DISENROLLED:
                return $this->disenrolled($response->enrolment->user, $response->enrolment) ?:
                    redirect()->route('tfa.disenrolled', [$method])
                        ->withEnrolment($response->enrolment);

            // The user has been challenged via the chosen 
            // authentication method and needs to verify the token.
            case AuthBroker::USER_CHALLENGED:
                return $this->challenged($response->challenge->user, $response->challenge) ?: 
                    redirect()->route('tfa.verify.form', [$method])
                        ->withChallenge($response->challenge);

            // The challenge was verified by the method instance, the user 
            // should be redirected to the intended destination.
            case AuthBroker::CHALLENGE_VERIFIED:
                return $this->verified($response->challenge->user, $response->challenge) ?:
                    redirect()
                        ->to($this->verificationRedirectPath())
                        ->withChallenge($response->challenge);

            // The code / token provided was invalid, the user should 
            // check that it correct and try again.
            case AuthBroker::CHALLENGE_NOT_VERIFIED:
                return redirect()->back()->withErrors([
                    'code' => 'The code you entered was incorrect.'
                ]);

            // The requested challenge does not exist, a new 
            // challenge should be made.
            case AuthBroker::CHALLENGE_NOT_FOUND:
                return $this->sendErrorResponse(
                    'No active challenge could be found, please restart authentication.'
                );

            // The requested enrolment does not exist, a new 
            // enrolment request should be made.
            case AuthBroker::ENROLMENT_NOT_FOUND:
                return $this->sendErrorResponse(
                    'No active enrolment could be found, please restart enrolment.'
                );

            // The requested method is not available for enrolment.
            case AuthBroker::METHOD_NOT_FOUND:
                return $this->sendErrorResponse(
                    sprintf(
                        'The %s method is not available for enrollment.', 
                        $method
                    )
                );

            // The user is already enrolled in the authentication method.
            case AuthBroker::USER_ALREADY_ENROLLED:
                return  $this->sendErrorResponse(
                    sprintf(
                        'The user is already enrolled in %s two factor authentication.', 
                        $method
                    )
                );

            // The user is not enrolled in the requested authentication method.
            case AuthBroker::USER_NOT_ENROLLED:
                return  $this->sendErrorResponse(
                    sprintf(
                        'Two factor authentication via %s is not enabled for this user.', 
                        $method
                    )
                );

            // The user cannot enrol into the requested authentication method.
            case AuthBroker::USER_CANNOT_ENROL:
                return $this->sendErrorResponse(
                    sprintf(
                        'The user cannot enrol into %s two factor authentication.', 
                        $method
                    )
                );
        }

        throw new TwoFactorLogicException(
            sprintf('Broker returned an invalid response. [%s]', $response)
        );
    }
}