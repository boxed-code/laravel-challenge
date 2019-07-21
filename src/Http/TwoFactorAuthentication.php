<?php

namespace BoxedCode\Laravel\TwoFactor\Http;

use BoxedCode\Laravel\TwoFactor\AuthenticationBroker;
use BoxedCode\Laravel\TwoFactor\Challengeable;
use BoxedCode\Laravel\TwoFactor\Providers\Provider;
use Illuminate\Http\Request;
use LogicException;

trait TwoFactorAuthentication
{
    public function verificationPath($provider_name)
    {
        return "/auth/tfa/$provider_name/verify";
    }

    public function verificationRedirectPath()
    {
        return '/';
    }

    public function enrollmentFormPath($provider_name)
    {
        return "/auth/tfa/$provider_name/enroll/setup";
    }

    public function enrollmentPath($provider_name)
    {
        return "/auth/tfa/$provider_name/enroll/setup";
    }

    public function enroledPath($provider_name)
    {
        return "/auth/tfa/$provider_name/enrolled";
    }

    public function challengePath()
    {
        return '/auth/tfa/challenge';
    }

    public function errorPath()
    {
        return '/auth/tfa/error';
    }

    public function showMethodSelectionForm(Request $request)
    {
        $user = $request->user();

        // If the user only has one enrolled authentication method, 
        // we direct them straight to the verification process.
        if (!$this->broker()->hasMultipleProviders($user)) {
            return $this->challengeAndRedirect(
                $request,
                $this->broker()->defaultProvider($user)
            );            
        }

        // Otherwise, we show them the provider selection screen.
        return view('two_factor::method')->withProviders(
            $this->broker()->availableProviders($user)
        )->with('challenge_path', $this->challengePath());
    }

    public function challenge(Request $request)
    {
        $request->validate([
            'method' => 'required|string'
        ]);

        return $this->challengeAndRedirect(
            $request,
            $request->get('method')
        );
    }

    public function showVerificationForm(Request $request, $provider_name)
    {
        $isEnroling = $this->broker()->enrolling(
            $request->user(), $provider_name
        );

        $isEnroled = $this->broker()->enrolled(
            $request->user(), $provider_name
        );

        // First, we check that the requested authentication method
        // is valid and that the user is enrolled into it.
        if (!$isEnroled &&!$isEnroling) {
            return $this->sendUserNotEnrolledResponse($provider_name);
        }

        // Next, whether the provider has a custom verification view.
        $providerViewName = "two_factor::$provider_name.verify";
        if (view()->exists($providerViewName)) {
            return view($providerViewName)
                ->withIsEnroling($isEnroling)
                ->withVerificationPath($this->verificationPath($provider_name));
        }

        // Otherwise, we serve the default verification screen.
        return view('two_factor::verify')
            ->withIsEnroling($isEnroling)
            ->withVerificationPath($this->verificationPath($provider_name));
    }

    public function verify(Request $request, $provider_name)
    {
        $isEnroling = ('1' === $request->get('is_enroling', false));

        $response = $this->broker()->verify(
            $request->user(), 
            $provider_name, 
            $request->get('code'),
            $request->session()->getId(),
            $isEnroling // Should be able to get this from the token!
        );

        switch ($response) 
        {
            case AuthenticationBroker::INVALID_TOKEN:
                return $this->sendErrorResponse('The token is invalid.');
            case AuthenticationBroker::INVALID_CODE:
                return redirect()->back()->withErrors([
                    'code' => 'The code you entered was incorrect.'
                ]);
            case AuthenticationBroker::CODE_VERIFIED:
                return redirect()->to(
                    $this->verificationRedirectPath()
                );
            case AuthenticationBroker::PROVIDER_REQUIRES_SETUP:
                return redirect()->to(
                    $this->enrollmentFormPath($provider_name)
                );
            case AuthenticationBroker::USER_ENROLED:
                return redirect()->to(
                    $this->enroledPath($provider_name)
                );
        }

        throw new LogicException('Broker returned an invalid response.');
    }

    public function enroll(Request $request, $provider_name)
    {
        $response = $this->broker()->enroll(
            $request->user(), 
            $provider_name,
            $request->session()->getId()
        );

        switch ($response) {
            case AuthenticationBroker::USER_CHALLENGED:
                return redirect()->to(
                    $this->verificationPath($provider_name)
                );
            case AuthenticationBroker::PROVIDER_REQUIRES_SETUP:
                return redirect()->to(
                    $this->enrollmentFormPath($provider_name)
                );
            case AuthenticationBroker::USER_ENROLED:
                return redirect()->to(
                    $this->enroledPath($provider_name)
                );
            case AuthenticationBroker::INVALID_PROVIDER:
                return $this->sendErrorResponse(
                    sprintf(
                        'The %s provider is not available for enrollment.', 
                        $provider_name
                    )
                );
            case AuthenticationBroker::USER_CANNOT_ENROL:
                return $this->sendErrorResponse(
                    sprintf(
                        'The user cannot enroll in to %s two factor authentication.', 
                        $provider_name
                    )
                );
        }

        throw new LogicException('Broker returned an invalid response.');
    }

    public function showEnrollmentForm(Request $request, $provider_name)
    {
        return view('two_factor::enroll');
    }

    public function enrollment(Request $request, $provider_name)
    {
        if ($this->broker()->providerRequiresEnrollmentChallenge()) {
            // Validate the verification actually happened here, maybe we start 
            // getting the broker to record whether we've had a successful 
            // validation OR maybe we store it on the token in the TokenRepository?
        }

        if ($this->broker()->requiresSetup()) {
            // Do setup request validation etc here....
        }
    }

    public function enrolled()
    {
        return view('two_factor::enrolled');
    }

    public function showError(Request $request)
    {
        return view('two_factor::error');
    }

    protected function challengeAndRedirect(Request $request, $provider_name)
    {
        if (!$this->broker()->validProviderName($provider_name)) {
            return $this->sendErrorResponse(
                sprintf(
                    'The %s provider is not available.', 
                    $provider_name
                )
            );
        }

        $sessionId = $request->session()->getId();

        $response = $this->broker()->challenge(
            $request->user(), 
            $provider_name, 
            $sessionId
        );

        if (AuthenticationBroker::USER_NOT_ENROLLED === $response) {
            return $this->sendUserNotEnrolledResponse($provider_name);
        }

        return redirect()->to(
            $this->verificationPath($provider_name)
        );
    }

    protected function sendErrorResponse($message)
    {
        return redirect()->to($this->errorPath())
            ->withErrors([
                $message
            ]);
    }

    protected function sendUserNotEnrolledResponse($provider_name)
    {
        return $this->sendErrorResponse(
            sprintf(
                'Two factor authentication via %s is not enabled for this user.', 
                $provider_name
            )
        );
    }

    protected function logout()
    {
        $this->guard()->logout();

        $request->session()->invalidate();
    }

    protected function broker()
    {
        return app('auth.tfa');
    }

    protected function guard()
    {
        return auth()->guard();
    }
}