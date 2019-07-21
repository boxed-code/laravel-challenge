<?php

namespace BoxedCode\Laravel\TwoFactor;

interface TokenRepository
{
    public function getBySessionId($session_id, $enrollment_token = false): ?array;
    public function getByChallengeableId($challengeable_id, $enrollment_token = false): ?array;
    public function create($token, $session_id, $challengable_id, $provider, $enrollment_token = false): ?array;
    public function destroy($session_id);
    public function gc($challengable_id, $lifetime);
    public function flush($challengeable_id);
}