<?php

namespace BoxedCode\Laravel\TwoFactor;

interface EnrollmentRepository
{
    public function enroll($challengeable_id, $provider, $token = null);
    public function disenroll($challengeable_id, $provider);
    public function enrolled($challengeable_id, $provider);
    public function enrollments($challengeable_id);
}