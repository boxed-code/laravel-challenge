<?php

namespace BoxedCode\Laravel\TwoFactor\Http\Traits;

use BoxedCode\Laravel\TwoFactor\AuthenticationBroker;

trait BrokerAware
{
    protected function broker()
    {
        return app('auth.tfa');
    }
}