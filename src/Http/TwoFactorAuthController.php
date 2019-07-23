<?php

namespace BoxedCode\Laravel\TwoFactor\Http;

use Illuminate\Routing\Controller;

class TwoFactorAuthenticationController extends Controller
{
    use Traits\TwoFactorAuthentication;
}