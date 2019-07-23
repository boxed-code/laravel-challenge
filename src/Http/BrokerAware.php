<?php

namespace BoxedCode\Laravel\TwoFactor\Http;

use BoxedCode\Laravel\TwoFactor\AuthenticationBroker;

trait BrokerAware
{
    protected function broker()
    {
        return app('auth.tfa');
    }
}