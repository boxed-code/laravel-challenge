<?php

namespace BoxedCode\Laravel\TwoFactor\Http;

use Illuminate\Routing\Controller;

class TwoFactorAuthController extends Controller
{
    use Traits\TwoFactorAuthentication;
}