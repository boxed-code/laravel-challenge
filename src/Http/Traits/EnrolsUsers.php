<?php

namespace BoxedCode\Laravel\Auth\Challenge\Http\Traits;

use BoxedCode\Laravel\Auth\Challenge\AuthBroker;
use BoxedCode\Laravel\Auth\Challenge\Contracts\Challenge;
use BoxedCode\Laravel\Auth\Challenge\Contracts\Enrolment;
use Illuminate\Http\Request;

trait EnrolsUsers
{
    /**
     * Begin the two factor enrolment process.
     *
     * @param Request $request
     * @param string  $method
     *
     * @return \Illuminate\Http\Response
     */
    public function begin(Request $request, $method)
    {
        $this->manager()->requestAuthentication(
            Challenge::PURPOSE_ENROLMENT
        );

        $response = $this->broker()->beginEnrolment(
            $request->user(),
            $method
        );

        return $this->routeResponse($request, $response, $method);
    }

    /**
     * The enrolment method requires additional setup, the user
     * should be redirected to the methods setup form.
     *
     * @param Request   $request
     * @param Enrolment $enrolment
     *
     * @return \Illuminate\Http\Response
     */
    protected function requiresSetup(Request $request, Enrolment $enrolment)
    {
        //
    }

    /**
     * Show the enrolment method setup form.
     *
     * @param Request $request
     * @param string  $method
     *
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
        if (AuthBroker::BEFORE_SETUP_COMPLETE !== (string) $response) {
            return $this->routeResponse($request, $response);
        }

        return $this->view('setup', $method, [
            'setup_data'      => $response->data,
            'form_action_url' => route('challenge.enrolment.setup', [$method]),
        ]);
    }

    /**
     * Handle the setup form submission.
     *
     * @param Request $request
     * @param string  $method
     *
     * @return \Illuminate\Http\Response
     */
    public function setup(Request $request, $method)
    {
        $response = $this->broker()->setup(
            $request->user(),
            $method,
            $request->all()
        );

        return $this->routeResponse($request, $response, $method);
    }

    /**
     * The user has been successfully enrolled into the requested
     * authentication method and should be shown the enrolment success view.
     *
     * @param Request   $request
     * @param Enrolment $enrolment
     *
     * @return \Illuminate\Http\Request
     */
    protected function enrolled(Request $request, Enrolment $enrolment)
    {
        //
    }

    /**
     * Show the enrolment success page.
     *
     * @param Request $request
     * @param string  $method
     *
     * @return \Illuminate\Http\Response
     */
    public function showEnrolled(Request $request, $method)
    {
        $this->manager()->revokeAuthenticationRequest();

        return $this->view('enrolled', $method, [
            'method' => $method,
        ]);
    }

    /**
     * Handle a request to disenroll the user
     * from the requested authentication method.
     *
     * @param Request $request
     * @param string  $method
     *
     * @return \Illuminate\Http\Response
     */
    public function disenrol(Request $request, $method)
    {
        $response = $this->broker()->disenrol(
            $request->user(),
            $method
        );

        return $this->routeResponse($request, $response, $method);
    }

    /**
     * The user has been disenrolled for the requested authentication
     * method an should be shown the disenrolment success view.
     *
     * @param Request   $request
     * @param Enrolment $enrolment
     *
     * @return \Illuminate\Http\Request
     */
    protected function disenrolled(Request $request, Enrolment $enrolment)
    {
        //
    }

    /**
     * Show the 'successfully disenrolled' page.
     *
     * @param Request $request
     * @param string  $method
     *
     * @return \Illuminate\Http\Response
     */
    public function showDisenrolled(Request $request, $method)
    {
        return $this->view('disenrolled', $method, [
            'method' => $method,
        ]);
    }
}
