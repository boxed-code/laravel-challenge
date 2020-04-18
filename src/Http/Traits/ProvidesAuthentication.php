<?php

namespace BoxedCode\Laravel\Auth\Challenge\Http\Traits;

trait ProvidesAuthentication
{
    use EnrolsUsers;
    use ChallengesUsers;
    use RoutesBrokerResponses;
    use Helpers;
}
