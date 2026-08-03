<?php

namespace App\Policies;

use App\Models\User;

class ErrorLogPolicy
{
    /**
     * Whether the user may view the admin log viewer.
     *
     * Access is granted by this policy, not by an unguessable route —
     * see docs/ARCHITECTURE.md's "log viewer is an attack surface" section.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }
}
