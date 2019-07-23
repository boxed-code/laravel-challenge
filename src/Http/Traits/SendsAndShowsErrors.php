<?php

namespace BoxedCode\Laravel\TwoFactor\Http\Traits;

trait SendsAndShowsErrors
{
    protected function sendUserNotEnrolledResponse($provider_name)
    {
        return $this->sendErrorResponse(
            sprintf(
                'Two factor authentication via %s is not enabled for this user.', 
                $provider_name
            )
        );
    }

    protected function sendInvalidEnrolmentResponse()
    {
        return $this->sendErrorResponse(
            'No active enrolment could be found, please restart enrolment.'
        );
    }

    protected function sendErrorResponse($message)
    {
        return redirect()->to($this->errorPath())
            ->withErrors([
                $message
            ]);
    }

    public function showError()
    {
        return view('two_factor::error');
    }
}