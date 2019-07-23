<?php

namespace BoxedCode\Laravel\TwoFactor\Http\Traits;

trait TwoFactorAuthentication
{
    use EnrolsUsers, 
        ChallengesUsers, 
        SendsAndShowsErrors, 
        RedirectPaths;
}