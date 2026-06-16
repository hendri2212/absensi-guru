<?php

namespace App\Policies;

use App\Models\Evaluation;
use App\Models\User;

class EvaluationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'Admin' || $user->teacher !== null;
    }

    public function view(User $user, Evaluation $evaluation): bool
    {
        if ($user->role === 'Admin') {
            return true;
        }

        return $user->teacher !== null &&
            $evaluation->teacher_id === $user->teacher->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'Admin' || $user->teacher !== null;
    }

    public function update(User $user, Evaluation $evaluation): bool
    {
        return $this->view($user, $evaluation);
    }

    public function delete(User $user, Evaluation $evaluation): bool
    {
        return $this->view($user, $evaluation);
    }
}
