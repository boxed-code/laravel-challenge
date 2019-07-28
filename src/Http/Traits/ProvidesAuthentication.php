<?php

namespace BoxedCode\Laravel\Auth\Challenge\Http\Traits;

trait ProvidesAuthentication
{
    use EnrolsUsers, 
        ChallengesUsers, 
        RoutesBrokerResponses,
        Helpers;
}