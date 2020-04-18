<?php

namespace BoxedCode\Laravel\Auth\Challenge\Http;

use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    use Traits\ProvidesAuthentication;
}
