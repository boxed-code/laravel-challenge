<?php

namespace BoxedCode\Laravel\TwoFactor;

use Illuminate\Database\ConnectionInterface;

class DatabaseTokenRepository implements TokenRepository
{
    protected $connection;

    protected $table;

    public function __construct(ConnectionInterface $connection, $table)
    {
        $this->connection = $connection;

        $this->table = $table;
    }

    protected function getQuery()
    {
        return $this->connection->table($this->table);
    }

    public function getBySessionId($session_id, $enrollment_token = false): ?array
    {
        $result = $this->getQuery()
            ->where('session_id', '=', $session_id)
            ->where('is_enrollment_token', '=', $enrollment_token)
            ->orderBy('id', 'DESC')
            ->first();

        return $result ? (array) $result : null;
    }

    public function getByChallengeableId($challengeable_id, $enrollment_token = false): ?array
    {
        $result = $this->getQuery()
            ->where('challengeable_id', '=', $challengeable_id)
            ->where('is_enrollment_token', '=', $enrollment_token)
            ->orderBy('id', 'DESC')
            ->first();

        return $result ? (array) $result : null;
    }

    public function create($token, $session_id, $challengable_id, $provider, $enrollment_token = false): ?array
    {
        $result = $this->getQuery()->insert([
            'token' => $token,
            'session_id' => $session_id,
            'challengeable_id' => $challengable_id,
            'provider' => $provider,
            'is_enrollment_token' => $enrollment_token,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $result ? (array) $result : null;
    }

    public function destroy($session_id)
    {
        return $this->getQuery()
            ->where('session_id', '=', $session_id)
            ->delete();
    }

    public function gc($challengable_id, $lifetime)
    {
        return $this->getQuery()
            ->where('challengeable_id', '=', $challengable_id)
            ->where('created_at', '<=', now()->subSeconds($lifetime))
            ->delete();
    }

    public function flush($challengeable_id)
    {
        return $this->getQuery()
            ->where('challengeable_id', '=', $challengeable_id)
            ->delete();
    }
}