<?php

namespace BoxedCode\Laravel\TwoFactor\Http\Traits;

trait TwoFactorAuthentication
{
    use EnrolsUsers, 
        ChallengesUsers, 
        RoutesBrokerResponses,
        Helpers;
}