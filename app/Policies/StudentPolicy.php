<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'Admin' || $user->teacher !== null;
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->role === 'Admin') {
            return true;
        }

        return $user->teacher !== null &&
            $student->classroom !== null &&
            $student->classroom->walas_id === $user->teacher->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'Admin';
    }

    public function update(User $user, Student $student): bool
    {
        return $this->view($user, $student);
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->role === 'Admin';
    }
}
