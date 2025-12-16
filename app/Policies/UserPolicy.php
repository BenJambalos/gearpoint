<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user)
    {
        return $user && ($user->isAdmin() || $user->isManager());
    }

    public function view(?User $user, User $model)
    {
        return $user && ($user->isAdmin() || $user->isManager() || $user->id === $model->id);
    }

    public function create(User $user)
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function update(User $user, User $model)
    {
        // Admin can update any user. Manager can update cashiers.
        if ($user->isAdmin()) {
            return true;
        }
        if ($user->isManager()) {
            return $model->role === 'cashier';
        }
        return $user->id === $model->id;
    }

    public function delete(User $user, User $model)
    {
        // Admin can delete other users but not themselves.
        return $user->isAdmin() && $user->id !== $model->id;
    }
}
