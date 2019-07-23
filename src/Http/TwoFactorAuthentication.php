<?php

namespace BoxedCode\Laravel\TwoFactor\Http;

trait TwoFactorAuthentication
{
    use EnrolsUsers, 
        ChallengesUsers, 
        SendsAndShowsErrors, 
        RedirectPaths;
}