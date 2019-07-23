<?php

namespace BoxedCode\Laravel\TwoFactor\Http;

trait RedirectPaths
{
    public function verificationPath($provider_name)
    {
        return "/auth/tfa/$provider_name/verify";
    }

    public function verificationRedirectPath()
    {
        return '/';
    }

    public function enrolmentSetupPath($provider_name)
    {
        return "/auth/tfa/$provider_name/enrol/setup";
    }

    public function enrolledPath($provider_name)
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
}