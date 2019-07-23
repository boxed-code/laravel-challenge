<?php

namespace BoxedCode\Laravel\TwoFactor\Http\Traits;

use BoxedCode\Laravel\TwoFactor\AuthenticationBroker;
use BoxedCode\Laravel\TwoFactor\Contracts\Challenge;
use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use BoxedCode\Laravel\TwoFactor\Exceptions\TwoFactorLogicException;
use Illuminate\Http\Request;

trait ChallengesUsers
{
    protected function challengeAndRedirect(Challengeable $user, $method, $purpose, array $data = [])
    {
        $response = $this->broker()->challenge(
            $user, 
            $method, 
            $purpose,
            $data
        );

        if (AuthenticationBroker::USER_NOT_ENROLLED === $response) {
            return $this->sendUserNotEnrolledResponse($method);
        }

        return redirect()->to(
            $this->verificationPath($method)
        )->with('_tfa_purpose', $purpose);
    }

    public function showMethodSelectionForm(Request $request)
    {
        $enrolmentCount = $request->user()->enrolments()->enrolled()->count();

        // Send an error if there are no enrolments for the current user.
        if (0 === $enrolmentCount) {
            return $this->sendErrorResponse(
                'This user is not enroled in any two factor authentication methods.'
            );
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

        $methods = $this->broker()->getEnrolledAuthDriverList(
            $request->user()
        );

        // Otherwise, we show them the method selection screen.
        return view('two_factor::method')
            ->with('methods', $methods)
            ->with('challenge_path', $this->challengePath());
    }

    public function challenge(Request $request)
    {
        $purpose =  $request->session()->get(
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

    public function showVerificationForm(Request $request, $method)
    {
        // First, we check that the requested authentication method
        // is valid and that the user is enrolled into it.
        $purpose = $request->session()->get('_tfa_purpose', Challenge::PURPOSE_AUTH);

        $request->session()->keep('_tfa_purpose');

        if (!$this->broker()->canChallenge($request->user(), $method, $purpose)) {
            return $this->sendUserNotEnrolledResponse($method);
        }

        // Next, we check whether the provider has a custom verification view.
        $methodViewName = "two_factor::$method.verify";

        if (view()->exists($methodViewName)) {
            $view = view($methodViewName);
        }

        // Otherwise, we serve the default verification screen.
        return (isset($view) ? $view : view('two_factor::verify'))
            ->withVerificationPath($this->verificationPath($method));
    }

    public function verify(Request $request, $method)
    {
        $response = $this->broker()->verify(
            $request->user(), 
            $method, 
            $request->all()
        );

        switch ($response) 
        {
            case AuthenticationBroker::INVALID_CHALLENGE:
                return $this->sendErrorResponse('The token is invalid.');
            case AuthenticationBroker::INVALID_CODE:
                $request->session()->keep('_tfa_purpose');

                return redirect()->back()->withErrors([
                    'code' => 'The code you entered was incorrect.'
                ]);
            case AuthenticationBroker::INVALID_ENROLMENT:
                return $this->sendInvalidEnrolmentResponse();
            case AuthenticationBroker::CODE_VERIFIED:
                return redirect()->to(
                    $this->verificationRedirectPath()
                );
            case AuthenticationBroker::USER_ENROLLED:
                return redirect()->to(
                    $this->enrolledPath($method)
                );
        }

        throw new TwoFactorLogicException(
            sprintf('Broker returned an invalid response. [%s]', $response)
        );
    }
}