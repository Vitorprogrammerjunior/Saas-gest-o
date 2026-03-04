<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    /**
     * View workspace — must be a member
     */
    public function view(User $user, Workspace $workspace): bool
    {
        return $workspace->hasMember($user);
    }

    /**
     * Update workspace — must be owner or admin
     */
    public function update(User $user, Workspace $workspace): bool
    {
        $role = $workspace->getMemberRole($user);

        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Delete workspace — must be owner
     */
    public function delete(User $user, Workspace $workspace): bool
    {
        return $workspace->owner_id === $user->id;
    }
}
