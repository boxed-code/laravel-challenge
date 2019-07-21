<?php

namespace BoxedCode\Laravel\TwoFactor;

use BoxedCode\Laravel\TwoFactor\Models\Enrollment;
use Illuminate\Database\Eloquent\Model;

class EloquentEnrollmentRepository implements EnrollmentRepository
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    protected function getQuery()
    {
        return $this->model->query();
    }

    public function enroll($challengeable_id, $provider, $token = null)
    {
        return $this->model->create([
            'provider' => 'name', 
            'challengeable_id' => $challengeable_id,
            'token' => $token
        ]);
    }

    public function enrollments($challengeable_id)
    {
        return $this->getQuery()
            ->where('challengeable_id', '=', $challengeable_id)
            ->get();
    }

    public function disenroll($challengeable_id, $provider)
    {
        return $this->getQuery()
            ->where('challengeable_id', '=', $challengeable_id)
            ->where('provider', '=', $provider)
            ->delete();
    }

    public function enrolled($challengeable_id, $provider)
    {
        return $this->getQuery()
            ->where('challengeable_id', '=', $challengeable_id)
            ->where('provider', '=', $provider)
            ->count() > 0;
    }
}