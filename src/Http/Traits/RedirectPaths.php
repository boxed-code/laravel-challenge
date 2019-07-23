<?php

namespace BoxedCode\Laravel\TwoFactor\Http\Traits;

trait RedirectPaths
{
    public function enrolmentPath($provider_name)
    {
        return "/tfa/$provider_name/enrol";
    }

    public function enrolmentSetupPath($provider_name)
    {
        return "/tfa/$provider_name/enrol/setup";
    }

    public function enrolledPath($provider_name)
    {
        return "/tfa/$provider_name/enrolled";
    }

    public function challengePath()
    {
        return '/tfa/challenge';
    }
    
    public function verificationPath($provider_name)
    {
        return "/tfa/$provider_name/verify";
    }

    public function verificationRedirectPath()
    {
        return '/';
    }

    public function errorPath()
    {
        return '/tfa/error';
    }
}