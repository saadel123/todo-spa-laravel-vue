<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TodoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given user can view the todo.
     */
    public function view(User $user, Todo $todo): bool
    {
        // Only the user who owns the todo can view it
        return $user->id === $todo->user_id;
    }

    /**
     * Determine if the given user can create a todo.
     */

    public function create(User $user)
    {
        // Any authenticated user can create a Todo
        return true;
    }

    /**
     * Determine if the given user can update the todo.
     */
    public function update(User $user, Todo $todo)
    {
        // Only the user who owns the todo can update it
        return $user->id === $todo->user_id;
    }

    /**
     * Determine if the given user can delete the todo.
     */
    public function delete(User $user, Todo $todo)
    {
        // Only the user who owns the todo can delete it
        return $user->id === $todo->user_id;
    }
}
