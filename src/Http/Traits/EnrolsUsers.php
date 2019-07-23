<?php

namespace BoxedCode\Laravel\TwoFactor\Http\Traits;

use BoxedCode\Laravel\TwoFactor\AuthenticationBroker;
use BoxedCode\Laravel\TwoFactor\Contracts\Challenge;
use Illuminate\Http\Request;
use LogicException;

trait EnrolsUsers
{
    public function begin(Request $request, $method)
    {
        $response = $this->broker()->begin(
            $request->user(), 
            $method
        );

        switch ($response) {
            case AuthenticationBroker::USER_CHALLENGED:
                return redirect()->to(
                    $this->verificationPath($method)
                )->with('_tfa_purpose', Challenge::PURPOSE_ENROLMENT);
            case AuthenticationBroker::METHOD_REQUIRES_SETUP:
                return redirect()->to(
                    $this->enrolmentSetupPath($method)
                );
            case AuthenticationBroker::USER_ENROLLED:
                return redirect()->to(
                    $this->enrolledPath($method)
                );
            case AuthenticationBroker::INVALID_METHOD:
                return $this->sendErrorResponse(
                    sprintf(
                        'The %s method is not available for enrollment.', 
                        $method
                    )
                );
            case AuthenticationBroker::USER_CANNOT_ENROL:
                return $this->sendErrorResponse(
                    sprintf(
                        'The user cannot enrol in to %s two factor authentication.', 
                        $method
                    )
                );
        }

        throw new LogicException(
            sprintf('Broker returned an invalid response. [%s]', $response)
        );
    }

    public function showSetupForm(Request $request, $method)
    {
        $response = $this->broker()->beforeSetup(
            $request->user(),
            $method
        );

        if (AuthenticationBroker::INVALID_ENROLMENT === $response) {
            return $this->sendInvalidEnrolmentResponse();
        } 

        $methodViewName = "two_factor::$method.enrol";

        if (view()->exists($methodViewName)) {
            $view = view($methodViewName);
        }

        return (isset($view) ? $view : view('two_factor::enrol'))
            ->withSetupPath($this->enrolmentSetupPath($method))
            ->withSetupData($response);
    }

    public function setup(Request $request, $method)
    {
        $response = $this->broker()->setup(
            $request->user(),
            $method
        );

        switch ($response) {
            case AuthenticationBroker::USER_CHALLENGED:
                return redirect()->to(
                    $this->verificationPath($method)
                )->with('_tfa_purpose', Challenge::PURPOSE_ENROLMENT);
            case AuthenticationBroker::INVALID_ENROLMENT:
                return $this->sendInvalidEnrolmentResponse();
            case AuthenticationBroker::USER_ENROLLED:
                return redirect()->to(
                    $this->enrolledPath($method)
                );
        }

        throw new LogicException(
            sprintf('Broker returned an invalid response. [%s]', $response)
        );
    }

    public function showEnrolled()
    {
        return view('two_factor::enrolled');
    }

    public function disenrol(Request $request, $method)
    {
        $response = $this->broker()->disenrol(
            $request->user(),
            $method
        );

        switch ($response) {
            case AuthenticationBroker::USER_DISENROLLED:
                return redirect()->to(
                    $this->disenrolledPath($method)
                );
            case AuthenticationBroker::INVALID_ENROLMENT:
                return $this->sendInvalidEnrolmentResponse();
        }

        throw new LogicException(
            sprintf('Broker returned an invalid response. [%s]', $response)
        );
    }

    public function showDisenrolled()
    {
        return view('two_factor::disenrolled');
    }
}