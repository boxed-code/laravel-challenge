<?php

namespace BoxedCode\Laravel\TwoFactor\Contracts;

interface AuthenticationBroker
{
    const INVALID_METHOD = 'invalid_method',
          INVALID_TOKEN = 'invalid_token',
          INVALID_CODE = 'invalid_code',
          INVALID_ENROLMENT = 'invalid_enrolment',
          INVALID_CHALLENGE = 'invalid_challenge',
          CODE_VERIFIED = 'code_verified',
          USER_NOT_ENROLLED = 'user_not_enrolled',
          USER_CANNOT_ENROL = 'user_cannot_enrol',
          USER_CHALLENGED = 'user_challenged',
          USER_ENROLLED = 'user_enrolled',
          METHOD_REQUIRES_SETUP = 'requires_setup',
          NO_ENROLMENT_IN_PROGRESS = 'no_enrolment_in_progress';
}